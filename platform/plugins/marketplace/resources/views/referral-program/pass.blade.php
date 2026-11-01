@extends(EcommerceHelper::viewPath('customers.master'))
@section('title', __('Your in-store referral pass'))
@section('content')
@include('plugins/marketplace::referral-program.styles')
@include('plugins/marketplace::referral-program.offer-styles')
@if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
@if (session('success_msg'))<div class="alert alert-success">{{ session('success_msg') }}</div>@endif
<div class="refer-panel text-center">
    <span class="refer-badge">{{ __('Your referral pass') }}</span>
    <h2 class="mt-3">{{ $referral->store?->name }}</h2>
    <p class="refer-muted">{{ __('Show this QR or code at the store. A scan alone does not approve a reward. You confirm the activity and admin verifies it.') }}</p>
    <div style="max-width:240px;margin:16px auto" aria-label="{{ __('Referral pass QR code') }}">{!! $svg !!}</div>
    <strong style="font-size:20px;letter-spacing:.08em;overflow-wrap:anywhere">{{ $referral->pass_code }}</strong>
    <p class="refer-muted mt-2">{{ __('Attribution deadline') }}: {{ $referral->expires_at->format('d M Y') }}</p>
    <a href="{{ route('public.referral.terms', ['program' => $referral->program_id, 'referral' => $referral->id]) }}" data-referral-terms>{{ __('Saved offer terms & conditions') }}</a>
</div>
@php
    $activities = $events;
@endphp
@include('plugins/marketplace::referral-program.activity-confirmations')
@endsection
