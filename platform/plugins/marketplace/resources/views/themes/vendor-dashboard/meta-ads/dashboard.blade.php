@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Meta Ads — Dashboard</h4>
        <a href="{{ route('marketplace.vendor.meta-ads.campaigns.create') }}" class="btn btn-primary">
            <i class="ti ti-plus"></i> New Campaign
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(!$adAccount || !$adAccount->is_connected)
        <div class="alert alert-warning">
            Your Facebook account is not connected.
            <a href="{{ route('marketplace.vendor.meta-ads.connection') }}" class="alert-link">Connect now →</a>
        </div>
    @else
        <div class="alert alert-success">
            Connected as <strong>{{ $adAccount->fb_user_name }}</strong>
            @if($adAccount->ad_account_name)
                · Ad Account: <strong>{{ $adAccount->ad_account_name }}</strong>
                @if($adAccount->token_expires_at)
                    · Token expires: <strong>{{ $adAccount->token_expires_at->format('d M Y') }}</strong>
                @endif
            @endif
        </div>
    @endif

    {{-- Payment / Account Status Notice --}}
    @if($hasPaymentMethod === false)
        <div class="alert alert-danger d-flex align-items-start gap-2">
            <i class="ti ti-credit-card-off fs-5 mt-1 flex-shrink-0"></i>
            <div>
                <strong>No payment method found on your ad account.</strong>
                Campaigns cannot be pushed to Meta or go live until you add a payment method.
                <a href="https://adsmanager.facebook.com/billing" target="_blank" class="alert-link fw-semibold ms-1">
                    Add payment method on Meta Ads Manager →
                </a>
                <div class="mt-1 small text-muted">
                    Go to: Ads Manager → Account Overview → Set up billing → Add card/UPI
                </div>
            </div>
        </div>
    @elseif($accountStatus === 3)
        <div class="alert alert-warning d-flex align-items-start gap-2">
            <i class="ti ti-alert-triangle fs-5 mt-1 flex-shrink-0"></i>
            <div>
                <strong>Your ad account has an unpaid balance.</strong>
                Campaigns are paused until the overdue amount is settled.
                <a href="https://adsmanager.facebook.com/billing" target="_blank" class="alert-link ms-1">Settle payment →</a>
            </div>
        </div>
    @elseif($accountStatus !== null && $accountStatus !== 1)
        <div class="alert alert-warning d-flex align-items-start gap-2">
            <i class="ti ti-alert-circle fs-5 mt-1 flex-shrink-0"></i>
            <div>
                <strong>Your Meta ad account is not active</strong> (status: {{ $accountStatus }}).
                <a href="https://adsmanager.facebook.com" target="_blank" class="alert-link ms-1">Check account in Ads Manager →</a>
            </div>
        </div>
    @elseif($hasPaymentMethod === true)
        <div class="alert alert-success d-flex align-items-start gap-2">
            <i class="ti ti-circle-check fs-5 mt-1 flex-shrink-0"></i>
            <div>
                <strong>Ad account is ready.</strong>
                Payment method is configured and your account is active. Campaigns you activate will go live on Meta.
            </div>
        </div>
    @else
        <div class="alert alert-info d-flex align-items-start gap-2">
            <i class="ti ti-info-circle fs-5 mt-1 flex-shrink-0"></i>
            <div>
                <strong>Before activating any campaign:</strong>
                Make sure your Facebook Ad Account has a valid payment method set up. Meta will charge your ad account in <strong>INR</strong> as ads run.
                <a href="https://adsmanager.facebook.com/billing" target="_blank" class="alert-link ms-1">Set up payment on Meta →</a>
            </div>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted mb-1">Total Campaigns</div>
                    <h3 class="mb-0">{{ $totalCampaigns }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted mb-1">Active Campaigns</div>
                    <h3 class="mb-0 text-success">{{ $activeCampaigns }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted mb-1">Total Spend</div>
                    <h3 class="mb-0">₹{{ number_format($totalSpend, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted mb-1">Avg CTR</div>
                    <h3 class="mb-0">{{ $avgCtr }}%</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5 class="mb-0">Recent Campaigns</h5>
            <a href="{{ route('marketplace.vendor.meta-ads.campaigns.index') }}">View all →</a>
        </div>
        <div class="card-body p-0">
            @if($recentCampaigns->isEmpty())
                <div class="p-4 text-center text-muted">No campaigns yet.
                    <a href="{{ route('marketplace.vendor.meta-ads.campaigns.create') }}">Create one →</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr>
                            <th>Name</th><th>Objective</th><th>Status</th><th>Spend</th><th>Impressions</th><th>Clicks</th>
                        </tr></thead>
                        <tbody>
                            @foreach($recentCampaigns as $campaign)
                                <tr>
                                    <td><a href="{{ route('marketplace.vendor.meta-ads.campaigns.show', $campaign->id) }}">{{ $campaign->name }}</a></td>
                                    <td>{{ str_replace('OUTCOME_', '', $campaign->objective) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $campaign->status === 'ACTIVE' ? 'success' : 'secondary' }}">
                                            {{ $campaign->status }}
                                        </span>
                                    </td>
                                    <td>₹{{ number_format($campaign->spend, 2) }}</td>
                                    <td>{{ number_format($campaign->impressions) }}</td>
                                    <td>{{ number_format($campaign->clicks) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
