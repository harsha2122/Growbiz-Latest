@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))
@section('content')
@include('plugins/marketplace::referral-program.styles')
@if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
@if (session('success_msg'))<div class="alert alert-success">{{ session('success_msg') }}</div>@endif
<form method="post" action="{{ route('marketplace.vendor.referral-activity.submit') }}" enctype="multipart/form-data" class="refer-panel">
    @csrf
    <h2>{{ __('Record an in-store referral') }}</h2>
    <p class="refer-muted">{{ __('Ask for the customer’s referral pass. If they only have a friend’s referral code, enter that code and their registered email. The customer must confirm within seven days, then admin verifies it. One reward per referral. Enter amounts in the platform’s base currency.') }}</p>
    <div class="refer-grid">
        <div class="refer-field"><label for="offline-code">{{ __('Referral pass or referrer code') }}</label><input id="offline-code" name="code" maxlength="40" required value="{{ old('code', $code) }}"></div>
        <div class="refer-field"><label for="offline-email">{{ __('Customer email (required when using a referrer code)') }}</label><input id="offline-email" name="customer_email" type="email" value="{{ old('customer_email') }}"></div>
        <div class="refer-field"><label for="offline-ref">{{ __('Invoice / visit register reference') }}</label><input id="offline-ref" name="reference" maxlength="191" required value="{{ old('reference') }}"></div>
        <div class="refer-field"><label for="offline-date">{{ __('Activity date') }}</label><input id="offline-date" type="date" name="occurred_on" max="{{ today()->toDateString() }}" required value="{{ old('occurred_on', today()->toDateString()) }}"></div>
        @foreach (['subtotal' => __('Merchandise subtotal (purchase only)'), 'discount' => __('Discount on merchandise'), 'tax' => __('Tax on receipt'), 'shipping' => __('Shipping on receipt')] as $key => $label)
            <div class="refer-field"><label for="offline-{{ $key }}">{{ $label }}</label><input id="offline-{{ $key }}" type="number" name="{{ $key }}" min="0" max="10000000" step="0.01" value="{{ old($key, 0) }}"></div>
        @endforeach
        <div class="refer-field"><label for="offline-receipt">{{ __('Purchase receipt / invoice') }}</label><input id="offline-receipt" name="receipt" type="file" accept="image/jpeg,image/png,application/pdf"><small class="refer-muted">{{ __('Required for purchases. JPG, PNG or PDF, up to 5 MB. Stored privately. Enter the actual discount granted offline; online coupons do not apply automatically at your till.') }}</small></div>
    </div>
    <button class="refer-btn mt-3" type="submit">{{ __('Submit for customer confirmation') }}</button>
</form>
<div class="refer-panel"><h2>{{ __('In-store activity history') }}</h2><div style="overflow-x:auto"><table class="refer-table"><thead><tr><th>{{ __('Customer') }}</th><th>{{ __('Activity / reference') }}</th><th>{{ __('Eligible amount') }}</th><th>{{ __('Status') }}</th><th>{{ __('Receipt') }}</th></tr></thead><tbody>
@forelse ($events as $event)
<tr><td>{{ $event->referral->customer?->name }}</td><td>{{ __(ucwords(str_replace('_', ' ', $event->kind))) }}<div class="refer-muted">{{ $event->reference }} · {{ $event->occurred_on->format('d M Y') }}</div></td><td>{{ format_price($event->eligibleAmount()) }}</td><td>{{ __(ucwords(str_replace('_', ' ', $event->status))) }}<div class="refer-muted">{{ $event->reason }}</div></td><td>@if ($event->evidence_path)<a href="{{ route('marketplace.vendor.referral-activity.evidence', $event->id) }}">{{ __('Download receipt') }}</a>@endif</td></tr>
@empty<tr><td colspan="5">{{ __('No in-store activity yet.') }}</td></tr>@endforelse
</tbody></table></div>{{ $events->links() }}</div>
@endsection
