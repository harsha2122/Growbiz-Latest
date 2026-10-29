@extends(EcommerceHelper::viewPath('customers.master'))
@section('title', __('Refer & Earn'))
@section('content')
@include('plugins/marketplace::referral-program.styles')
<h1 class="h3 mb-3">{{ __('Refer & Earn') }}</h1>
@if (session('success_msg'))<div class="alert alert-success">{{ session('success_msg') }}</div>@endif
@if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div class="refer-stats">@foreach (['pending' => __('Pending'), 'available' => __('Available'), 'paid' => __('Paid')] as $key => $label)<div class="refer-stat"><strong>{{ format_price($totals[$key] ?? 0) }}</strong><span class="refer-muted">{{ $label }}</span></div>@endforeach</div>
<div class="refer-panel"><h2>{{ __('Your referral links') }}</h2><p class="refer-muted">{{ __('Join a program from a store page, then share your personal link. Only new, verified customers qualify. Sellers fund and pay rewards directly.') }}</p>
@forelse ($links as $link)
    <div class="mb-4"><strong>{{ $link->program->store?->name }}</strong> <span class="refer-badge">{{ $link->program->isActive() ? __('Active') : __('Paused / expired') }}</span>
        <label class="visually-hidden" for="refer-link-{{ $link->id }}">{{ __('Your shareable referral link') }}</label>
        <input id="refer-link-{{ $link->id }}" class="refer-link mt-2" readonly value="{{ route('public.referral.landing', $link->token) }}" onclick="this.select()">
        <button type="button" class="refer-btn mt-2" data-copy-referral="refer-link-{{ $link->id }}">{{ __('Copy link') }}</button>
        <small class="refer-muted ms-2" data-copy-status role="status"></small>
    </div>
@empty<p class="refer-muted">{{ __('Visit a participating store and choose Get my referral link.') }}</p>@endforelse
</div>
@if ($offers->isNotEmpty())<div class="refer-panel"><h2>{{ __('Offers you unlocked') }}</h2>@foreach ($offers as $offer)<div class="mb-3"><strong>{{ $offer->store?->name }}</strong>
@if ($offer->coupon)<div>{{ __('Coupon') }}: <strong>{{ $offer->coupon->code }}</strong></div>@endif
@if ($offer->terms['offer_type'] === 'custom')<p>{{ $offer->terms['offer_text'] }}</p>@endif
<p class="refer-muted">{{ __('First order only. Minimum order') }}: {{ format_price($offer->terms['minimum_order']) }} · {{ __('Expires') }} {{ $offer->expires_at->format('d M Y') }}</p></div>@endforeach</div>@endif
<div class="refer-panel"><h2>{{ __('Your earnings') }}</h2>@include('plugins/marketplace::referral-program.ledger'){{ $referrals->links() }}</div>
<script>
    document.querySelectorAll('[data-copy-referral]').forEach(function (button) {
        button.addEventListener('click', async function () {
            var input = document.getElementById(button.dataset.copyReferral);
            var status = button.nextElementSibling;
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
