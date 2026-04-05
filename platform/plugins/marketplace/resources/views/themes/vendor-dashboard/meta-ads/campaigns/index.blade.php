@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Campaigns</h4>
        <a href="{{ route('marketplace.vendor.meta-ads.campaigns.create') }}" class="btn btn-primary">
            <i class="ti ti-plus"></i> New Campaign
        </a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="alert alert-info d-flex align-items-start gap-2 mb-3">
        <i class="ti ti-info-circle fs-5 mt-1"></i>
        <div>
            <strong>Payment notice:</strong> Campaigns are created as <strong>PAUSED</strong> by default. Meta will only charge your ad account (INR) once you activate a campaign and it starts running.
            Ensure your ad account has a payment method at
            <a href="https://business.facebook.com/billing" target="_blank" class="alert-link">Meta Business Manager →</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($campaigns->isEmpty())
                <div class="p-5 text-center text-muted">
                    <i class="ti ti-speakerphone fs-1 d-block mb-2"></i>
                    No campaigns yet. <a href="{{ route('marketplace.vendor.meta-ads.campaigns.create') }}">Create your first campaign →</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr>
                            <th>Name</th><th>Objective</th><th>Budget</th><th>Status</th>
                            <th>Ad Sets</th><th>Spend</th><th>Impressions</th><th>Clicks</th><th></th>
                        </tr></thead>
                        <tbody>
                            @foreach($campaigns as $campaign)
                                <tr>
                                    <td><a href="{{ route('marketplace.vendor.meta-ads.campaigns.show', $campaign->id) }}">{{ $campaign->name }}</a></td>
                                    <td><small>{{ str_replace('OUTCOME_', '', $campaign->objective) }}</small></td>
                                    <td>
                                        @if($campaign->daily_budget)
                                            ₹{{ $campaign->daily_budget }}/day
                                        @elseif($campaign->lifetime_budget)
                                            ₹{{ $campaign->lifetime_budget }} total
                                        @else —
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $campaign->status === 'ACTIVE' ? 'success' : 'secondary' }}">
                                            {{ $campaign->status }}
                                        </span>
                                    </td>
                                    <td>{{ $campaign->ad_sets_count }}</td>
                                    <td>₹{{ number_format($campaign->spend, 2) }}</td>
                                    <td>{{ number_format($campaign->impressions) }}</td>
                                    <td>{{ number_format($campaign->clicks) }}</td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('marketplace.vendor.meta-ads.campaigns.edit', $campaign->id) }}"
                                               class="btn btn-sm btn-outline-secondary">Edit</a>
                                            <form action="{{ route('marketplace.vendor.meta-ads.campaigns.destroy', $campaign->id) }}"
                                                  method="POST" onsubmit="return confirm('Delete campaign?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">Del</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3">{{ $campaigns->links() }}</div>
            @endif
        </div>
    </div>
@endsection
