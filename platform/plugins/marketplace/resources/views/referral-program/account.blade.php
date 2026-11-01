@extends(EcommerceHelper::viewPath('customers.master'))
@section('title', __('Refer & Earn'))
@section('content')
@include('plugins/marketplace::referral-program.styles')
@include('plugins/marketplace::referral-program.customer-styles')
@include('plugins/marketplace::referral-program.offer-styles')
<div class="customer-refer-account">
<p class="refer-intro">{{ __('Share stores you love. Keep track of every reward.') }}</p>
@if (session('success_msg'))<div class="alert alert-success">{{ session('success_msg') }}</div>@endif
@if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div class="refer-stats">@foreach (['pending' => __('Pending'), 'available' => __('Available'), 'paid' => __('Paid')] as $key => $label)<div class="refer-stat"><strong>{{ format_price($totals[$key] ?? 0) }}</strong><span class="refer-muted">{{ $label }}</span></div>@endforeach</div>
<div class="refer-panel"><h2>{{ __('Your referral links') }}</h2><p class="refer-muted">{{ __('Share your link with a friend. Rewards are paid directly by the participating store.') }}</p>
@forelse ($links as $link)
    <div class="refer-store-card"><div class="refer-store-heading"><strong>{{ $link->program->store?->name }}</strong> <span class="refer-badge {{ $link->program->isActive() ? '' : 'is-paused' }}">{{ $link->program->isActive() ? __('Active') : __('Paused / expired') }}</span></div>
        @if ($link->program->isActive())
        <label class="visually-hidden" for="refer-link-{{ $link->id }}">{{ __('Your shareable referral link') }}</label>
        <div class="refer-copy-row"><input id="refer-link-{{ $link->id }}" class="refer-link mt-2" readonly value="{{ route('public.referral.landing', $link->token) }}" onclick="this.select()">
        <button type="button" class="refer-btn mt-2" data-copy-referral="refer-link-{{ $link->id }}">{{ __('Copy link') }}</button>
        </div>
        <div class="d-flex flex-wrap gap-3 align-items-center mt-2"><small>{{ __('Your referral code') }}: <strong>{{ $link->short_code }}</strong></small><details class="refer-muted"><summary>{{ __('Share QR code') }}</summary><img src="{{ route('public.referral.qr', $link->token) }}" width="180" height="180" alt="{{ __('Referral link QR code') }}" loading="lazy"></details></div>
        <small class="refer-muted d-block mt-2" data-copy-status role="status" aria-live="polite"></small>
        @else
        <p class="refer-muted mt-2 mb-0">{{ __('Sharing is unavailable while this program is paused or expired. Your existing earnings are shown below.') }}</p>
        @endif
    </div>
@empty<div class="refer-empty"><x-core::icon name="ti ti-link" /><p>{{ __('Your first referral starts here') }}</p><span>{{ __('Visit a participating store and choose Get my referral link.') }}</span><a href="{{ route('public.stores') }}" class="refer-btn mt-3">{{ __('Explore stores') }}</a></div>@endforelse
</div>
@include('plugins/marketplace::referral-program.activity-confirmations')
@if ($offers->isNotEmpty())<div class="refer-panel"><h2>{{ __('Offers you unlocked') }}</h2>@foreach ($offers as $offer)<div class="mb-3"><strong>{{ $offer->store?->name }}</strong>
@if ($offer->coupon)<div>{{ __('Coupon') }}: <strong>{{ $offer->coupon->code }}</strong></div>@endif
@if ($offer->terms['offer_type'] === 'custom')<p>{{ $offer->terms['offer_text'] }}</p>@endif
@if (in_array($offer->terms['channel'] ?? 'online', ['offline', 'both']))<p><a class="refer-btn" href="{{ route('public.referral.pass', $offer->pass_code) }}">{{ __('Show my in-store pass') }}</a></p>@endif
<a data-referral-terms href="{{ route('public.referral.terms', ['program' => $offer->program_id, 'referral' => $offer->id]) }}">{{ __('Saved terms & conditions') }}</a>
<p class="refer-muted">@if (in_array($offer->terms['offer_type'], ['fixed', 'percentage'])){{ __('First order only. Minimum order') }}: {{ format_price($offer->terms['minimum_order']) }}@else{{ __('See the saved terms for offer eligibility.') }}@endif · {{ __('Expires') }} {{ $offer->expires_at->format('d M Y') }}</p></div>@endforeach</div>@endif
<div class="refer-panel"><h2>{{ __('Your earnings') }}</h2>@include('plugins/marketplace::referral-program.ledger'){{ $referrals->links() }}</div>
</div>
<script>
    document.querySelectorAll('[data-copy-referral]').forEach(function (button) {
        button.addEventListener('click', async function () {
            var input = document.getElementById(button.dataset.copyReferral);
            var status = button.closest('.refer-store-card').querySelector('[data-copy-status]');
            input.select();
            try {
                await navigator.clipboard.writeText(input.value);
                status.textContent = {{ Illuminate\Support\Js::from(__('Link copied')) }};
            } catch (error) {
                status.textContent = {{ Illuminate\Support\Js::from(__('Link selected. Copy it to share.')) }};
            }
        });
    });
</script>
@endsection
