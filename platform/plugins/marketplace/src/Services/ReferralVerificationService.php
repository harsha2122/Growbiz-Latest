<?php

namespace Botble\Marketplace\Services;

use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Customer;
use Botble\Marketplace\Models\CustomerReferral;
use Botble\Marketplace\Models\Enquiry;
use Botble\Marketplace\Models\ReferralEvent;
use Botble\Marketplace\Models\ReferralLink;
use Botble\Marketplace\Models\ReferralProgram;
use Botble\Marketplace\Models\ServiceCompletion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReferralVerificationService
{
    public function audit(CustomerReferral $referral, string $action, string $role, ?int $actor, array $details = []): void
    {
        DB::table('mp_referral_audits')->insert([
            'referral_id' => $referral->id, 'event_id' => $referral->event_id, 'action' => $action,
            'actor_role' => $role, 'actor_id' => $actor, 'details' => json_encode($details), 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function require(bool $valid, string $message): void
    {
        if (! $valid) {
            throw ValidationException::withMessages(['referral' => $message]);
        }
    }

    public function offline(int $storeId, int $sellerId, array $data): ReferralEvent
    {
        return DB::transaction(function () use ($storeId, $sellerId, $data) {
            $code = Str::upper(trim($data['code']));
            $referral = CustomerReferral::query()->where('store_id', $storeId)->where('pass_code', $code)->first();
            if (! $referral) {
                $link = ReferralLink::query()->where('short_code', $code)->whereHas('program', fn ($q) => $q->where('store_id', $storeId))->first();
                $customer = Customer::query()->where('email', $data['customer_email'] ?? '')->first();
                $this->require($link && $customer, __('Use this store’s referral pass, or a valid referral code and the customer’s registered email.'));
                $this->require(in_array($link->program->channel, ['offline', 'both']), __('This program does not accept offline activity.'));
                $referral = app(StoreReferralService::class)->claim($link, $customer, null, 'offline');
            }
            $program = ReferralProgram::query()->lockForUpdate()->findOrFail($referral->program_id);
            $referral = CustomerReferral::query()->lockForUpdate()->findOrFail($referral->id);
            $terms = $referral->terms;
            $this->require(in_array($terms['channel'] ?? 'online', ['offline', 'both']), __('This referral does not support offline activity.'));
            $this->require($referral->status === 'tracked' && ! $referral->event_id && ! $referral->order_id, __('This referral already has a qualifying activity or has ended. Only one reward is allowed.'));
            $this->require($referral->expires_at->isFuture(), __('The referral attribution period has expired.'));
            $kind = $terms['mode'] === 'purchase' ? 'offline_purchase' : 'offline_visit';
            $this->require(in_array($terms['mode'], ['visit', 'purchase']), __('Enquiry programs require an online enquiry.'));
            $this->require($referral->customer_id != $sellerId && $referral->referrer_id != $sellerId, __('Self-referrals are not eligible.'));
            $this->require($data['occurred_on'] >= $referral->created_at->toDateString() && $data['occurred_on'] <= today()->toDateString(), __('Activity must take place within the referral period, not before attribution or in the future.'));
            $amount = round(max(0, (float) ($data['subtotal'] ?? 0) - (float) ($data['discount'] ?? 0)), 2);
            if ($kind === 'offline_purchase') {
                $this->require($amount > 0 && $amount >= (float) $terms['minimum_order'] && ! empty($data['evidence_path']), __('A qualifying purchase amount and receipt are required.'));
            }
            $reward = app(StoreReferralService::class)->reward($terms, $amount);
            app(StoreReferralService::class)->reserve($program, $reward);
            $event = ReferralEvent::create([
                'referral_id' => $referral->id, 'store_id' => $storeId, 'kind' => $kind,
                'reference' => $data['reference'], 'occurred_on' => $data['occurred_on'],
                'subtotal' => $kind === 'offline_purchase' ? $data['subtotal'] : 0,
                'discount' => $kind === 'offline_purchase' ? ($data['discount'] ?? 0) : 0,
                'tax' => $kind === 'offline_purchase' ? ($data['tax'] ?? 0) : 0,
                'shipping' => $kind === 'offline_purchase' ? ($data['shipping'] ?? 0) : 0,
                'invoice_key' => $kind === 'offline_purchase' ? hash('sha256', preg_replace('/[^\pL\pN]/u', '', Str::upper($data['reference']))) : null,
                'evidence_path' => $data['evidence_path'] ?? null, 'receipt_hash' => $data['receipt_hash'] ?? null,
                'status' => 'awaiting_customer', 'submitted_by' => $sellerId,
            ]);
            $referral->forceFill(['event_id' => $event->id, 'status' => 'awaiting_customer', 'amount' => $reward, 'reserved_amount' => $reward])->save();
            $this->audit($referral, 'offline_submitted', 'seller', $sellerId, ['kind' => $kind, 'reference' => $data['reference']]);

            return $event;
        }, 3);
    }

    public function trackEnquiry(Enquiry $enquiry): void
    {
        if (! $enquiry->customer_id) {
            return;
        }
        $customer = Customer::find($enquiry->customer_id);
        if (! $customer?->confirmed_at || ! is_string($enquiry->email) || Str::lower($enquiry->email) !== Str::lower($customer->email)) {
            return;
        }
        $candidate = CustomerReferral::query()->where('store_id', $enquiry->store_id)->where('customer_id', $customer->id)
            ->where('status', 'tracked')->where('expires_at', '>', now())->first();
        if (! $candidate || $candidate->terms['mode'] !== 'enquiry') {
            return;
        }
        DB::transaction(function () use ($candidate, $enquiry, $customer): void {
            $program = ReferralProgram::query()->lockForUpdate()->findOrFail($candidate->program_id);
            $referral = CustomerReferral::query()->lockForUpdate()->findOrFail($candidate->id);
            if ($referral->status !== 'tracked' || $referral->event_id) {
                return;
            }
            $reward = app(StoreReferralService::class)->reward($referral->terms, 0);
            if ((float) $program->reserved + $reward > (float) $program->budget) {
                $referral->update(['status' => 'rejected', 'reason' => __('Reward budget exhausted.')]);

                return;
            }
            app(StoreReferralService::class)->reserve($program, $reward);
            $event = ReferralEvent::create([
                'referral_id' => $referral->id, 'store_id' => $enquiry->store_id, 'kind' => 'enquiry',
                'enquiry_id' => $enquiry->id, 'reference' => 'Enquiry #'.$enquiry->id, 'occurred_on' => today(),
                'status' => 'awaiting_admin', 'customer_confirmed_at' => now(), 'submitted_by' => $customer->id,
            ]);
            $referral->update(['event_id' => $event->id, 'status' => 'awaiting_approval', 'amount' => $reward, 'reserved_amount' => $reward]);
            $this->audit($referral, 'enquiry_submitted', 'customer', $customer->id, ['enquiry_id' => $enquiry->id]);
        }, 3);
    }

    private function locked(int $id): array
    {
        $candidate = ReferralEvent::findOrFail($id);
        $ref = CustomerReferral::findOrFail($candidate->referral_id);
        $program = ReferralProgram::query()->lockForUpdate()->findOrFail($ref->program_id);
        $ref = CustomerReferral::query()->lockForUpdate()->findOrFail($ref->id);
        $event = ReferralEvent::query()->lockForUpdate()->findOrFail($id);

        return [$program, $ref, $event];
    }

    public function confirm(int $id, int $customerId, bool $accept): void
    {
        DB::transaction(function () use ($id, $customerId, $accept): void {
            [$program, $referral, $event] = $this->locked($id);
            abort_unless($referral->customer_id == $customerId, 404);
            $this->require($event->status === 'awaiting_customer' && $referral->status === 'awaiting_customer', __('This request is no longer awaiting your confirmation.'));
            if ($accept) {
                $this->require(Customer::query()->whereKey($customerId)->whereNotNull('confirmed_at')->exists(), __('Verify your email before confirming an offline referral.'));
                $this->require($event->created_at->copy()->addDays(7)->isFuture(), __('The seven-day customer confirmation period has expired.'));
            }
            $event->update(['status' => $accept ? 'awaiting_admin' : 'rejected', 'customer_confirmed_at' => $accept ? now() : null, 'reason' => $accept ? null : __('Customer declined this activity.')]);
            if ($accept) {
                $referral->update(['status' => 'awaiting_approval']);
            } else {
                app(StoreReferralService::class)->reject($program, $referral, __('Customer declined this activity.'));
            }
            $this->audit($referral, $accept ? 'customer_confirmed' : 'customer_declined', 'customer', $customerId);
        }, 3);
    }

    public function expireUnconfirmed(int $id): void
    {
        DB::transaction(function () use ($id): void {
            [$program, $referral, $event] = $this->locked($id);
            if ($event->status !== 'awaiting_customer' || $event->created_at->copy()->addDays(7)->isFuture()) {
                return;
            }
            app(StoreReferralService::class)->reject($program, $referral, __('Customer confirmation was not received within seven days.'));
            $this->audit($referral, 'confirmation_expired', 'system', null);
        }, 3);
    }

    public function review(int $id, int $adminId, bool $approve, string $reason): void
    {
        DB::transaction(function () use ($id, $adminId, $approve, $reason): void {
            [$program, $referral, $event] = $this->locked($id);
            $this->require(in_array($event->status, ['awaiting_customer', 'awaiting_admin']), __('This activity has already been reviewed.'));
            $this->require(in_array($referral->status, ['awaiting_customer', 'awaiting_approval']), __('This referral can no longer be approved.'));
            if ($approve) {
                $this->require($event->status === 'awaiting_admin' && $event->customer_confirmed_at !== null, __('The customer must confirm this activity first.'));
                if ($event->kind === 'enquiry') {
                    $this->require($event->enquiry()->exists(), __('The enquiry no longer exists.'));
                    if (($referral->terms['enquiry_trigger'] ?? 'approved') === 'service_completed') {
                        $this->require(ServiceCompletion::query()->where('store_id', $referral->store_id)->where('customer_id', $referral->customer_id)->whereDate('completed_at', '<=', today())->exists(), __('Confirm the completed service in Profile Reviews & Services before approving this reward.'));
                    }
                }
                $days = $event->kind === 'offline_purchase' ? max((int) $referral->terms['hold_days'], EcommerceHelper::getReturnableDays()) : 1;
                $referral->update(['status' => 'pending', 'qualified_at' => now(), 'available_at' => now()->addDays($days)]);
            } else {
                app(StoreReferralService::class)->reject($program, $referral, $reason);
            }
            $event->update(['status' => $approve ? 'approved' : 'rejected', 'reviewed_by' => $adminId, 'reviewed_at' => now(), 'reason' => $reason]);
            $this->audit($referral, $approve ? 'admin_approved' : 'admin_rejected', 'admin', $adminId, ['reason' => $reason]);
        }, 3);
    }

    public function refund(int $id, int $adminId, float $amount, string $reason): void
    {
        DB::transaction(function () use ($id, $adminId, $amount, $reason): void {
            [$program, $referral, $event] = $this->locked($id);
            $this->require($event->kind === 'offline_purchase' && $event->status === 'approved', __('Only an approved offline purchase can have a refund recorded.'));
            $this->require($amount >= (float) $event->refunded_amount && $amount <= (float) $event->subtotal - (float) $event->discount, __('Enter the cumulative refunded merchandise amount, up to the eligible purchase amount.'));
            $event->update(['refunded_amount' => $amount]);
            $this->audit($referral, 'offline_refund', 'admin', $adminId, ['amount' => $amount, 'reason' => $reason]);
            $this->reconcileLocked($program, $referral);
        }, 3);
    }

    public function reconcileLocked(ReferralProgram $program, CustomerReferral $referral): void
    {
        $event = $referral->event;
        $valid = $event && $event->status === 'approved' && ($event->kind !== 'enquiry' || $event->enquiry()->exists());
        if (! $valid) {
            app(StoreReferralService::class)->reject($program, $referral, __('Verified referral activity was rejected or removed.'));

            return;
        }
        if ($event->kind === 'enquiry' && ($referral->terms['enquiry_trigger'] ?? 'approved') === 'service_completed'
            && ! ServiceCompletion::query()->where('store_id', $referral->store_id)->where('customer_id', $referral->customer_id)->whereDate('completed_at', '<=', today())->exists()) {
            app(StoreReferralService::class)->reject($program, $referral, __('The completed-service confirmation was removed.'));

            return;
        }
        $eligible = $event->eligibleAmount();
        if ($event->kind === 'offline_purchase' && ($eligible <= 0 || $eligible < (float) $referral->terms['minimum_order'])) {
            app(StoreReferralService::class)->reject($program, $referral, __('The retained offline purchase amount no longer qualifies.'));

            return;
        }
        if ($referral->paid_at) {
            if ((float) $event->refunded_amount > 0) {
                $referral->update(['status' => 'reversed', 'reason' => __('Offline purchase refunded after payout. Seller adjustment required; no money was automatically recovered.')]);
            }

            return;
        }
        if ($event->kind === 'offline_purchase') {
            $referral->amount = min((float) $referral->reserved_amount, app(StoreReferralService::class)->reward($referral->terms, $eligible));
        }
        $referral->status = $referral->available_at?->lte(now()) ? 'available' : 'pending';
        $referral->save();
    }
}
