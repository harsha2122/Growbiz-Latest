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
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    {{-- Meta Sync Status for Ad Set --}}
    @if(!$adSet->meta_adset_id)
        <div class="alert alert-warning d-flex justify-content-between align-items-center">
            <span><i class="ti ti-cloud-off me-1"></i> <strong>Not synced to Meta Ads Manager.</strong> This ad set has not been pushed to Meta yet.</span>
            <form action="{{ route('marketplace.vendor.meta-ads.ad-sets.push-to-meta', $adSet->id) }}" method="POST" class="ms-3 flex-shrink-0">
                @csrf
                <button type="submit" class="btn btn-warning btn-sm">
                    <i class="ti ti-upload me-1"></i> Push to Meta
                </button>
            </form>
        </div>
    @else
        <div class="alert alert-success py-2 d-flex align-items-center gap-2">
            <i class="ti ti-cloud-check fs-5"></i>
            <span>Synced to Meta Ads Manager &nbsp;·&nbsp; Ad Set ID: <code>{{ $adSet->meta_adset_id }}</code></span>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-sm-3"><div class="card card-body">
            <div class="text-muted small">Status</div>
            <span class="badge bg-{{ $adSet->status === 'ACTIVE' ? 'success' : 'warning' }} text-dark">{{ $adSet->status }}</span>
        </div></div>
        <div class="col-sm-3"><div class="card card-body">
            <div class="text-muted small">Daily Budget</div>
            <strong>₹{{ $adSet->daily_budget }}</strong>
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

    @if($adSet->bid_cap)
        <div class="alert alert-info py-2 small">
            <i class="ti ti-coin me-1"></i> Bid Cap: <strong>₹{{ $adSet->bid_cap }}</strong> per result (LOWEST_COST_WITH_BID_CAP)
        </div>
    @endif

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
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Format</th>
                                <th>Status</th>
                                <th>Meta Sync</th>
                                <th>Impressions</th>
                                <th>Clicks</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($adSet->ads as $ad)
                                <tr>
                                    <td><a href="{{ route('marketplace.vendor.meta-ads.ads.show', $ad->id) }}">{{ $ad->name }}</a></td>
                                    <td><small>{{ $ad->format }}</small></td>
                                    <td><span class="badge bg-{{ $ad->status === 'ACTIVE' ? 'success' : ($ad->status === 'IN_REVIEW' ? 'warning text-dark' : 'secondary') }}">{{ $ad->status }}</span></td>
                                    <td>
                                        @if($ad->meta_ad_id)
                                            <span class="badge bg-success"><i class="ti ti-check me-1"></i>Synced</span>
                                        @else
                                            <form action="{{ route('marketplace.vendor.meta-ads.ads.push-to-meta', $ad->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-warning py-0 px-2" style="font-size:.75rem"
                                                    {{ !$adSet->meta_adset_id ? 'disabled title=Push ad set to Meta first' : 'title=Push this ad to Meta' }}>
                                                    <i class="ti ti-upload"></i> Push
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                    <td>{{ number_format((int)($ad->impressions ?? 0)) }}</td>
                                    <td>{{ number_format((int)($ad->clicks ?? 0)) }}</td>
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
