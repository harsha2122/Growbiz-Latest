@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    @include(MarketplaceHelper::viewPath('vendor-dashboard.meta-ads.partials.breadcrumb'), ['items' => [
        ['label' => 'Campaigns', 'url' => route('marketplace.vendor.meta-ads.campaigns.index')],
        ['label' => $adSet->campaign->name, 'url' => route('marketplace.vendor.meta-ads.campaigns.show', $adSet->campaign_id)],
        ['label' => 'Edit: ' . $adSet->name],
    ]])

    <h4 class="mb-3">{{ __('Edit Ad Set') }}</h4>

    <form action="{{ route('marketplace.vendor.meta-ads.ad-sets.update', $adSet->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">{{ __('Ad Set Details') }}</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">{{ __('Ad Set Name') }} <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $adSet->name) }}" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('Daily Budget (₹)') }}</label>
                        <input type="number" name="daily_budget" class="form-control" value="{{ old('daily_budget', $adSet->daily_budget) }}" min="1" step="0.01" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('Optimization Goal') }}</label>
                        <select name="optimization_goal" class="form-select">
                            @foreach (['LINK_CLICKS' => 'Link Clicks', 'IMPRESSIONS' => 'Impressions', 'CONVERSIONS' => 'Conversions', 'REACH' => 'Reach'] as $val => $label)
                                <option value="{{ $val }}" {{ old('optimization_goal', $adSet->optimization_goal) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0"><x-core::icon name="ti ti-target" class="me-1" /> {{ __('Audience Targeting') }}</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">{{ __('Locations') }}</label>
                    <input type="text" name="targeting_locations" class="form-control" value="{{ old('targeting_locations', is_array($adSet->targeting_locations) ? implode(', ', $adSet->targeting_locations) : '') }}">
                    <small class="text-muted">{{ __('Comma-separated cities or states') }}</small>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('Age Range') }}</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="number" name="targeting_age_min" class="form-control" value="{{ old('targeting_age_min', $adSet->targeting_age_min) }}" min="13" max="65">
                            <span>to</span>
                            <input type="number" name="targeting_age_max" class="form-control" value="{{ old('targeting_age_max', $adSet->targeting_age_max) }}" min="13" max="65">
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('Gender') }}</label>
                        <div class="d-flex gap-3 mt-2">
                            @foreach (['all' => 'All', 'male' => 'Male', 'female' => 'Female'] as $val => $label)
                                <div class="form-check">
                                    <input type="radio" name="targeting_genders" value="{{ $val }}" class="form-check-input" id="gender_{{ $val }}" {{ old('targeting_genders', $adSet->targeting_genders) === $val ? 'checked' : '' }}>
                                    <label class="form-check-label" for="gender_{{ $val }}">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('Interests') }}</label>
                        <input type="text" name="targeting_interests" class="form-control" value="{{ old('targeting_interests', is_array($adSet->targeting_interests) ? implode(', ', $adSet->targeting_interests) : '') }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0"><x-core::icon name="ti ti-device-mobile" class="me-1" /> {{ __('Placements') }}</h6></div>
            <div class="card-body">
                <div class="row">
                    @php $currentPlacements = old('placements', $adSet->placements ?? []); @endphp
                    @foreach ([
                        'facebook_feed' => ['icon' => 'ti ti-brand-facebook', 'label' => 'Facebook Feed'],
                        'instagram_feed' => ['icon' => 'ti ti-brand-instagram', 'label' => 'Instagram Feed'],
                        'instagram_stories' => ['icon' => 'ti ti-brand-instagram', 'label' => 'Instagram Stories'],
                        'instagram_reels' => ['icon' => 'ti ti-brand-instagram', 'label' => 'Instagram Reels'],
                        'audience_network' => ['icon' => 'ti ti-world', 'label' => 'Audience Network'],
                        'messenger' => ['icon' => 'ti ti-brand-messenger', 'label' => 'Messenger'],
                    ] as $val => $info)
                        <div class="col-md-4 col-6 mb-2">
                            <div class="form-check">
                                <input type="checkbox" name="placements[]" value="{{ $val }}" class="form-check-input" id="placement_{{ $val }}"
                                    {{ in_array($val, $currentPlacements) ? 'checked' : '' }}>
                                <label class="form-check-label" for="placement_{{ $val }}">
                                    <x-core::icon :name="$info['icon']" class="me-1" /> {{ $info['label'] }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><x-core::icon name="ti ti-check" /> {{ __('Update Ad Set') }}</button>
            <a href="{{ route('marketplace.vendor.meta-ads.campaigns.show', $adSet->campaign_id) }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
@stop
