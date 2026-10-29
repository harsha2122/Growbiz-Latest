<?php

namespace Botble\Marketplace\Services;

use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Discount;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\OrderReturn;
use Botble\Marketplace\Models\CustomerReferral;
use Botble\Marketplace\Models\ReferralLink;
use Botble\Marketplace\Models\ReferralProgram;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StoreReferralService
{
    public function claim(ReferralLink $link, Customer $customer, ?int $attributionUntil = null): CustomerReferral
    {
        return DB::transaction(function () use ($link, $customer, $attributionUntil) {
            $program = ReferralProgram::query()->lockForUpdate()->findOrFail($link->program_id);
            $this->require($program->isActive(), __('This referral program is paused or expired.'));
            $this->require($customer->confirmed_at !== null, __('Verify your email address before joining a referral offer.'));
            $this->require($link->customer_id != $customer->id && $program->store->customer_id != $customer->id, __('You cannot refer yourself or your own store.'));
            $existing = CustomerReferral::query()->where('program_id', $program->id)->where('customer_id', $customer->id)->first();
            if ($existing) {
                $this->require($existing->expires_at->isFuture(), __('Your referral offer for this store has already ended.'));

                return $existing;
            }
            $this->require(! $this->hasPurchased($program->store_id, $customer->id), __('This offer is for new customers of this store.'));
            $referral = CustomerReferral::create([
                'program_id' => $program->id, 'link_id' => $link->id, 'customer_id' => $customer->id,
                'referrer_id' => $link->customer_id, 'store_id' => $program->store_id,
                'terms' => $program->snapshot(), 'expires_at' => $attributionUntil ? now()->setTimestamp(min($attributionUntil, now()->addDays(30)->timestamp)) : now()->addDays(30),
            ]);
            if ($program->mode === 'visit') {
                $amount = $this->reward($referral->terms, 0);
                $this->reserve($program, $amount);
                $referral->forceFill([
                    'status' => 'pending', 'amount' => $amount, 'reserved_amount' => $amount,
                    'qualified_at' => now(), 'available_at' => now()->addDay(),
                ])->save();
            }
            $this->issueCoupon($referral);

            return $referral;
        }, 3);
    }

    public function hasPurchased(int $storeId, int $customerId, ?int $exceptOrder = null): bool
    {
        return Order::query()->where('store_id', $storeId)->where('user_id', $customerId)->where('is_finished', true)
            ->when($exceptOrder, fn ($query) => $query->where('id', '!=', $exceptOrder))
            ->whereNotIn('status', ['canceled', 'returned'])->exists();
    }

    public function reward(array $terms, float $eligible): float
    {
        $amount = $terms['reward_type'] === 'percentage'
            ? $eligible * (float) $terms['reward_value'] / 100
            : (float) $terms['reward_value'];
        if ($terms['reward_cap'] !== null) {
            $amount = min($amount, (float) $terms['reward_cap']);
        }

        return round(max(0, $amount), 2);
    }

    private function reserve(ReferralProgram $program, float $amount): void
    {
        $this->require(round((float) $program->reserved + $amount, 2) <= (float) $program->budget, __('This referral program has reached its reward budget.'));
        $program->reserved = round((float) $program->reserved + $amount, 2);
        $program->save();
    }

    private function issueCoupon(CustomerReferral $referral): void
    {
        $terms = $referral->terms;
        if (! in_array($terms['offer_type'], ['fixed', 'percentage'])) {
            return;
        }
        $coupon = new Discount([
            'title' => __('Referral offer'), 'code' => 'REF-'.Str::upper(Str::random(16)),
            'type' => 'coupon', 'type_option' => $terms['offer_type'] === 'percentage' ? 'percentage' : 'amount',
            'target' => 'all-orders', 'value' => $terms['offer_value'], 'quantity' => 1, 'total_used' => 0,
            'start_date' => now(), 'end_date' => $referral->expires_at, 'discount_on' => 'per-order',
            'can_use_with_promotion' => false, 'can_use_with_flash_sale' => false,
            'display_at_checkout' => false, 'description' => __('Personal referral offer for this store only.'),
        ]);
        $coupon->store_id = $referral->store_id;
        $coupon->save();
        $referral->coupon_id = $coupon->id;
        $referral->save();
    }

    public function trackOrder(Order $order): void
    {
        if (! $order->user_id || ! $order->store_id) {
            return;
        }
        $candidate = CustomerReferral::query()->where('store_id', $order->store_id)->where('customer_id', $order->user_id)
            ->whereNull('order_id')->where('expires_at', '>', now())->first();
        if (! $candidate || $candidate->terms['mode'] !== 'purchase') {
            $this->attachCustomOffer($candidate, $order);

            return;
        }
        DB::transaction(function () use ($candidate, $order): void {
            $program = ReferralProgram::query()->lockForUpdate()->findOrFail($candidate->program_id);
            $referral = CustomerReferral::query()->lockForUpdate()->findOrFail($candidate->id);
            if ($referral->order_id || $referral->status !== 'tracked' || $this->hasPurchased($order->store_id, $order->user_id, $order->id)) {
                return;
            }
            $eligible = max(0, (float) $order->sub_total - (float) $order->discount_amount);
            if ($eligible <= 0 || $eligible < (float) $referral->terms['minimum_order']) {
                return;
            }
            $amount = $this->reward($referral->terms, $eligible);
            if ((float) $program->reserved + $amount > (float) $program->budget) {
                $referral->forceFill(['status' => 'rejected', 'reason' => __('Reward budget exhausted.')])->save();

                return;
            }
            $this->reserve($program, $amount);
            $referral->forceFill(['order_id' => $order->id, 'status' => 'pending', 'amount' => $amount, 'reserved_amount' => $amount])->save();
            $this->attachCustomOffer($referral, $order);
        }, 3);
    }

    private function attachCustomOffer(?CustomerReferral $referral, Order $order): void
    {
        if (! $referral || $referral->terms['offer_type'] !== 'custom' || $referral->expires_at->isPast()) {
            return;
        }
        if ($this->hasPurchased($order->store_id, $order->user_id, $order->id)
            || (float) $order->sub_total - (float) $order->discount_amount < (float) $referral->terms['minimum_order']) {
            return;
        }
        $note = '[Referral offer #'.$referral->id.'] '.$referral->terms['offer_text'];
        if (! str_contains((string) $order->description, '[Referral offer #'.$referral->id.']')) {
            $order->description = trim($order->description."\n".$note);
            $order->save();
        }
    }

    public function reconcile(int $id): void
    {
        DB::transaction(function () use ($id): void {
            $candidate = CustomerReferral::find($id);
            if (! $candidate) {
                return;
            }
            $program = ReferralProgram::query()->lockForUpdate()->findOrFail($candidate->program_id);
            $referral = CustomerReferral::query()->lockForUpdate()->findOrFail($id);
            if (! in_array($referral->status, ['pending', 'available', 'paid'])) {
                return;
            }
            if ($referral->terms['mode'] === 'visit') {
                if ($referral->status !== 'paid' && $referral->available_at?->isPast()) {
                    $referral->status = 'available';
                    $referral->save();
                }

                return;
            }
            $order = $referral->order_id ? Order::query()->lockForUpdate()->find($referral->order_id) : null;
            if (! $order || in_array((string) $order->status, ['canceled', 'returned'])
                || in_array((string) $order->payment->status, ['refunded', 'fraud', 'failed', 'canceled'])) {
                $this->reject($program, $referral, __('Order cancelled, returned, or payment invalid.'));

                return;
            }
            if ($referral->status === 'paid') {
                if ((float) $order->payment->refunded_amount > 0 || (string) $order->status === 'partial_returned') {
                    $referral->forceFill(['status' => 'reversed', 'reason' => __('Order refunded after payout. Seller adjustment required; no money was automatically recovered.')])->save();
                }

                return;
            }
            // Never release while a return is being reviewed.
            if (OrderReturn::query()->where('order_id', $order->id)->whereNotIn('return_status', ['canceled', 'completed'])->exists()) {
                $referral->forceFill(['status' => 'pending', 'available_at' => null])->save();

                return;
            }
            $delivered = in_array((string) $order->shipment->status, ['delivered', 'audited']);
            if (! $delivered || ! in_array((string) $order->status, ['completed', 'partial_returned']) || (string) $order->payment->status !== 'completed') {
                $referral->forceFill(['status' => 'pending', 'available_at' => null])->save();

                return;
            }
            $returned = (float) DB::table('ec_order_return_items')->join('ec_order_returns', 'ec_order_returns.id', '=', 'ec_order_return_items.order_return_id')
                ->where('ec_order_returns.order_id', $order->id)->where('ec_order_returns.return_status', 'completed')->sum('ec_order_return_items.refund_amount');
            // Payment refunds can also happen without a return request. Use the larger recorded total.
            $returned = max($returned, (float) $order->payment->refunded_amount);
            $eligible = max(0, (float) $order->sub_total - (float) $order->discount_amount - $returned);
            if ($eligible <= 0 || $eligible < (float) $referral->terms['minimum_order']) {
                $this->reject($program, $referral, __('The retained order amount does not qualify.'));

                return;
            }
            $referral->amount = min((float) $referral->reserved_amount, $this->reward($referral->terms, $eligible));
            if (! $referral->qualified_at) {
                $referral->qualified_at = now();
            }
            $hold = max((int) $referral->terms['hold_days'], EcommerceHelper::getReturnableDays());
            $referral->available_at = $referral->qualified_at->copy()->addDays($hold);
            $referral->status = $referral->available_at->lte(now()) ? 'available' : 'pending';
            $referral->save();
        }, 3);
    }

    private function reject(ReferralProgram $program, CustomerReferral $referral, string $reason): void
    {
        if ($referral->paid_at) {
            $referral->forceFill(['status' => 'reversed', 'reason' => $reason.' '.__('Already paid: seller adjustment required.')])->save();

            return;
        }
        $program->reserved = max(0, round((float) $program->reserved - (float) $referral->reserved_amount, 2));
        $program->save();
        $referral->forceFill(['status' => 'rejected', 'amount' => 0, 'reserved_amount' => 0, 'reason' => $reason])->save();
    }

    public function recordPayout(int $id, int $storeId, string $reference, int $actor): void
    {
        DB::transaction(function () use ($id, $storeId, $reference, $actor): void {
            $candidate = CustomerReferral::query()->where('store_id', $storeId)->findOrFail($id);
            $program = ReferralProgram::query()->lockForUpdate()->findOrFail($candidate->program_id);
            $referral = CustomerReferral::query()->lockForUpdate()->findOrFail($id);
            $this->reconcile($id);
            $referral->refresh();
            $this->require($referral->status === 'available', __('Only available earnings can be marked paid.'));
            $unused = max(0, (float) $referral->reserved_amount - (float) $referral->amount);
            $program->reserved = max(0, round((float) $program->reserved - $unused, 2));
            $program->save();
            $referral->forceFill(['status' => 'paid', 'reserved_amount' => $referral->amount, 'payout_reference' => $reference, 'paid_at' => now(), 'paid_by' => $actor])->save();
        }, 3);
    }

    public function rejectReward(int $id, string $reason): void
    {
        DB::transaction(function () use ($id, $reason): void {
            $candidate = CustomerReferral::findOrFail($id);
            $program = ReferralProgram::query()->lockForUpdate()->findOrFail($candidate->program_id);
            $referral = CustomerReferral::query()->lockForUpdate()->findOrFail($id);
            $this->require(in_array($referral->status, ['tracked', 'pending', 'available']), __('Only unpaid referrals can be rejected.'));
            $this->reject($program, $referral, $reason);
            $referral->expires_at = now()->subSecond();
            $referral->save();
            if ($referral->coupon_id) {
                Discount::query()->whereKey($referral->coupon_id)->update(['end_date' => now()->subSecond()]);
            }
        }, 3);
    }

    private function require(bool $condition, string $message): void
    {
        if (! $condition) {
            throw ValidationException::withMessages(['referral' => $message]);
        }
    }
}
