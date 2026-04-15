<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Http\Controllers\BaseController;

class ReferralController extends BaseController
{
    public function index()
    {
        $store = auth('customer')->user()->store;

        if (! $store) {
            abort(404);
        }

        $referrals = $store->referrals()
            ->with('referee:id,name,email,created_at')
            ->latest('joined_at')
            ->paginate(20);

        $referralLink = url('/register') . '?ref=' . $store->referral_code;

        $perReferralAmount = (float) MarketplaceHelper::getSetting('referral_earning_per_referral', 0);
        $totalReferralEarnings = $referrals->total() * $perReferralAmount;

        return view('plugins/marketplace::themes.vendor-dashboard.referrals', compact(
            'store',
            'referrals',
            'referralLink',
            'perReferralAmount',
            'totalReferralEarnings'
        ));
    }
}
