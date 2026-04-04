<?php

namespace Botble\Marketplace\Commands;

use Botble\Marketplace\Models\MetaAd;
use Botble\Marketplace\Models\MetaAdAccount;
use Botble\Marketplace\Models\MetaAdSet;
use Botble\Marketplace\Models\MetaCampaign;
use Botble\Marketplace\Services\MetaApiClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncMetaInsightsCommand extends Command
{
    protected $signature = 'meta-ads:sync-insights';
    protected $description = 'Sync impressions, clicks, and spend from Meta Marketing API for all connected accounts';

    public function handle(MetaApiClient $client): int
    {
        $accounts = MetaAdAccount::query()
            ->where('is_connected', true)
            ->whereNotNull('access_token')
            ->whereNotNull('ad_account_id')
            ->get();

        if ($accounts->isEmpty()) {
            $this->info('No connected Meta Ads accounts found.');
            return self::SUCCESS;
        }

        $this->info("Syncing insights for {$accounts->count()} account(s)...");

        foreach ($accounts as $account) {
            $this->syncAccount($client, $account);
        }

        $this->info('Meta Ads insights sync complete.');

        return self::SUCCESS;
    }

    protected function syncAccount(MetaApiClient $client, MetaAdAccount $account): void
    {
        $token = $account->access_token;
        $storeId = $account->store_id;

        // ── Campaigns ────────────────────────────────────────────────────────
        $campaigns = MetaCampaign::query()
            ->where('store_id', $storeId)
            ->whereNotNull('meta_campaign_id')
            ->get();

        foreach ($campaigns as $campaign) {
            try {
                $result = $client->getInsights($token, $campaign->meta_campaign_id);
                $row = $result['data'][0] ?? null;
                if ($row) {
                    $campaign->update([
                        'impressions' => (int) ($row['impressions'] ?? 0),
                        'clicks'      => (int) ($row['clicks'] ?? 0),
                        'spend'       => (float) ($row['spend'] ?? 0),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning("Meta insights sync failed for campaign {$campaign->meta_campaign_id}", ['error' => $e->getMessage()]);
            }
        }

        // ── Ad Sets ───────────────────────────────────────────────────────────
        $adSets = MetaAdSet::query()
            ->where('store_id', $storeId)
            ->whereNotNull('meta_adset_id')
            ->get();

        foreach ($adSets as $adSet) {
            try {
                $result = $client->getInsights($token, $adSet->meta_adset_id);
                $row = $result['data'][0] ?? null;
                if ($row) {
                    $adSet->update([
                        'impressions' => (int) ($row['impressions'] ?? 0),
                        'clicks'      => (int) ($row['clicks'] ?? 0),
                        'spend'       => (float) ($row['spend'] ?? 0),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning("Meta insights sync failed for ad set {$adSet->meta_adset_id}", ['error' => $e->getMessage()]);
            }
        }

        // ── Ads ───────────────────────────────────────────────────────────────
        $ads = MetaAd::query()
            ->where('store_id', $storeId)
            ->whereNotNull('meta_ad_id')
            ->get();

        foreach ($ads as $ad) {
            try {
                $result = $client->getInsights($token, $ad->meta_ad_id);
                $row = $result['data'][0] ?? null;
                if ($row) {
                    $ad->update([
                        'impressions' => (int) ($row['impressions'] ?? 0),
                        'clicks'      => (int) ($row['clicks'] ?? 0),
                        'spend'       => (float) ($row['spend'] ?? 0),
                        'ctr'         => (float) ($row['ctr'] ?? 0),
                        'cpc'         => (float) ($row['cpc'] ?? 0),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning("Meta insights sync failed for ad {$ad->meta_ad_id}", ['error' => $e->getMessage()]);
            }
        }

        $this->line("  ✓ Store #{$storeId}: synced {$campaigns->count()} campaigns, {$adSets->count()} ad sets, {$ads->count()} ads");
    }
}
