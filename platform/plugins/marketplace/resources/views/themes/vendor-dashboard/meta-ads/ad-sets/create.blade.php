@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('marketplace.vendor.meta-ads.campaigns.show', $campaign->id) }}" class="text-muted small">← {{ $campaign->name }}</a>
            <h4 class="mb-0">Create Ad Set</h4>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('marketplace.vendor.meta-ads.campaigns.ad-sets.store', $campaign->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Ad Set Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Daily Budget (₹) <span class="text-danger">*</span></label>
                    <input type="number" name="daily_budget" class="form-control" min="1" step="0.01" value="{{ old('daily_budget') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Bid Cap (₹) <span class="text-muted small fw-normal">— optional</span></label>
                    <input type="number" name="bid_cap" class="form-control" min="1" step="0.01" value="{{ old('bid_cap') }}" placeholder="Leave blank for no cap (recommended)">
                    <div class="form-text">Set a maximum bid per result. Leave blank to let Meta optimize automatically (recommended for most campaigns).</div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Age Min <span class="text-danger">*</span></label>
                        <input type="number" name="targeting_age_min" class="form-control" min="13" max="65" value="{{ old('targeting_age_min', 18) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Age Max <span class="text-danger">*</span></label>
                        <input type="number" name="targeting_age_max" class="form-control" min="13" max="65" value="{{ old('targeting_age_max', 65) }}" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Gender <span class="text-danger">*</span></label>
                    <select name="targeting_genders" class="form-select" required>
                        <option value="all" {{ old('targeting_genders') === 'all' ? 'selected' : '' }}>All</option>
                        <option value="male" {{ old('targeting_genders') === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('targeting_genders') === 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Optimization Goal <span class="text-danger">*</span></label>
                    <select name="optimization_goal" class="form-select" required>
                        <option value="LINK_CLICKS">Link Clicks</option>
                        <option value="LANDING_PAGE_VIEWS">Landing Page Views</option>
                        <option value="IMPRESSIONS">Impressions</option>
                        <option value="REACH">Reach</option>
                        <option value="POST_ENGAGEMENT">Post Engagement</option>
                        <option value="VIDEO_VIEWS">Video Views</option>
                        <option value="OFFSITE_CONVERSIONS">Conversions</option>
                    </select>
                    <div class="form-text">Choose based on your campaign objective: Traffic → Link Clicks / Landing Page Views · Engagement → Post Engagement · Awareness → Reach / Impressions · Sales → Conversions</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Locations (comma-separated)</label>
                    <input type="text" name="targeting_locations" class="form-control" value="{{ old('targeting_locations') }}" placeholder="US, UK, IN">
                </div>
                <div class="mb-3">
                    <label class="form-label">Interests (comma-separated)</label>
                    <input type="text" name="targeting_interests" class="form-control" value="{{ old('targeting_interests') }}" placeholder="Shopping, Fashion">
                </div>
                <div class="mb-3">
                    <label class="form-label">Placements</label>
                    @foreach(['facebook_feed' => 'Facebook Feed', 'instagram_feed' => 'Instagram Feed', 'instagram_stories' => 'Instagram Stories', 'instagram_reels' => 'Reels', 'messenger' => 'Messenger'] as $val => $label)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="placements[]" value="{{ $val }}" id="pl_{{ $val }}"
                                {{ in_array($val, old('placements', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="pl_{{ $val }}">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Create Ad Set</button>
                    <a href="{{ route('marketplace.vendor.meta-ads.campaigns.show', $campaign->id) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
