<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\MetaAd;
use Botble\Marketplace\Models\MetaAdAccount;
use Botble\Marketplace\Models\MetaCampaign;
use Botble\Marketplace\Services\MetaApiClient;

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
            $this->storeId = auth('customer')->user()?->store?->id ?? 0;
            if (! $this->storeId) {
                return redirect()->route('marketplace.vendor.dashboard')
                    ->with('error', 'No store found for your account.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $this->pageTitle('Meta Ads — Dashboard');

        $adAccount       = MetaAdAccount::query()->where('store_id', $this->storeId)->first();
        $totalCampaigns  = MetaCampaign::query()->where('store_id', $this->storeId)->count();
        $activeCampaigns = MetaCampaign::query()->where('store_id', $this->storeId)->where('status', 'ACTIVE')->count();
        $totalSpend      = MetaCampaign::query()->where('store_id', $this->storeId)->sum('spend');
        $totalImpressions = MetaCampaign::query()->where('store_id', $this->storeId)->sum('impressions');
        $totalClicks     = MetaCampaign::query()->where('store_id', $this->storeId)->sum('clicks');
        $avgCtr          = $totalImpressions > 0 ? round(($totalClicks / $totalImpressions) * 100, 2) : 0;
        $recentCampaigns = MetaCampaign::query()->where('store_id', $this->storeId)->latest()->limit(5)->get();

        // Live check: payment method and account status
        $adAccountDetails = [];
        $hasPaymentMethod = null; // null = unknown (not connected or check failed)
        $accountStatus    = null;

        if ($adAccount && $adAccount->is_connected && $adAccount->access_token && $adAccount->ad_account_id) {
            $adAccountDetails = app(MetaApiClient::class)
                ->getAdAccountDetails($adAccount->access_token, $adAccount->ad_account_id);

            if (! empty($adAccountDetails['account_status'])) {
                $accountStatus    = (int) $adAccountDetails['account_status'];
                // funding_source_details is present (even as empty array) when payment method exists
                $hasPaymentMethod = ! empty($adAccountDetails['funding_source_details']);
            }
        }

        return MarketplaceHelper::view('vendor-dashboard.meta-ads.dashboard', compact(
            'adAccount', 'totalCampaigns', 'activeCampaigns',
            'totalSpend', 'totalImpressions', 'totalClicks', 'avgCtr', 'recentCampaigns',
            'hasPaymentMethod', 'accountStatus', 'adAccountDetails'
        ));
    }
}
