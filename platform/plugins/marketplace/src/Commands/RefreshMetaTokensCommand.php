<?php

namespace Botble\Marketplace\Commands;

use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\MetaAdAccount;
use Botble\Marketplace\Services\MetaApiClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RefreshMetaTokensCommand extends Command
{
    protected $signature = 'meta-ads:refresh-tokens';
    protected $description = 'Refresh Meta Ads long-lived tokens before they expire (run every 30 days)';

    public function handle(MetaApiClient $client): int
    {
        $appId     = MarketplaceHelper::getMetaAdsAuthAppId();
        $appSecret = MarketplaceHelper::getMetaAdsAuthAppSecret();

        if (! $appId || ! $appSecret) {
            $this->error('Meta Ads App credentials not configured.');
            return self::FAILURE;
        }

        // Refresh tokens expiring within the next 15 days
        $accounts = MetaAdAccount::query()
            ->where('is_connected', true)
            ->whereNotNull('access_token')
            ->where('token_expires_at', '<=', now()->addDays(15))
            ->get();

        if ($accounts->isEmpty()) {
            $this->info('No tokens need refreshing.');
            return self::SUCCESS;
        }

        $this->info("Refreshing {$accounts->count()} token(s)...");

        foreach ($accounts as $account) {
            try {
                $result = $client->extendToken($account->access_token, $appId, $appSecret);

                if (! empty($result['access_token'])) {
                    $expiresIn = $result['expires_in'] ?? null;

                    $account->update([
                        'access_token'     => $result['access_token'],
                        'token_expires_at' => $expiresIn ? now()->addSeconds($expiresIn) : now()->addDays(60),
                    ]);

                    $this->line("  ✓ Store #{$account->store_id}: token refreshed, expires " . $account->fresh()->token_expires_at->format('Y-m-d'));
                } else {
                    // Token could not be refreshed — mark as disconnected so vendor sees reconnect prompt
                    $account->update(['is_connected' => false]);

                    $this->warn("  ✗ Store #{$account->store_id}: refresh failed — marked disconnected. Vendor must reconnect.");

                    Log::warning('Meta token refresh failed for store', [
                        'store_id' => $account->store_id,
                        'error'    => $result['error'] ?? 'unknown',
                    ]);
                }
            } catch (\Throwable $e) {
                $this->error("  ✗ Store #{$account->store_id}: exception — {$e->getMessage()}");
                Log::error('Meta token refresh exception', ['store_id' => $account->store_id, 'error' => $e->getMessage()]);
            }
        }

        $this->info('Token refresh complete.');

        return self::SUCCESS;
    }
}
