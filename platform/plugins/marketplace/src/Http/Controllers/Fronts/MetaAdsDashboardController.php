<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\MetaAd;
use Botble\Marketplace\Models\MetaAdAccount;
use Botble\Marketplace\Models\MetaCampaign;

class MetaAdsDashboardController extends BaseController
{
    protected int $storeId;

    public function __construct()
    {
        if (! MarketplaceHelper::isMetaAdsEnabled()) { abort(404); }
        abort_unless(auth('customer')->check(), 403);

        $this->storeId = auth('customer')->user()->store?->id ?? 0;
        abort_unless($this->storeId, 403);
    }

    public function index()
    {
        $this->pageTitle(__('Meta Ads — Dashboard'));

        $adAccount = MetaAdAccount::query()->where('store_id', $this->storeId)->first();

        $totalCampaigns = MetaCampaign::query()->where('store_id', $this->storeId)->count();
        $activeCampaigns = MetaCampaign::query()->where('store_id', $this->storeId)->where('status', 'ACTIVE')->count();
        $activeAds = MetaAd::query()->where('store_id', $this->storeId)->where('status', 'ACTIVE')->count();

        $totalSpend = MetaCampaign::query()->where('store_id', $this->storeId)->sum('spend');
        $totalImpressions = MetaCampaign::query()->where('store_id', $this->storeId)->sum('impressions');
        $totalClicks = MetaCampaign::query()->where('store_id', $this->storeId)->sum('clicks');
        $avgCtr = $totalImpressions > 0 ? round(($totalClicks / $totalImpressions) * 100, 2) : 0;

        $recentCampaigns = MetaCampaign::query()
            ->where('store_id', $this->storeId)
            ->latest()
            ->limit(5)
            ->get();

        // Last 7 days chart data
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chartData[] = [
                'date' => $date->format('d M'),
                'impressions' => MetaCampaign::query()
                    ->where('store_id', $this->storeId)
                    ->whereDate('created_at', '<=', $date)
                    ->sum('impressions'),
                'clicks' => MetaCampaign::query()
                    ->where('store_id', $this->storeId)
                    ->whereDate('created_at', '<=', $date)
                    ->sum('clicks'),
            ];
        }

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.dashboard', compact(
            'adAccount', 'totalCampaigns', 'activeCampaigns', 'activeAds',
            'totalSpend', 'totalImpressions', 'totalClicks', 'avgCtr',
            'recentCampaigns', 'chartData'
        ));
    }
}
