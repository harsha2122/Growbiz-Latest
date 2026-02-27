<?php

namespace Botble\Marketplace\Jobs;

use Botble\Marketplace\Models\MetaAdAccount;
use Botble\Marketplace\Models\MetaAdSet;
use Botble\Marketplace\Models\MetaAd;
use Botble\Marketplace\Services\MetaAdsService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncMetaAdsInsightsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(private MetaAdAccount $account) {}

    public function handle(MetaAdsService $metaAdsService): void
    {
        if (! $this->account->is_connected || ! $this->account->access_token) {
            return;
        }

        $campaigns = $this->account->campaigns()->whereNotNull('meta_remote_id')->get();

        foreach ($campaigns as $campaign) {
            try {
                $insights = $metaAdsService->fetchCampaignInsights($this->account, $campaign->meta_remote_id);

                if (! empty($insights['data'][0])) {
                    $row = $insights['data'][0];
                    $campaign->update([
                        'impressions' => (int) ($row['impressions'] ?? $campaign->impressions),
                        'clicks' => (int) ($row['clicks'] ?? $campaign->clicks),
                        'spend' => (float) ($row['spend'] ?? $campaign->spend),
                    ]);
                }
            } catch (Exception $e) {
                Log::warning("Meta Ads: could not sync campaign {$campaign->meta_remote_id}", [
                    'error' => $e->getMessage(),
                ]);
            }

            // Sync ad sets for this campaign
            $adSets = MetaAdSet::query()
                ->where('campaign_id', $campaign->id)
                ->whereNotNull('meta_remote_id')
                ->get();

            foreach ($adSets as $adSet) {
                try {
                    $insights = $metaAdsService->safely(
                        fn () => $metaAdsService->fetchCampaignInsights($this->account, $adSet->meta_remote_id)
                    );

                    if (! empty($insights['data'][0])) {
                        $row = $insights['data'][0];
                        $adSet->update([
                            'impressions' => (int) ($row['impressions'] ?? $adSet->impressions),
                            'clicks' => (int) ($row['clicks'] ?? $adSet->clicks),
                            'spend' => (float) ($row['spend'] ?? $adSet->spend),
                        ]);
                    }
                } catch (Exception $e) {
                    Log::warning("Meta Ads: could not sync ad set {$adSet->meta_remote_id}", [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Sync individual ads
            $ads = MetaAd::query()
                ->where('campaign_id', $campaign->id)
                ->whereNotNull('meta_remote_id')
                ->get();

            foreach ($ads as $ad) {
                try {
                    $insights = $metaAdsService->safely(
                        fn () => $metaAdsService->fetchCampaignInsights($this->account, $ad->meta_remote_id)
                    );

                    if (! empty($insights['data'][0])) {
                        $row = $insights['data'][0];
                        $impressions = (int) ($row['impressions'] ?? $ad->impressions);
                        $clicks = (int) ($row['clicks'] ?? $ad->clicks);
                        $spend = (float) ($row['spend'] ?? $ad->spend);
                        $ctr = $impressions > 0 ? round($clicks / $impressions * 100, 2) : 0;
                        $cpc = $clicks > 0 ? round($spend / $clicks, 2) : 0;

                        $ad->update([
                            'impressions' => $impressions,
                            'clicks' => $clicks,
                            'spend' => $spend,
                            'ctr' => $ctr,
                            'cpc' => $cpc,
                        ]);
                    }
                } catch (Exception $e) {
                    Log::warning("Meta Ads: could not sync ad {$ad->meta_remote_id}", [
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        Log::info("Meta Ads: insights synced for account {$this->account->id} (store {$this->account->store_id})");
    }

    public function failed(Exception $exception): void
    {
        Log::error("Meta Ads: SyncMetaAdsInsightsJob failed for account {$this->account->id}", [
            'error' => $exception->getMessage(),
        ]);
    }
}
