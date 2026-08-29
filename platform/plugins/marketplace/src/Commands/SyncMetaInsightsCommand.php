<?php

namespace Botble\Marketplace\Commands;

use Botble\Marketplace\Models\MetaAd;
use Botble\Marketplace\Models\MetaAdAccount;
use Botble\Marketplace\Models\MetaAdInsightDaily;
use Botble\Marketplace\Models\MetaAdSet;
use Botble\Marketplace\Models\MetaCampaign;
use Botble\Marketplace\Services\MetaApiClient;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncMetaInsightsCommand extends Command
{
    protected $signature = 'meta-ads:sync-insights';
    protected $description = 'Sync impressions, clicks, spend, reach, frequency, CPM, conversions, daily trend data, and breakdowns from Meta Marketing API for all connected accounts';

    protected const CUMULATIVE_FIELDS = MetaApiClient::BASIC_INSIGHT_FIELDS . ',' . MetaApiClient::CONVERSION_INSIGHT_FIELDS;
    protected const AD_CUMULATIVE_FIELDS = self::CUMULATIVE_FIELDS . ',' . MetaApiClient::RANKING_INSIGHT_FIELDS;
    protected const DAILY_SYNC_DAYS = 30;

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
            $this->syncAccountFinancials($client, $account);
            $this->syncAccount($client, $account);
        }

        $this->info('Meta Ads insights sync complete.');

        return self::SUCCESS;
    }

    protected function syncAccountFinancials(MetaApiClient $client, MetaAdAccount $account): void
    {
        try {
            $details = $client->getAdAccountDetails($account->access_token, $account->ad_account_id);

            if (empty($details) || ! empty($details['error'])) {
                return;
            }

            $account->update([
                'currency'            => $details['currency'] ?? $account->currency,
                'account_status'      => isset($details['account_status']) ? (int) $details['account_status'] : $account->account_status,
                'amount_spent'        => isset($details['amount_spent']) ? ((float) $details['amount_spent']) / 100 : $account->amount_spent,
                'spend_cap'           => isset($details['spend_cap']) ? ((float) $details['spend_cap']) / 100 : null,
                'balance'             => isset($details['balance']) ? ((float) $details['balance']) / 100 : null,
                'timezone_name'       => $details['timezone_name'] ?? $account->timezone_name,
                'has_payment_method'  => ! empty($details['funding_source_details']),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Meta account financials sync failed', ['account_id' => $account->id, 'error' => $e->getMessage()]);
        }
    }

    protected function syncAccount(MetaApiClient $client, MetaAdAccount $account): void
    {
        $token = $account->access_token;
        $storeId = $account->store_id;

        $since = Carbon::now()->subDays(self::DAILY_SYNC_DAYS - 1)->format('Y-m-d');
        $until = Carbon::now()->format('Y-m-d');

        // ── Campaigns ────────────────────────────────────────────────────────
        $campaigns = MetaCampaign::query()
            ->where('store_id', $storeId)
            ->whereNotNull('meta_campaign_id')
            ->get();

        foreach ($campaigns as $campaign) {
            $this->syncCumulative($client, $token, $campaign, $campaign->meta_campaign_id, self::CUMULATIVE_FIELDS);
            $this->syncDaily($client, $token, MetaAdInsightDaily::TYPE_CAMPAIGN, $campaign, $campaign->meta_campaign_id, $storeId, $since, $until);
            $this->syncBreakdowns($client, $token, $campaign, $campaign->meta_campaign_id, $since, $until);
        }

        // ── Ad Sets ───────────────────────────────────────────────────────────
        $adSets = MetaAdSet::query()
            ->where('store_id', $storeId)
            ->whereNotNull('meta_adset_id')
            ->get();

        foreach ($adSets as $adSet) {
            $this->syncCumulative($client, $token, $adSet, $adSet->meta_adset_id, self::CUMULATIVE_FIELDS);
            $this->syncDaily($client, $token, MetaAdInsightDaily::TYPE_AD_SET, $adSet, $adSet->meta_adset_id, $storeId, $since, $until);
        }

        // ── Ads ───────────────────────────────────────────────────────────────
        $ads = MetaAd::query()
            ->where('store_id', $storeId)
            ->whereNotNull('meta_ad_id')
            ->get();

        foreach ($ads as $ad) {
            $this->syncCumulative($client, $token, $ad, $ad->meta_ad_id, self::AD_CUMULATIVE_FIELDS);
            $this->syncDaily($client, $token, MetaAdInsightDaily::TYPE_AD, $ad, $ad->meta_ad_id, $storeId, $since, $until);
        }

        $this->line("  ✓ Store #{$storeId}: synced {$campaigns->count()} campaigns, {$adSets->count()} ad sets, {$ads->count()} ads");
    }

    /**
     * Pull the lifetime cumulative row for one object and update its snapshot columns.
     */
    protected function syncCumulative(MetaApiClient $client, string $token, MetaCampaign|MetaAdSet|MetaAd $model, string $metaId, string $fields): void
    {
        try {
            $result = $client->getInsights($token, $metaId, ['fields' => $fields]);
            $row = $result['data'][0] ?? null;

            if (! $row) {
                return;
            }

            $actions = $row['actions'] ?? [];
            $actionValues = $row['action_values'] ?? [];

            $update = [
                'impressions' => (int) ($row['impressions'] ?? 0),
                'clicks'      => (int) ($row['clicks'] ?? 0),
                'spend'       => (float) ($row['spend'] ?? 0),
                'reach'       => (int) ($row['reach'] ?? 0),
                'frequency'   => (float) ($row['frequency'] ?? 0),
                'cpm'         => (float) ($row['cpm'] ?? 0),
                'ctr'         => (float) ($row['ctr'] ?? 0),
                'cpc'         => (float) ($row['cpc'] ?? 0),
                'conversions' => MetaApiClient::sumConversionActions($actions),
                'conversion_value' => MetaApiClient::sumConversionValues($actionValues),
                'insights_synced_at' => now(),
            ];

            if ($model instanceof MetaAd) {
                $update['quality_ranking'] = $row['quality_ranking'] ?? null;
                $update['engagement_rate_ranking'] = $row['engagement_rate_ranking'] ?? null;
                $update['conversion_rate_ranking'] = $row['conversion_rate_ranking'] ?? null;
            }

            $model->update($update);
        } catch (\Throwable $e) {
            Log::warning('Meta cumulative insights sync failed', ['meta_id' => $metaId, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Pull a day-by-day time series for one object over the last N days and upsert
     * each day into meta_ad_insights_daily, powering the trend charts.
     */
    protected function syncDaily(MetaApiClient $client, string $token, string $objectType, MetaCampaign|MetaAdSet|MetaAd $model, string $metaId, int $storeId, string $since, string $until): void
    {
        try {
            $result = $client->getInsights($token, $metaId, [
                'fields'         => self::CUMULATIVE_FIELDS,
                'time_range'     => ['since' => $since, 'until' => $until],
                'time_increment' => 1,
            ]);

            $rows = $result['data'] ?? [];

            foreach ($rows as $row) {
                if (empty($row['date_start'])) {
                    continue;
                }

                $actions = $row['actions'] ?? [];
                $actionValues = $row['action_values'] ?? [];

                MetaAdInsightDaily::query()->updateOrCreate(
                    [
                        'object_type' => $objectType,
                        'object_id'   => $model->id,
                        'date'        => $row['date_start'],
                    ],
                    [
                        'store_id'    => $storeId,
                        'impressions' => (int) ($row['impressions'] ?? 0),
                        'clicks'      => (int) ($row['clicks'] ?? 0),
                        'spend'       => (float) ($row['spend'] ?? 0),
                        'reach'       => (int) ($row['reach'] ?? 0),
                        'frequency'   => (float) ($row['frequency'] ?? 0),
                        'cpm'         => (float) ($row['cpm'] ?? 0),
                        'ctr'         => (float) ($row['ctr'] ?? 0),
                        'cpc'         => (float) ($row['cpc'] ?? 0),
                        'conversions' => MetaApiClient::sumConversionActions($actions),
                        'conversion_value' => MetaApiClient::sumConversionValues($actionValues),
                    ]
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Meta daily insights sync failed', ['meta_id' => $metaId, 'object_type' => $objectType, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Pull age/gender and placement breakdowns for a campaign (last 30 days) and
     * cache them as JSON on the campaign row — refreshed on every sync run rather
     * than stored historically, since breakdowns are shown as a "current mix" view.
     */
    protected function syncBreakdowns(MetaApiClient $client, string $token, MetaCampaign $campaign, string $metaCampaignId, string $since, string $until): void
    {
        try {
            $ageGender = $client->getInsights($token, $metaCampaignId, [
                'fields'     => 'impressions,spend',
                'breakdowns' => ['age', 'gender'],
                'time_range' => ['since' => $since, 'until' => $until],
            ]);

            $campaign->age_gender_breakdown = $ageGender['data'] ?? [];
        } catch (\Throwable $e) {
            Log::warning('Meta age/gender breakdown sync failed', ['meta_campaign_id' => $metaCampaignId, 'error' => $e->getMessage()]);
        }

        try {
            $placement = $client->getInsights($token, $metaCampaignId, [
                'fields'     => 'impressions,spend',
                'breakdowns' => ['publisher_platform'],
                'time_range' => ['since' => $since, 'until' => $until],
            ]);

            $campaign->placement_breakdown = $placement['data'] ?? [];
        } catch (\Throwable $e) {
            Log::warning('Meta placement breakdown sync failed', ['meta_campaign_id' => $metaCampaignId, 'error' => $e->getMessage()]);
        }

        $campaign->save();
    }
}
