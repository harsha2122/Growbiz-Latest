@php
    page_title()->setTitle(__('Referrals'));
@endphp

@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    @if ($perReferralAmount > 0)
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <div class="h3 mb-1">₹{{ number_format($totalReferralEarnings, 2) }}</div>
                        <div class="text-muted">{{ __('Total Referral Earnings') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <div class="h3 mb-1">{{ $referrals->total() }}</div>
                        <div class="text-muted">{{ __('Total Referrals') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <div class="h3 mb-1">₹{{ number_format($perReferralAmount, 2) }}</div>
                        <div class="text-muted">{{ __('Earning per Referral') }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="mb-4">
        <h5 class="mb-1">{{ __('Your Referral Link') }}</h5>
        <p class="text-muted small mb-2">{{ __('Share this link so other sellers can join through you.') }}</p>
        <div class="input-group" style="max-width: 560px;">
            <input type="text" class="form-control" id="referral-link-input" value="{{ $referralLink }}" readonly>
            <button class="btn btn-outline-secondary" type="button" onclick="copyReferralLink()" id="copy-btn">
                <i class="ti ti-copy"></i> {{ __('Copy') }}
            </button>
        </div>
        <div class="mt-2">
            <span class="badge bg-secondary">{{ __('Code') }}: {{ $store->referral_code }}</span>
            <span class="badge bg-primary ms-2">{{ $referrals->total() }} {{ __('Referral(s)') }}</span>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">{{ __('Vendors Referred by You') }}</h6>
        </div>
        <div class="card-body p-0">
            @if ($referrals->isEmpty())
                <div class="p-4 text-center text-muted">
                    {{ __('No referrals yet. Share your link to invite other sellers!') }}
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Email') }}</th>
                                <th>{{ __('Joined') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($referrals as $index => $referral)
                                <tr>
                                    <td>{{ $referrals->firstItem() + $index }}</td>
                                    <td>{{ $referral->referee->name ?? '—' }}</td>
                                    <td>{{ $referral->referee->email ?? '—' }}</td>
                                    <td>{{ $referral->joined_at->format('d M Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $referrals->links() }}
                </div>
            @endif
        </div>
    </div>

    <script>
        function copyReferralLink() {
            var input = document.getElementById('referral-link-input');
            input.select();
            document.execCommand('copy');
            var btn = document.getElementById('copy-btn');
            btn.innerHTML = '<i class="ti ti-check"></i> {{ __("Copied!") }}';
            setTimeout(function () {
                btn.innerHTML = '<i class="ti ti-copy"></i> {{ __("Copy") }}';
            }, 2000);
        }
    </script>
@endsection
