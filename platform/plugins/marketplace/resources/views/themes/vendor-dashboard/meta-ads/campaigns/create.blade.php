@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">{{ __('Create Campaign') }}</h4>
        <a href="{{ route('marketplace.vendor.meta-ads.campaigns.index') }}" class="btn btn-secondary btn-sm">
            {{ __('Back') }}
        </a>
    </div>

    @if (! $adAccount)
        <div class="alert alert-warning">
            {{ __('You need to connect a Facebook Ad Account first.') }}
            <a href="{{ route('marketplace.vendor.meta-ads.accounts.index') }}" class="alert-link">{{ __('Connect now') }}</a>
        </div>
    @endif

    <form action="{{ route('marketplace.vendor.meta-ads.campaigns.store') }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('Campaign Name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('Objective') }} <span class="text-danger">*</span></label>
                        <select name="objective" class="form-select @error('objective') is-invalid @enderror" required>
                            <option value="TRAFFIC" {{ old('objective') == 'TRAFFIC' ? 'selected' : '' }}>{{ __('Traffic') }}</option>
                            <option value="CONVERSIONS" {{ old('objective') == 'CONVERSIONS' ? 'selected' : '' }}>{{ __('Conversions') }}</option>
                            <option value="BRAND_AWARENESS" {{ old('objective') == 'BRAND_AWARENESS' ? 'selected' : '' }}>{{ __('Brand Awareness') }}</option>
                            <option value="REACH" {{ old('objective') == 'REACH' ? 'selected' : '' }}>{{ __('Reach') }}</option>
                            <option value="ENGAGEMENT" {{ old('objective') == 'ENGAGEMENT' ? 'selected' : '' }}>{{ __('Engagement') }}</option>
                            <option value="LEAD_GENERATION" {{ old('objective') == 'LEAD_GENERATION' ? 'selected' : '' }}>{{ __('Lead Generation') }}</option>
                        </select>
                        @error('objective') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('Daily Budget ($)') }}</label>
                        <input type="number" name="daily_budget" class="form-control @error('daily_budget') is-invalid @enderror"
                            value="{{ old('daily_budget') }}" min="1" step="0.01">
                        @error('daily_budget') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('Lifetime Budget ($)') }}</label>
                        <input type="number" name="lifetime_budget" class="form-control @error('lifetime_budget') is-invalid @enderror"
                            value="{{ old('lifetime_budget') }}" min="1" step="0.01">
                        @error('lifetime_budget') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('Start Date') }}</label>
                        <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror"
                            value="{{ old('start_date') }}">
                        @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('End Date') }}</label>
                        <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                            value="{{ old('end_date') }}">
                        @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('Status') }}</label>
                        <select name="status" class="form-select">
                            <option value="PAUSED" {{ old('status', 'PAUSED') == 'PAUSED' ? 'selected' : '' }}>{{ __('Paused') }}</option>
                            <option value="ACTIVE" {{ old('status') == 'ACTIVE' ? 'selected' : '' }}>{{ __('Active') }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary" {{ ! $adAccount ? 'disabled' : '' }}>
                    {{ __('Create Campaign') }}
                </button>
            </div>
        </div>
    </form>
@endsection
