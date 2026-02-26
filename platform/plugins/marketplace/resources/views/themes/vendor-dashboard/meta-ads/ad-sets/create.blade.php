@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">{{ __('Create Ad Set') }}</h4>
        <a href="{{ route('marketplace.vendor.meta-ads.ad-sets.index') }}" class="btn btn-secondary btn-sm">{{ __('Back') }}</a>
    </div>
    <form action="{{ route('marketplace.vendor.meta-ads.ad-sets.store') }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('Ad Set Name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('Campaign') }} <span class="text-danger">*</span></label>
                        <select name="campaign_id" class="form-select @error('campaign_id') is-invalid @enderror" required>
                            <option value="">{{ __('Select campaign') }}</option>
                            @foreach ($campaigns as $campaign)
                                <option value="{{ $campaign->id }}" {{ old('campaign_id') == $campaign->id ? 'selected' : '' }}>{{ $campaign->name }}</option>
                            @endforeach
                        </select>
                        @error('campaign_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('Daily Budget ($)') }}</label>
                        <input type="number" name="daily_budget" class="form-control" value="{{ old('daily_budget') }}" min="1" step="0.01">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">{{ __('Min Age') }}</label>
                        <input type="number" name="targeting_age_min" class="form-control" value="{{ old('targeting_age_min', 18) }}" min="13" max="65">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">{{ __('Max Age') }}</label>
                        <input type="number" name="targeting_age_max" class="form-control" value="{{ old('targeting_age_max', 65) }}" min="13" max="65">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('Gender') }}</label>
                        <select name="targeting_genders" class="form-select">
                            <option value="all" {{ old('targeting_genders', 'all') == 'all' ? 'selected' : '' }}>{{ __('All') }}</option>
                            <option value="male" {{ old('targeting_genders') == 'male' ? 'selected' : '' }}>{{ __('Male') }}</option>
                            <option value="female" {{ old('targeting_genders') == 'female' ? 'selected' : '' }}>{{ __('Female') }}</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('Optimization Goal') }}</label>
                        <select name="optimization_goal" class="form-select">
                            <option value="LINK_CLICKS">{{ __('Link Clicks') }}</option>
                            <option value="IMPRESSIONS">{{ __('Impressions') }}</option>
                            <option value="REACH">{{ __('Reach') }}</option>
                            <option value="CONVERSIONS">{{ __('Conversions') }}</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('Status') }}</label>
                        <select name="status" class="form-select">
                            <option value="PAUSED">{{ __('Paused') }}</option>
                            <option value="ACTIVE">{{ __('Active') }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">{{ __('Create Ad Set') }}</button>
            </div>
        </div>
    </form>
@endsection
