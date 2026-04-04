@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('marketplace.vendor.meta-ads.campaigns.show', $adSet->campaign_id) }}" class="text-muted small">← Campaign</a>
            <h4 class="mb-0">Edit Ad Set</h4>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('marketplace.vendor.meta-ads.ad-sets.update', $adSet->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Ad Set Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $adSet->name) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Daily Budget ($) <span class="text-danger">*</span></label>
                    <input type="number" name="daily_budget" class="form-control" min="1" step="0.01"
                           value="{{ old('daily_budget', $adSet->daily_budget) }}" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Age Min</label>
                        <input type="number" name="targeting_age_min" class="form-control" min="13" max="65"
                               value="{{ old('targeting_age_min', $adSet->targeting_age_min) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Age Max</label>
                        <input type="number" name="targeting_age_max" class="form-control" min="13" max="65"
                               value="{{ old('targeting_age_max', $adSet->targeting_age_max) }}" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Gender</label>
                    <select name="targeting_genders" class="form-select" required>
                        @foreach(['all' => 'All', 'male' => 'Male', 'female' => 'Female'] as $val => $label)
                            <option value="{{ $val }}" {{ old('targeting_genders', $adSet->targeting_genders) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Optimization Goal</label>
                    <select name="optimization_goal" class="form-select" required>
                        @foreach(['LINK_CLICKS' => 'Link Clicks', 'IMPRESSIONS' => 'Impressions', 'REACH' => 'Reach'] as $val => $label)
                            <option value="{{ $val }}" {{ old('optimization_goal', $adSet->optimization_goal) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Locations (comma-separated)</label>
                    <input type="text" name="targeting_locations" class="form-control"
                           value="{{ old('targeting_locations', is_array($adSet->targeting_locations) ? implode(', ', $adSet->targeting_locations) : '') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Interests (comma-separated)</label>
                    <input type="text" name="targeting_interests" class="form-control"
                           value="{{ old('targeting_interests', is_array($adSet->targeting_interests) ? implode(', ', $adSet->targeting_interests) : '') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Placements</label>
                    @foreach(['facebook_feed' => 'Facebook Feed', 'instagram_feed' => 'Instagram Feed', 'instagram_stories' => 'Instagram Stories', 'instagram_reels' => 'Reels', 'messenger' => 'Messenger'] as $val => $label)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="placements[]" value="{{ $val }}" id="pl_{{ $val }}"
                                {{ in_array($val, old('placements', $adSet->placements ?? [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="pl_{{ $val }}">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="{{ route('marketplace.vendor.meta-ads.campaigns.show', $adSet->campaign_id) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
