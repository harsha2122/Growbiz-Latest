@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">{{ __('Edit Campaign') }}</h4>
        <a href="{{ route('marketplace.vendor.meta-ads.campaigns.index') }}" class="btn btn-secondary btn-sm">
            {{ __('Back') }}
        </a>
    </div>

    <form action="{{ route('marketplace.vendor.meta-ads.campaigns.update', $campaign->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('Campaign Name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $campaign->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('Objective') }} <span class="text-danger">*</span></label>
                        <select name="objective" class="form-select @error('objective') is-invalid @enderror" required>
                            @foreach (['TRAFFIC' => __('Traffic'), 'CONVERSIONS' => __('Conversions'), 'BRAND_AWARENESS' => __('Brand Awareness'), 'REACH' => __('Reach'), 'ENGAGEMENT' => __('Engagement'), 'LEAD_GENERATION' => __('Lead Generation')] as $value => $label)
                                <option value="{{ $value }}" {{ old('objective', $campaign->objective) == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('objective') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('Daily Budget ($)') }}</label>
                        <input type="number" name="daily_budget" class="form-control @error('daily_budget') is-invalid @enderror"
                            value="{{ old('daily_budget', $campaign->daily_budget) }}" min="1" step="0.01">
                        @error('daily_budget') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('Lifetime Budget ($)') }}</label>
                        <input type="number" name="lifetime_budget" class="form-control @error('lifetime_budget') is-invalid @enderror"
                            value="{{ old('lifetime_budget', $campaign->lifetime_budget) }}" min="1" step="0.01">
                        @error('lifetime_budget') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('Start Date') }}</label>
                        <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror"
                            value="{{ old('start_date', $campaign->start_date?->format('Y-m-d')) }}">
                        @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('End Date') }}</label>
                        <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                            value="{{ old('end_date', $campaign->end_date?->format('Y-m-d')) }}">
                        @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('Status') }}</label>
                        <select name="status" class="form-select">
                            <option value="PAUSED" {{ old('status', $campaign->status) == 'PAUSED' ? 'selected' : '' }}>{{ __('Paused') }}</option>
                            <option value="ACTIVE" {{ old('status', $campaign->status) == 'ACTIVE' ? 'selected' : '' }}>{{ __('Active') }}</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top">
                    <h6 class="text-muted">{{ __('Performance') }}</h6>
                    <div class="row g-3">
                        <div class="col-4 col-md-3">
                            <div class="text-center">
                                <div class="fs-4 fw-bold">{{ number_format($campaign->impressions) }}</div>
                                <small class="text-muted">{{ __('Impressions') }}</small>
                            </div>
                        </div>
                        <div class="col-4 col-md-3">
                            <div class="text-center">
                                <div class="fs-4 fw-bold">{{ number_format($campaign->clicks) }}</div>
                                <small class="text-muted">{{ __('Clicks') }}</small>
                            </div>
                        </div>
                        <div class="col-4 col-md-3">
                            <div class="text-center">
                                <div class="fs-4 fw-bold">${{ number_format($campaign->spend, 2) }}</div>
                                <small class="text-muted">{{ __('Spend') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">{{ __('Update Campaign') }}</button>
            </div>
        </div>
    </form>
@endsection
