@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('marketplace.vendor.meta-ads.campaigns.index') }}" class="text-muted small">← Campaigns</a>
            <h4 class="mb-0">{{ $campaign->name }}</h4>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('marketplace.vendor.meta-ads.campaigns.ad-sets.create', $campaign->id) }}" class="btn btn-primary">
                <i class="ti ti-plus"></i> New Ad Set
            </a>
            <a href="{{ route('marketplace.vendor.meta-ads.campaigns.edit', $campaign->id) }}" class="btn btn-outline-secondary">Edit</a>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="row g-3 mb-4">
        <div class="col-sm-4"><div class="card card-body">
            <div class="text-muted small">Objective</div>
            <strong>{{ str_replace('OUTCOME_', '', $campaign->objective) }}</strong>
        </div></div>
        <div class="col-sm-4"><div class="card card-body">
            <div class="text-muted small">Status</div>
            <span class="badge bg-{{ $campaign->status === 'ACTIVE' ? 'success' : 'warning' }} text-dark">{{ $campaign->status }}</span>
        </div></div>
        <div class="col-sm-4"><div class="card card-body">
            <div class="text-muted small">Total Spend</div>
            <strong>₹{{ number_format($campaign->spend, 2) }}</strong>
        </div></div>
    </div>

    <div class="card">
        <div class="card-header"><h5 class="mb-0">Ad Sets ({{ $campaign->adSets->count() }})</h5></div>
        <div class="card-body p-0">
            @if($campaign->adSets->isEmpty())
                <div class="p-4 text-center text-muted">
                    No ad sets yet.
                    <a href="{{ route('marketplace.vendor.meta-ads.campaigns.ad-sets.create', $campaign->id) }}">Create one →</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Name</th><th>Status</th><th>Budget/day</th><th>Ads</th><th></th></tr></thead>
                        <tbody>
                            @foreach($campaign->adSets as $adSet)
                                <tr>
                                    <td><a href="{{ route('marketplace.vendor.meta-ads.ad-sets.show', $adSet->id) }}">{{ $adSet->name }}</a></td>
                                    <td><span class="badge bg-{{ $adSet->status === 'ACTIVE' ? 'success' : 'warning' }} text-dark">{{ $adSet->status }}</span></td>
                                    <td>₹{{ $adSet->daily_budget }}</td>
                                    <td>{{ $adSet->ads_count }}</td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('marketplace.vendor.meta-ads.ad-sets.edit', $adSet->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                            <form action="{{ route('marketplace.vendor.meta-ads.ad-sets.destroy', $adSet->id) }}" method="POST" onsubmit="return confirm('Delete?')">
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
            @endif
        </div>
    </div>
@endsection
