@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    @include(MarketplaceHelper::viewPath('vendor-dashboard.meta-ads.partials.breadcrumb'), ['items' => [
        ['label' => 'Campaigns', 'url' => route('marketplace.vendor.meta-ads.campaigns.index')],
        ['label' => $campaign->name, 'url' => route('marketplace.vendor.meta-ads.campaigns.show', $campaign->id)],
        ['label' => 'Create Ad Set'],
    ]])

    <h4 class="mb-3">{{ __('Create Ad Set') }}</h4>

    <form action="{{ route('marketplace.vendor.meta-ads.campaigns.ad-sets.store', $campaign->id) }}" method="POST">
        @csrf

        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">{{ __('Ad Set Details') }}</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">{{ __('Ad Set Name') }} <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required placeholder="e.g. Women 25-45 India">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('Daily Budget (₹)') }} <span class="text-danger">*</span></label>
                        <input type="number" name="daily_budget" class="form-control @error('daily_budget') is-invalid @enderror" value="{{ old('daily_budget', 500) }}" min="1" step="0.01" required>
                        @error('daily_budget') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('Optimization Goal') }} <span class="text-danger">*</span></label>
                        <select name="optimization_goal" class="form-select">
                            <option value="LINK_CLICKS" {{ old('optimization_goal') === 'LINK_CLICKS' ? 'selected' : '' }}>{{ __('Link Clicks') }}</option>
                            <option value="IMPRESSIONS" {{ old('optimization_goal') === 'IMPRESSIONS' ? 'selected' : '' }}>{{ __('Impressions') }}</option>
                            <option value="CONVERSIONS" {{ old('optimization_goal') === 'CONVERSIONS' ? 'selected' : '' }}>{{ __('Conversions') }}</option>
                            <option value="REACH" {{ old('optimization_goal') === 'REACH' ? 'selected' : '' }}>{{ __('Reach') }}</option>
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
                    <input type="text" name="targeting_locations" class="form-control" value="{{ old('targeting_locations') }}" placeholder="e.g. Mumbai, Delhi, Bangalore, Pune">
                    <small class="text-muted">{{ __('Comma-separated cities or states') }}</small>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('Age Range') }}</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="number" name="targeting_age_min" class="form-control" value="{{ old('targeting_age_min', 18) }}" min="13" max="65">
                            <span>{{ __('to') }}</span>
                            <input type="number" name="targeting_age_max" class="form-control" value="{{ old('targeting_age_max', 65) }}" min="13" max="65">
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('Gender') }}</label>
                        <div class="d-flex gap-3 mt-2">
                            @foreach (['all' => 'All', 'male' => 'Male', 'female' => 'Female'] as $val => $label)
                                <div class="form-check">
                                    <input type="radio" name="targeting_genders" value="{{ $val }}" class="form-check-input" id="gender_{{ $val }}" {{ old('targeting_genders', 'all') === $val ? 'checked' : '' }}>
                                    <label class="form-check-label" for="gender_{{ $val }}">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('Interests') }}</label>
                        <input type="text" name="targeting_interests" class="form-control" value="{{ old('targeting_interests') }}" placeholder="e.g. Online Shopping, Fashion, Electronics">
                        <small class="text-muted">{{ __('Comma-separated') }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0"><x-core::icon name="ti ti-device-mobile" class="me-1" /> {{ __('Placements') }}</h6></div>
            <div class="card-body">
                <div class="row">
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
                                    {{ in_array($val, old('placements', ['facebook_feed', 'instagram_feed'])) ? 'checked' : '' }}>
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
            <button type="submit" class="btn btn-primary"><x-core::icon name="ti ti-check" /> {{ __('Create Ad Set') }}</button>
            <a href="{{ route('marketplace.vendor.meta-ads.campaigns.show', $campaign->id) }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
@stop
