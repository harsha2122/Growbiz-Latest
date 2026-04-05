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

    {{-- Payment Notice --}}
    <div class="alert alert-info d-flex align-items-start gap-2">
        <i class="ti ti-info-circle fs-5 mt-1"></i>
        <div>
            <strong>Before activating any campaign:</strong>
            Make sure your Facebook Ad Account has a valid payment method set up.
            Meta will charge your ad account in <strong>INR</strong> as your ads run.
            <a href="https://business.facebook.com/billing" target="_blank" class="alert-link ms-1">
                Set up payment method on Meta →
            </a>
        </div>
    </div>

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
                                    <td>{{ $campaign->objective }}</td>
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
