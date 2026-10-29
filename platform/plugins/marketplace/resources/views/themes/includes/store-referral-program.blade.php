@php
    $referralProgram = \Botble\Marketplace\Models\ReferralProgram::query()->where('store_id', $store->id)->first();
    $pendingReferral = session('store_referrals.' . $store->id);
    $hasPendingReferral = $pendingReferral && ($pendingReferral['until'] ?? 0) > now()->timestamp;
    $myReferralOffer = auth('customer')->check() ? \Botble\Marketplace\Models\CustomerReferral::query()->where('store_id', $store->id)->where('customer_id', auth('customer')->id())->where('expires_at', '>', now())->with('coupon')->first() : null;
@endphp
@if ($referralProgram?->isActive() || $myReferralOffer)
    @include('plugins/marketplace::referral-program.styles')
    <section class="refer-panel mt-3" aria-label="{{ __('Refer & Earn') }}">
        <h2>{{ __('Share this store. Earn rewards.') }}</h2>
        @if (session('success_msg'))<p role="status">{{ session('success_msg') }}</p>@endif
        @if ($errors->any())<p class="text-danger" role="alert">{{ $errors->first() }}</p>@endif
        @if ($referralProgram?->isActive())
            <p class="refer-muted">{{ __('Earn') }} {{ $referralProgram->reward_type === 'percentage' ? rtrim(rtrim(number_format($referralProgram->reward_value, 2), '0'), '.') . '%' : format_price($referralProgram->reward_value) }} {{ $referralProgram->mode === 'visit' ? __('for each qualifying new, verified store visitor.') : __('when your friend’s first qualifying order is delivered, paid, and past the return period.') }}
            @if ($referralProgram->reward_cap) {{ __('Up to') }} {{ format_price($referralProgram->reward_cap) }} {{ __('per referral.') }}@endif</p>
            @if (in_array($referralProgram->offer_type, ['fixed', 'percentage']))
                <p>{{ __('Your friend gets') }} {{ $referralProgram->offer_type === 'percentage' ? $referralProgram->offer_value . '%' : format_price($referralProgram->offer_value) }} {{ __('off their first qualifying order.') }}</p>
            @elseif ($referralProgram->offer_type === 'custom')<p>{{ $referralProgram->offer_text }}</p>@endif
            <p class="refer-muted">{{ __('First-time store customers only. Sign-in and verified email required. Referral window: 30 days. Subject to remaining program budget.') }} @if ($referralProgram->minimum_order > 0){{ __('Minimum order') }}: {{ format_price($referralProgram->minimum_order) }}.@endif</p>
        @endif
        @if ($myReferralOffer)
            @if ($myReferralOffer->coupon)<p>{{ __('Your personal coupon') }}: <strong>{{ $myReferralOffer->coupon->code }}</strong></p>@endif
            @if ($myReferralOffer->terms['offer_type'] === 'custom')<p>{{ __('Your offer') }}: {{ $myReferralOffer->terms['offer_text'] }}</p>@endif
            <p class="refer-muted">{{ __('Use on your first qualifying order by') }} {{ $myReferralOffer->expires_at->format('d M Y') }}. {{ __('Minimum order') }}: {{ format_price($myReferralOffer->terms['minimum_order']) }}.</p>
        @endif
        <div class="refer-actions">
        @if (auth('customer')->check())
            @if ($hasPendingReferral && ! $myReferralOffer && $referralProgram?->isActive())<form method="post" action="{{ route('public.referral.claim', $store->id) }}">@csrf<button type="submit" class="refer-btn">{{ __('Unlock referral offer') }}</button></form>@endif
            @if ($referralProgram?->isActive() && $store->customer_id != auth('customer')->id())<form method="post" action="{{ route('public.referral.join', $store->id) }}">@csrf<button type="submit" class="refer-btn">{{ __('Get my referral link') }}</button></form>@endif
            <a href="{{ route('public.referral.account') }}" class="refer-muted">{{ __('My referrals & earnings') }}</a>
        @else
            <a class="refer-btn" href="{{ route('customer.login') }}">{{ __('Sign in to join') }}</a>
            <span class="refer-muted">{{ __('After signing in, return to this store to unlock the offer.') }}</span>
        @endif
        </div>
    </section>
@endif
