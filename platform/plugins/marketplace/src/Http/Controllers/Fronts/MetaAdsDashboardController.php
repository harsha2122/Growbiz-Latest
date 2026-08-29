<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Facades\Assets;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\MetaAdAccount;
use Botble\Marketplace\Models\MetaAdInsightDaily;
use Botble\Marketplace\Models\MetaCampaign;
use Botble\Marketplace\Services\MetaApiClient;
use Illuminate\Http\Request;

class MetaAdsDashboardController extends BaseController
{
    protected int $storeId = 0;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (! MarketplaceHelper::isMetaAdsEnabled()) {
                return redirect()->route('marketplace.vendor.dashboard')
                    ->with('error', 'Meta Ads is not enabled. Please contact admin.');
            }
            $store = auth('customer')->user()?->store;
            $this->storeId = $store?->id ?? 0;
            if (! $this->storeId) {
                return redirect()->route('marketplace.vendor.dashboard')
                    ->with('error', 'No store found for your account.');
            }
            if (! $store->hasMetaAdsAccess()) {
                return redirect()->route('marketplace.vendor.dashboard')
                    ->with('error', 'Your current subscription plan does not include Meta Ads. Please contact admin to upgrade your plan.');
            }
            return $next($request);
        });

        Assets::addScriptsDirectly([
                'vendor/core/plugins/ecommerce/libraries/daterangepicker/daterangepicker.js',
                'vendor/core/plugins/ecommerce/libraries/apexcharts-bundle/dist/apexcharts.min.js',
            ])
            ->addStylesDirectly([
                'vendor/core/plugins/ecommerce/libraries/daterangepicker/daterangepicker.css',
                'vendor/core/plugins/ecommerce/libraries/apexcharts-bundle/dist/apexcharts.css',
            ])
            ->addScripts(['moment']);
    }

    public function index(Request $request)
    {
        $this->pageTitle('Meta Ads — Dashboard');

        [$startDate, $endDate, $predefinedRange] = EcommerceHelper::getDateRangeInReport($request);

        $adAccount       = MetaAdAccount::query()->where('store_id', $this->storeId)->first();
        $totalCampaigns  = MetaCampaign::query()->where('store_id', $this->storeId)->count();
        $activeCampaigns = MetaCampaign::query()->where('store_id', $this->storeId)->where('status', 'ACTIVE')->count();

        $rangeTotals = MetaAdInsightDaily::query()
            ->where('store_id', $this->storeId)
            ->where('object_type', MetaAdInsightDaily::TYPE_CAMPAIGN)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->selectRaw('SUM(impressions) as impressions, SUM(clicks) as clicks, SUM(spend) as spend, SUM(reach) as reach, SUM(conversions) as conversions, SUM(conversion_value) as conversion_value')
            ->first();

        $totalImpressions = (int) ($rangeTotals->impressions ?? 0);
        $totalClicks      = (int) ($rangeTotals->clicks ?? 0);
        $totalSpend       = (float) ($rangeTotals->spend ?? 0);
        $totalReach       = (int) ($rangeTotals->reach ?? 0);
        $totalConversions = (int) ($rangeTotals->conversions ?? 0);
        $totalConversionValue = (float) ($rangeTotals->conversion_value ?? 0);
        $avgCtr           = $totalImpressions > 0 ? round(($totalClicks / $totalImpressions) * 100, 2) : 0;
        $avgCpc           = $totalClicks > 0 ? round($totalSpend / $totalClicks, 2) : 0;
        $avgCpm           = $totalImpressions > 0 ? round(($totalSpend / $totalImpressions) * 1000, 2) : 0;

        $recentCampaigns = MetaCampaign::query()->where('store_id', $this->storeId)->latest()->limit(5)->get();

        // Daily trend series for the chart, summed across all of this store's campaigns.
        $dailySeries = MetaAdInsightDaily::query()
            ->where('store_id', $this->storeId)
            ->where('object_type', MetaAdInsightDaily::TYPE_CAMPAIGN)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->selectRaw('date, SUM(impressions) as impressions, SUM(clicks) as clicks, SUM(spend) as spend')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $chartData = [
            'dates'       => $dailySeries->pluck('date')->map(fn ($d) => $d->format('Y-m-d'))->values(),
            'spend'       => $dailySeries->pluck('spend')->map(fn ($v) => (float) $v)->values(),
            'impressions' => $dailySeries->pluck('impressions')->map(fn ($v) => (int) $v)->values(),
            'clicks'      => $dailySeries->pluck('clicks')->map(fn ($v) => (int) $v)->values(),
        ];

        // Live check: payment method and account status (also cached on the account row by the hourly sync)
        $adAccountDetails = [];
        $hasPaymentMethod = null; // null = unknown (not connected or check failed)
        $accountStatus    = null;

        if ($adAccount && $adAccount->is_connected && $adAccount->access_token && $adAccount->ad_account_id) {
            if ($adAccount->account_status !== null) {
                $accountStatus    = (int) $adAccount->account_status;
                $hasPaymentMethod = (bool) $adAccount->has_payment_method;
            } else {
                $adAccountDetails = app(MetaApiClient::class)
                    ->getAdAccountDetails($adAccount->access_token, $adAccount->ad_account_id);

                if (! empty($adAccountDetails['account_status'])) {
                    $accountStatus    = (int) $adAccountDetails['account_status'];
                    $hasPaymentMethod = ! empty($adAccountDetails['funding_source_details']);
                }
            }
        }

        $currencySymbol = $adAccount && $adAccount->currency === 'USD' ? '$' : '₹';

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.dashboard', compact(
            'adAccount', 'totalCampaigns', 'activeCampaigns',
            'totalSpend', 'totalImpressions', 'totalClicks', 'avgCtr', 'avgCpc', 'avgCpm',
            'totalReach', 'totalConversions', 'totalConversionValue', 'recentCampaigns',
            'hasPaymentMethod', 'accountStatus', 'adAccountDetails', 'currencySymbol',
            'startDate', 'endDate', 'predefinedRange', 'chartData'
        ));
    }
}
