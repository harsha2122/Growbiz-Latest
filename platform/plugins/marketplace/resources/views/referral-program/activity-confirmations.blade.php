@if ($activities->isNotEmpty())
<div class="refer-panel"><h2>{{ __('Your store visits & purchases') }}</h2><p class="refer-muted">{{ __('Confirm only activity you recognise. Confirm within seven days. This credits the referrer shown below; it does not make a payment or charge your account.') }}</p>
@foreach ($activities as $activity)
    <div class="refer-store-card">
        <strong>{{ $activity->store?->name }}</strong><span class="refer-muted"> · {{ $activity->reference }}</span>
        <p class="refer-muted mt-2">{{ __(ucwords(str_replace('_', ' ', $activity->kind))) }} · {{ $activity->occurred_on->format('d M Y') }} · {{ __('Referred by') }} {{ $activity->referral->referrer?->name }}</p>
        @if ($activity->kind === 'offline_purchase')<p>{{ __('Merchandise after discounts') }}: <strong>{{ format_price((float)$activity->subtotal - (float)$activity->discount) }}</strong> · {{ __('Receipt total') }}: {{ format_price((float)$activity->subtotal - (float)$activity->discount + (float)$activity->tax + (float)$activity->shipping) }}</p>@endif
        @if ($activity->evidence_path)<a class="refer-muted" href="{{ route('public.referral.evidence', $activity->id) }}">{{ __('Download receipt') }}</a>@endif
        @if ($activity->status === 'awaiting_customer')
            <form method="post" action="{{ route('public.referral.confirm', $activity->id) }}" class="refer-actions">@csrf<button class="refer-btn" type="submit" name="accept" value="1">{{ __('Yes, this was my visit / purchase') }}</button><button class="btn btn-outline-secondary" type="submit" name="accept" value="0">{{ __('I do not recognise this') }}</button></form>
        @else
            <p class="refer-muted">{{ __(ucwords(str_replace('_', ' ', $activity->status))) }} @if ($activity->reason) · {{ $activity->reason }}@endif</p>
        @endif
    </div>
@endforeach
</div>
@endif
