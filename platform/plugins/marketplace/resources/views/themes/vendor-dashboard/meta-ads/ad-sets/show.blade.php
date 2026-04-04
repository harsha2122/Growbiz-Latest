@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('marketplace.vendor.meta-ads.campaigns.show', $adSet->campaign_id) }}" class="text-muted small">← {{ $adSet->campaign->name }}</a>
            <h4 class="mb-0">{{ $adSet->name }}</h4>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('marketplace.vendor.meta-ads.ad-sets.ads.create', $adSet->id) }}" class="btn btn-primary">
                <i class="ti ti-plus"></i> New Ad
            </a>
            <a href="{{ route('marketplace.vendor.meta-ads.ad-sets.edit', $adSet->id) }}" class="btn btn-outline-secondary">Edit</a>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="row g-3 mb-4">
        <div class="col-sm-3"><div class="card card-body">
            <div class="text-muted small">Status</div>
            <span class="badge bg-{{ $adSet->status === 'ACTIVE' ? 'success' : 'secondary' }}">{{ $adSet->status }}</span>
        </div></div>
        <div class="col-sm-3"><div class="card card-body">
            <div class="text-muted small">Daily Budget</div>
            <strong>${{ $adSet->daily_budget }}</strong>
        </div></div>
        <div class="col-sm-3"><div class="card card-body">
            <div class="text-muted small">Age</div>
            <strong>{{ $adSet->targeting_age_min }}–{{ $adSet->targeting_age_max }}</strong>
        </div></div>
        <div class="col-sm-3"><div class="card card-body">
            <div class="text-muted small">Gender</div>
            <strong>{{ ucfirst($adSet->targeting_genders) }}</strong>
        </div></div>
    </div>

    <div class="card">
        <div class="card-header"><h5 class="mb-0">Ads ({{ $adSet->ads->count() }})</h5></div>
        <div class="card-body p-0">
            @if($adSet->ads->isEmpty())
                <div class="p-4 text-center text-muted">
                    No ads yet. <a href="{{ route('marketplace.vendor.meta-ads.ad-sets.ads.create', $adSet->id) }}">Create one →</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Name</th><th>Format</th><th>Status</th><th>Impressions</th><th>Clicks</th><th></th></tr></thead>
                        <tbody>
                            @foreach($adSet->ads as $ad)
                                <tr>
                                    <td><a href="{{ route('marketplace.vendor.meta-ads.ads.show', $ad->id) }}">{{ $ad->name }}</a></td>
                                    <td><small>{{ $ad->format }}</small></td>
                                    <td><span class="badge bg-{{ $ad->status === 'ACTIVE' ? 'success' : ($ad->status === 'IN_REVIEW' ? 'warning' : 'secondary') }}">{{ $ad->status }}</span></td>
                                    <td>{{ number_format($ad->impressions) }}</td>
                                    <td>{{ number_format($ad->clicks) }}</td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('marketplace.vendor.meta-ads.ads.preview', $ad->id) }}" class="btn btn-sm btn-outline-info">Preview</a>
                                            <a href="{{ route('marketplace.vendor.meta-ads.ads.edit', $ad->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                            <form action="{{ route('marketplace.vendor.meta-ads.ads.destroy', $ad->id) }}" method="POST" onsubmit="return confirm('Delete?')">
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
