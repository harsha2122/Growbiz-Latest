<?php

namespace Botble\Marketplace\Http\Controllers;

use Botble\Marketplace\Models\CustomerReferral;
use Botble\Marketplace\Models\ReferralProgram;
use Illuminate\Http\Request;

class ReferralProgramController extends BaseController
{
    public function index()
    {
        $this->pageTitle(__('Refer & Earn'));
        $programs = ReferralProgram::query()->with('store')->latest()->paginate(20, ['*'], 'programs');
        $referrals = CustomerReferral::query()->with(['store', 'referrer', 'order'])->latest()->paginate(30);

        return view('plugins/marketplace::referral-program.admin', compact('programs', 'referrals'));
    }

    public function reject(int $referral, Request $request, \Botble\Marketplace\Services\StoreReferralService $service)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:140']]);
        $service->rejectReward($referral, 'Admin #'.auth()->id().': '.$data['reason']);

        return back()->with('success_msg', __('Referral rejected and reserved budget released.'));
    }

    public function pause(int $program, Request $request)
    {
        ReferralProgram::findOrFail($program)->update(['enabled' => false]);

        return back()->with('success_msg', __('Referral program paused. Existing earned rewards are preserved.'));
    }
}
