@php
    $referralProgram = \Botble\Marketplace\Models\ReferralProgram::query()->where('store_id', $store->id)->first();
    $pendingReferral = session('store_referrals.' . $store->id);
    $hasPendingReferral = $pendingReferral && ($pendingReferral['until'] ?? 0) > now()->timestamp;
    $myReferralOffer = auth('customer')->check() ? \Botble\Marketplace\Models\CustomerReferral::query()->where('store_id', $store->id)->where('customer_id', auth('customer')->id())->where('expires_at', '>', now())->whereNotIn('status', ['rejected', 'reversed'])->with('coupon')->first() : null;
@endphp
@if ($referralProgram?->isActive() || $myReferralOffer)
    @php
        $offerTerms = $myReferralOffer?->terms ?? $referralProgram->snapshot();
        $reward = (float) $offerTerms['reward_value'];
        if ($offerTerms['reward_type'] === 'fixed' && $offerTerms['reward_cap'] !== null) $reward = min($reward, (float) $offerTerms['reward_cap']);
        $rewardLabel = $offerTerms['reward_type'] === 'percentage' ? rtrim(rtrim(number_format($reward, 2), '0'), '.') . '%' : format_price($reward);
        $channel = $offerTerms['channel'] ?? 'online';
        $qualifier = match ($offerTerms['mode']) {
            'enquiry' => ($offerTerms['enquiry_trigger'] ?? 'approved') === 'service_completed' ? __('For a referred enquiry that becomes a completed service.') : __('For a new friend’s genuine, verified enquiry.'),
            'visit' => $channel === 'offline' ? __('When a new friend visits this store in person.') : ($channel === 'both' ? __('When a new friend makes a qualifying online or in-store visit.') : __('When a new friend makes a qualifying online visit.')),
            default => $channel === 'offline' ? __('When a new friend completes a verified in-store purchase.') : ($channel === 'both' ? __('When a new friend completes a qualifying online or in-store purchase.') : __('When a new friend’s first order is delivered and past the return period.')),
        };
        $termsUrl = route('public.referral.terms', ['program' => $referralProgram->id] + ($myReferralOffer ? ['referral' => $myReferralOffer->id] : []));
    @endphp
    @include('plugins/marketplace::referral-program.offer-styles')
    <section class="referral-offer" id="store-referral-program" aria-label="{{ __('Refer & Earn') }}">
        <div>
            <span class="referral-offer-eyebrow"><x-core::icon name="ti ti-sparkles" />{{ __('Refer & Earn') }}</span>
            <h2>{{ __('Earn :reward', ['reward' => $rewardLabel]) }}</h2>
            <p class="referral-offer-subtitle">{{ $qualifier }}@if ($offerTerms['reward_type'] === 'percentage' && $offerTerms['reward_cap']) {{ __('Up to :amount per referral.', ['amount' => format_price($offerTerms['reward_cap'])]) }}@endif</p>
            @if (in_array($offerTerms['offer_type'], ['fixed', 'percentage']))
                <p class="referral-offer-friend">{{ __('Your friend gets :offer off.', ['offer' => $offerTerms['offer_type'] === 'percentage' ? rtrim(rtrim(number_format($offerTerms['offer_value'], 2), '0'), '.') . '%' : format_price($offerTerms['offer_value'])]) }}</p>
            @elseif ($offerTerms['offer_type'] === 'custom')
                <p class="referral-offer-friend">{{ $offerTerms['offer_text'] }}</p>
            @endif
            <div class="referral-offer-actions">
                @if (auth('customer')->check())
                    @if ($hasPendingReferral && ! $myReferralOffer && $referralProgram?->isActive())
                        <form method="post" action="{{ route('public.referral.claim', $store->id) }}">@csrf<button class="referral-offer-button" type="submit">{{ __('Unlock referral offer') }} <span aria-hidden="true">↗</span></button></form>
                    @elseif ($referralProgram?->isActive() && $store->customer_id != auth('customer')->id())
                        <form method="post" action="{{ route('public.referral.join', $store->id) }}">@csrf<button class="referral-offer-button" type="submit">{{ __('Get my referral link') }} <span aria-hidden="true">↗</span></button></form>
                    @else
                        <a class="referral-offer-button" href="{{ route('public.referral.account') }}">{{ __('My referrals & earnings') }}</a>
                    @endif
                @else
                    <a class="referral-offer-button" href="{{ route('customer.login') }}">{{ $hasPendingReferral ? __('Sign in to unlock referral offer') : __('Sign in to get your referral link') }}</a>
                @endif
                <a href="{{ $termsUrl }}" data-referral-terms class="referral-offer-terms">{{ __('Terms & conditions') }}</a>
            </div>
        </div>
        <div>
            @if (!empty($offerTerms['offer_image']))
                {{ RvMedia::image($offerTerms['offer_image'], __('Referral offer'), attributes: ['class' => 'referral-offer-image', 'loading' => 'lazy']) }}
            @else
                @include('plugins/marketplace::referral-program.offer-art')
            @endif
        </div>
        @if (session('success_msg') || $errors->any() || $myReferralOffer)
            <div class="referral-offer-unlocked" role="status">
                @if (session('success_msg'))<span>{{ session('success_msg') }}</span>@endif
                @if ($errors->any())<span>{{ $errors->first() }}</span>@endif
                @if ($myReferralOffer?->coupon)<span>{{ __('Your personal coupon') }}: <strong>{{ $myReferralOffer->coupon->code }}</strong></span>@endif
                @if ($myReferralOffer && in_array($channel, ['offline', 'both']))<a href="{{ route('public.referral.pass', $myReferralOffer->pass_code) }}">{{ __('Show my in-store referral pass') }}</a>@endif
            </div>
        @endif
    </section>
@endif
