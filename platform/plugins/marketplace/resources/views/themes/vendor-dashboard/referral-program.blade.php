@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))
@section('content')
@include('plugins/marketplace::referral-program.styles')
<p class="refer-muted mb-4">{{ __('Reward customers for bringing new customers to your store.') }}</p>
@if (session('success_msg'))<div class="alert alert-success">{{ session('success_msg') }}</div>@endif
@if ($errors->any())<div class="alert alert-danger" role="alert">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<div class="refer-stats">
    <div class="refer-stat"><strong>{{ format_price($program->budget ?? 0) }}</strong><span class="refer-muted">{{ __('Program budget') }}</span></div>
    <div class="refer-stat"><strong>{{ format_price($program->reserved ?? 0) }}</strong><span class="refer-muted">{{ __('Reserved and paid') }}</span></div>
    <div class="refer-stat"><strong>{{ $referrals->total() }}</strong><span class="refer-muted">{{ __('Customer referrals') }}</span></div>
</div>
<form method="post" action="{{ route('marketplace.vendor.refer-earn.save') }}" class="refer-panel">
    @csrf
    <h2>{{ __('Your referral program') }}</h2>
    <p class="refer-muted">{{ __('Rewards are funded and paid by you. The budget limits commitments; it does not deposit or transfer money. Amounts use the platform’s base currency.') }}</p>
    <div class="refer-grid">
        <div class="refer-field"><label for="ref-enabled">{{ __('Program status') }}</label><select id="ref-enabled" name="enabled"><option value="0">{{ __('Paused') }}</option><option value="1" @selected(old('enabled', $program->enabled))>{{ __('Active') }}</option></select></div>
        <div class="refer-field"><label for="ref-mode">{{ __('Reward for') }}</label><select id="ref-mode" name="mode"><option value="purchase">{{ __('Delivered purchase') }}</option><option value="visit" @selected(old('mode', $program->mode) === 'visit')>{{ __('Verified store visit') }}</option></select></div>
        <div class="refer-field"><label for="ref-reward-type">{{ __('Referrer reward type') }}</label><select id="ref-reward-type" name="reward_type"><option value="fixed">{{ __('Fixed amount') }}</option><option value="percentage" @selected(old('reward_type', $program->reward_type) === 'percentage')>{{ __('Percentage of eligible order') }}</option></select><small class="refer-muted">{{ __('Store visits support fixed rewards only.') }}</small></div>
        <div class="refer-field"><label for="ref-value">{{ __('Reward amount / percentage') }}</label><input id="ref-value" name="reward_value" type="number" min="0.01" max="1000000" step="0.01" required value="{{ old('reward_value', $program->reward_value) }}"></div>
        <div class="refer-field" data-purchase-field><label for="ref-cap">{{ __('Maximum reward per referral (optional)') }}</label><input id="ref-cap" name="reward_cap" type="number" min="0.01" max="1000000" step="0.01" value="{{ old('reward_cap', $program->reward_cap) }}"></div>
        <div class="refer-field"><label for="ref-budget">{{ __('Total reward budget') }}</label><input id="ref-budget" name="budget" type="number" min="{{ $program->reserved ?? 0 }}" max="10000000" step="0.01" required value="{{ old('budget', $program->budget ?? 0) }}"></div>
        <div class="refer-field"><label for="ref-offer">{{ __('Referred customer offer') }}</label><select id="ref-offer" name="offer_type">@foreach (['none' => __('No additional offer'), 'fixed' => __('Fixed discount'), 'percentage' => __('Percentage discount'), 'custom' => __('Custom offer / free gift')] as $value => $label)<option value="{{ $value }}" @selected(old('offer_type', $program->offer_type) === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="refer-field" data-discount-offer-field><label for="ref-offer-value">{{ __('Discount amount / percentage') }}</label><input id="ref-offer-value" name="offer_value" type="number" min="0" max="1000000" step="0.01" value="{{ old('offer_value', $program->offer_value ?? 0) }}"></div>
        <div class="refer-field"><label for="ref-min">{{ __('Minimum eligible order amount') }}</label><input id="ref-min" name="minimum_order" type="number" min="0" max="10000000" step="0.01" required value="{{ old('minimum_order', $program->minimum_order ?? 0) }}"><small class="refer-muted">{{ __('After discounts, excluding tax and shipping.') }}</small></div>
        <div class="refer-field" data-purchase-field><label for="ref-hold">{{ __('Purchase reward waiting period (days)') }}</label><input id="ref-hold" name="hold_days" type="number" min="{{ $returnDays }}" max="365" required value="{{ old('hold_days', max($returnDays, $program->hold_days ?? 0)) }}"><small class="refer-muted">{{ __('Starts after confirmed delivery and payment. At least the platform return window. Visit rewards wait 24 hours.') }}</small></div>
        <div class="refer-field" data-custom-offer-field><label for="ref-text">{{ __('Custom offer details') }}</label><textarea id="ref-text" name="offer_text" maxlength="500" rows="3">{{ old('offer_text', $program->offer_text) }}</textarea><small class="refer-muted">{{ __('For example: Free installation on your first order. You fulfil custom offers; details appear on the order.') }}</small></div>
        <div class="refer-field"><label for="ref-end">{{ __('Program expiry (optional)') }}</label><input id="ref-end" name="ends_at" type="datetime-local" value="{{ old('ends_at', $program->ends_at?->format('Y-m-d\TH:i')) }}"><small class="refer-muted">{{ __('Store timezone: :zone. Existing referral terms remain valid for 30 days.', ['zone' => config('app.timezone')]) }}</small></div>
    </div>
    <div class="refer-actions"><button class="refer-btn" type="submit">{{ __('Save program') }}</button></div>
    <p class="refer-muted mt-3 mb-0">{{ __('New customers must sign in and verify their email. Self-referrals and repeat rewards are blocked. Purchase rewards exclude shipping, tax, discounts, and refunds. Discounts cannot combine with promotions or flash sales.') }}</p>
</form>
<div class="refer-panel"><h2>{{ __('Referrals & payments') }}</h2><p class="refer-muted">{{ __('Pay available rewards directly to the referrer, then record the transaction reference below. This records a payment; it does not send money.') }}</p>
@include('plugins/marketplace::referral-program.ledger', ['seller' => true])
{{ $referrals->links() }}
</div>
<script>
(function () {
    var mode = document.getElementById('ref-mode');
    var reward = document.getElementById('ref-reward-type');
    var offer = document.getElementById('ref-offer');
    function update() {
        var visit = mode.value === 'visit';
        reward.querySelector('[value="percentage"]').disabled = visit;
        if (visit) reward.value = 'fixed';
        document.querySelectorAll('[data-purchase-field]').forEach(function (field) { field.hidden = visit; });
        document.querySelector('[data-custom-offer-field]').hidden = offer.value !== 'custom';
        document.querySelector('[data-discount-offer-field]').hidden = !['fixed', 'percentage'].includes(offer.value);
        document.getElementById('ref-text').required = offer.value === 'custom';
    }
    mode.addEventListener('change', update);
    offer.addEventListener('change', update);
    update();
})();
</script>
@endsection
