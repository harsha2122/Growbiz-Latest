@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    @include(MarketplaceHelper::viewPath('vendor-dashboard.meta-ads.partials.breadcrumb'), ['items' => [
        ['label' => 'Campaigns', 'url' => route('marketplace.vendor.meta-ads.campaigns.index')],
        ['label' => 'Edit: ' . $campaign->name],
    ]])

    <h4 class="mb-3">{{ __('Edit Campaign') }}</h4>

    <form action="{{ route('marketplace.vendor.meta-ads.campaigns.update', $campaign->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">{{ __('Campaign Details') }}</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">{{ __('Campaign Name') }} <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $campaign->name) }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('Campaign Objective') }} <span class="text-danger">*</span></label>
                    <div class="row g-2">
                        @foreach ([
                            'OUTCOME_TRAFFIC' => ['icon' => 'ti ti-click', 'label' => 'Traffic', 'desc' => 'Send people to your website'],
                            'OUTCOME_AWARENESS' => ['icon' => 'ti ti-eye', 'label' => 'Awareness', 'desc' => 'Reach people likely to remember your ad'],
                            'OUTCOME_ENGAGEMENT' => ['icon' => 'ti ti-message-circle', 'label' => 'Engagement', 'desc' => 'Get more messages, likes, comments'],
                            'OUTCOME_SALES' => ['icon' => 'ti ti-shopping-cart', 'label' => 'Sales', 'desc' => 'Find people likely to purchase'],
                        ] as $value => $info)
                            <div class="col-md-3 col-6">
                                <label class="card h-100 text-center p-3 border-2 objective-card" style="cursor: pointer;">
                                    <input type="radio" name="objective" value="{{ $value }}" class="d-none objective-radio" {{ old('objective', $campaign->objective) === $value ? 'checked' : '' }}>
                                    <x-core::icon :name="$info['icon']" style="font-size: 28px; color: #1877F2;" />
                                    <strong class="d-block mt-2">{{ $info['label'] }}</strong>
                                    <small class="text-muted">{{ $info['desc'] }}</small>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">{{ __('Budget & Schedule') }}</h6></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('Daily Budget (₹)') }}</label>
                        <input type="number" name="daily_budget" class="form-control" value="{{ old('daily_budget', $campaign->daily_budget) }}" min="1" step="0.01">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('Lifetime Budget (₹)') }}</label>
                        <input type="number" name="lifetime_budget" class="form-control" value="{{ old('lifetime_budget', $campaign->lifetime_budget) }}" min="1" step="0.01">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('Start Date') }}</label>
                        <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $campaign->start_date?->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('End Date') }}</label>
                        <input type="date" name="end_date" class="form-control" value="{{ old('end_date', $campaign->end_date?->format('Y-m-d')) }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><x-core::icon name="ti ti-check" /> {{ __('Update Campaign') }}</button>
            <a href="{{ route('marketplace.vendor.meta-ads.campaigns.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
@stop

@push('footer')
    <style>
        .objective-card { transition: all 0.2s; }
        .objective-card:has(.objective-radio:checked) { border-color: #1877F2 !important; background: #f0f7ff; }
        .objective-card:hover { border-color: #90c0f0 !important; }
    </style>
@endpush
