<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\CustomerReferral;
use Botble\Marketplace\Models\ReferralLink;
use Botble\Marketplace\Models\ReferralProgram;
use Botble\Marketplace\Models\Store;
use Botble\Marketplace\Services\StoreReferralService;
use Botble\Theme\Facades\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreReferralController extends BaseController
{
    public function seller()
    {
        $store = auth('customer')->user()->store;
        abort_unless($store, 404);
        $program = ReferralProgram::firstOrNew(['store_id' => $store->id]);
        $referrals = CustomerReferral::query()->where('store_id', $store->id)->with(['referrer', 'order'])->latest()->paginate(20);
        $this->pageTitle(__('Refer & Earn'));
        $returnDays = EcommerceHelper::getReturnableDays();

        return MarketplaceHelper::view('vendor-dashboard.referral-program', compact('program', 'referrals', 'returnDays'));
    }

    public function save(Request $request)
    {
        $store = auth('customer')->user()->store;
        abort_unless($store, 404);
        $data = $request->validate([
            'enabled' => ['required', 'boolean'], 'mode' => ['required', Rule::in(['visit', 'purchase'])],
            'reward_type' => ['required', Rule::in(['fixed', 'percentage'])],
            'reward_value' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:1000000'],
            'reward_cap' => ['nullable', 'numeric', 'decimal:0,2', 'min:0.01', 'max:1000000'],
            'budget' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:10000000'],
            'offer_type' => ['required', Rule::in(['none', 'fixed', 'percentage', 'custom'])],
            'offer_value' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:1000000'],
            'offer_text' => ['nullable', 'string', 'max:500', 'required_if:offer_type,custom'],
            'minimum_order' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:10000000'],
            'hold_days' => ['required', 'integer', 'min:0', 'max:365'],
            'ends_at' => ['nullable', 'date', 'after:now'],
        ]);
        if ($data['mode'] === 'visit' && $data['reward_type'] !== 'fixed') {
            throw ValidationException::withMessages(['reward_type' => __('Visit rewards must be a fixed amount.')]);
        }
        if ($data['reward_type'] === 'percentage' && $data['reward_value'] > 100) {
            throw ValidationException::withMessages(['reward_value' => __('Percentage cannot exceed 100.')]);
        }
        if (in_array($data['offer_type'], ['fixed', 'percentage']) && (($data['offer_value'] ?? 0) <= 0 || ($data['offer_type'] === 'percentage' && $data['offer_value'] > 100))) {
            throw ValidationException::withMessages(['offer_value' => __('Enter a valid discount amount or percentage (up to 100%).')]);
        }
        $data['offer_value'] = $data['offer_value'] ?? 0;
        $data['hold_days'] = max($data['hold_days'], EcommerceHelper::getReturnableDays());
        DB::transaction(function () use ($store, $data): void {
            // The store row also serializes the first program creation.
            Store::query()->whereKey($store->id)->lockForUpdate()->firstOrFail();
            $program = ReferralProgram::query()->where('store_id', $store->id)->lockForUpdate()->firstOrNew(['store_id' => $store->id]);
            if ((float) $data['budget'] < (float) $program->reserved) {
                throw ValidationException::withMessages(['budget' => __('Budget cannot be below already reserved and paid rewards.')]);
            }
            $program->fill($data)->save();
        }, 3);

        return back()->with('success_msg', __('Referral program saved.'));
    }

    public function landing(string $token, Request $request)
    {
        $link = ReferralLink::query()->with('program.store')->where('token', $token)->firstOrFail();
        abort_unless($link->program->isActive(), 404);
        $request->session()->put('store_referrals.'.$link->program->store_id, ['token' => $token, 'until' => now()->addDays(30)->timestamp]);
        // Only an existing published product from this store may be a landing target.
        $product = $request->integer('product');
        $target = $product ? $link->program->store->products()->whereKey($product)->wherePublished()->where('is_variation', 0)->first() : null;

        return redirect()->to($target?->url ?: $link->program->store->url);
    }

    public function claim(int $store, Request $request, StoreReferralService $service)
    {
        $pending = $request->session()->get('store_referrals.'.$store);
        abort_unless($pending && ($pending['until'] ?? 0) > now()->timestamp, 403);
        $link = ReferralLink::query()->where('token', $pending['token'])->whereHas('program', fn ($q) => $q->where('store_id', $store))->firstOrFail();
        $service->claim($link, auth('customer')->user(), (int) $pending['until']);

        return back()->with('success_msg', __('Referral offer unlocked. Your coupon or offer is shown on the store page and in Refer & Earn.'));
    }

    public function join(int $store)
    {
        $program = ReferralProgram::query()->where('store_id', $store)->firstOrFail();
        abort_unless($program->isActive(), 404);
        $customer = auth('customer')->user();
        abort_if($program->store->customer_id == $customer->id, 403);
        if (! $customer->confirmed_at) {
            throw ValidationException::withMessages(['referral' => __('Verify your email address to create a referral link.')]);
        }
        ReferralLink::firstOrCreate(['program_id' => $program->id, 'customer_id' => $customer->id], ['token' => Str::random(40)]);

        return redirect()->route('public.referral.account');
    }

    public function account(StoreReferralService $service)
    {
        $customerId = auth('customer')->id();
        CustomerReferral::query()->where('referrer_id', $customerId)->whereIn('status', ['pending', 'available'])->select('id')->chunkById(100, function ($rows) use ($service): void {
            foreach ($rows as $row) {
                $service->reconcile($row->id);
            }
        });
        $links = ReferralLink::query()->where('customer_id', $customerId)->with('program.store')->latest()->get();
        $referrals = CustomerReferral::query()->where('referrer_id', $customerId)->with('store')->latest()->paginate(20);
        $offers = CustomerReferral::query()->where('customer_id', $customerId)->where('expires_at', '>', now())->with(['store', 'coupon'])->get();
        $totals = CustomerReferral::query()->where('referrer_id', $customerId)->selectRaw('status, SUM(amount) as total')->groupBy('status')->pluck('total', 'status');
        Theme::breadcrumb()->add(__('Refer & Earn'));

        return Theme::scope('marketplace.referral-account', compact('links', 'referrals', 'offers', 'totals'), 'plugins/marketplace::referral-program.account')->render();
    }

    public function requestPayment(int $referral, StoreReferralService $service)
    {
        $reward = CustomerReferral::query()->where('referrer_id', auth('customer')->id())->findOrFail($referral);
        $service->reconcile($reward->id);
        $reward->refresh();
        abort_unless($reward->status === 'available', 422);
        if (! $reward->payout_requested_at) {
            $reward->update(['payout_requested_at' => now()]);
        }

        return back()->with('success_msg', __('Payment requested. The seller can see your request and account email.'));
    }

    public function payout(int $referral, Request $request, StoreReferralService $service)
    {
        $data = $request->validate(['reference' => ['required', 'string', 'max:191']]);
        $store = auth('customer')->user()->store;
        abort_unless($store, 404);
        $service->recordPayout($referral, $store->id, $data['reference'], auth('customer')->id());

        return back()->with('success_msg', __('Payment recorded. No bank transfer was initiated.'));
    }
}
