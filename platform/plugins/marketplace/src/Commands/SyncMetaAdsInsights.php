<?php

namespace Botble\Marketplace\Commands;

use Botble\Marketplace\Jobs\SyncMetaAdsInsightsJob;
use Botble\Marketplace\Models\MetaAdAccount;
use Illuminate\Console\Command;

class SyncMetaAdsInsights extends Command
{
    protected $signature = 'marketplace:sync-meta-ads-insights
                            {--account= : Sync a specific ad account ID only}
                            {--sync : Run synchronously instead of dispatching to the queue}';

    protected $description = 'Sync Meta Ads insights (impressions, clicks, spend) for all connected ad accounts';

    public function handle(): int
    {
        $query = MetaAdAccount::query()->where('is_connected', true);

        if ($accountId = $this->option('account')) {
            $query->where('id', $accountId);
        }

        $accounts = $query->get();

        if ($accounts->isEmpty()) {
            $this->warn('No connected Meta Ad Accounts found.');

            return self::SUCCESS;
        }

        $this->info("Found {$accounts->count()} connected account(s). Dispatching sync jobs...");

        $bar = $this->output->createProgressBar($accounts->count());
        $bar->start();

        foreach ($accounts as $account) {
            if ($this->option('sync')) {
                dispatch_sync(new SyncMetaAdsInsightsJob($account));
            } else {
                SyncMetaAdsInsightsJob::dispatch($account);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Done.');

        return self::SUCCESS;
    }
}
