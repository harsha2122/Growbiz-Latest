<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Models\MetaAd;
use Botble\Marketplace\Models\MetaCampaign;
use Illuminate\Http\Request;

class MetaAdReportController extends BaseController
{
    public function index(Request $request)
    {
        $this->pageTitle(__('Meta Ads Reports'));

        $store = auth('customer')->user()->store;
        $storeId = $store?->id;

        $dateFrom = $request->input('date_from', now()->subDays(29)->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        $campaigns = MetaCampaign::query()
            ->where('store_id', $storeId)
            ->orderByDesc('created_at')
            ->get(['id', 'name', 'status', 'objective', 'impressions', 'clicks', 'spend', 'created_at']);

        $totals = [
            'impressions' => $campaigns->sum('impressions'),
            'clicks' => $campaigns->sum('clicks'),
            'spend' => $campaigns->sum('spend'),
            'ctr' => $campaigns->sum('impressions') > 0
                ? round($campaigns->sum('clicks') / $campaigns->sum('impressions') * 100, 2)
                : 0,
        ];

        $topAds = MetaAd::query()
            ->where('store_id', $storeId)
            ->orderByDesc('impressions')
            ->limit(5)
            ->get(['id', 'name', 'status', 'impressions', 'clicks', 'ctr', 'spend', 'cpc']);

        return view('plugins/marketplace::themes.vendor-dashboard.meta-ads.reports.index', compact(
            'campaigns',
            'totals',
            'topAds',
            'dateFrom',
            'dateTo',
        ));
    }
}
