@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">{{ __('Meta Ads Reports') }}</h4>
        <a href="{{ route('marketplace.vendor.meta-ads.campaigns.index') }}" class="btn btn-secondary btn-sm">
            {{ __('Campaigns') }}
        </a>
    </div>

    {{-- Date filter --}}
    <form method="GET" class="card mb-4">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label mb-1">{{ __('From') }}</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
                </div>
                <div class="col-auto">
                    <label class="form-label mb-1">{{ __('To') }}</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary btn-sm">{{ __('Apply') }}</button>
                </div>
            </div>
        </div>
    </form>

    {{-- Summary cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <div class="fs-3 fw-bold">{{ number_format($totals['impressions']) }}</div>
                    <small class="text-muted">{{ __('Impressions') }}</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <div class="fs-3 fw-bold">{{ number_format($totals['clicks']) }}</div>
                    <small class="text-muted">{{ __('Clicks') }}</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <div class="fs-3 fw-bold">{{ $totals['ctr'] }}%</div>
                    <small class="text-muted">{{ __('CTR') }}</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <div class="fs-3 fw-bold">${{ number_format($totals['spend'], 2) }}</div>
                    <small class="text-muted">{{ __('Total Spend') }}</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Campaign breakdown --}}
    <div class="card mb-4">
        <div class="card-header"><strong>{{ __('Campaign Performance') }}</strong></div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Campaign') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Objective') }}</th>
                        <th class="text-end">{{ __('Impressions') }}</th>
                        <th class="text-end">{{ __('Clicks') }}</th>
                        <th class="text-end">{{ __('Spend') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($campaigns as $campaign)
                        <tr>
                            <td>{{ $campaign->name }}</td>
                            <td>
                                @php $badge = match($campaign->status) { 'ACTIVE' => 'success', 'PAUSED' => 'warning', default => 'secondary' }; @endphp
                                <span class="badge bg-{{ $badge }}">{{ $campaign->status }}</span>
                            </td>
                            <td>{{ $campaign->objective }}</td>
                            <td class="text-end">{{ number_format($campaign->impressions) }}</td>
                            <td class="text-end">{{ number_format($campaign->clicks) }}</td>
                            <td class="text-end">${{ number_format($campaign->spend, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">{{ __('No campaigns found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Top ads --}}
    @if ($topAds->isNotEmpty())
        <div class="card">
            <div class="card-header"><strong>{{ __('Top Performing Ads') }}</strong></div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Ad') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-end">{{ __('Impressions') }}</th>
                            <th class="text-end">{{ __('Clicks') }}</th>
                            <th class="text-end">{{ __('CTR') }}</th>
                            <th class="text-end">{{ __('Spend') }}</th>
                            <th class="text-end">{{ __('CPC') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($topAds as $ad)
                            <tr>
                                <td>{{ $ad->name }}</td>
                                <td>
                                    @php $badge = match($ad->status) { 'ACTIVE' => 'success', 'PAUSED' => 'warning', 'IN_REVIEW' => 'info', default => 'secondary' }; @endphp
                                    <span class="badge bg-{{ $badge }}">{{ $ad->status }}</span>
                                </td>
                                <td class="text-end">{{ number_format($ad->impressions) }}</td>
                                <td class="text-end">{{ number_format($ad->clicks) }}</td>
                                <td class="text-end">{{ number_format($ad->ctr, 2) }}%</td>
                                <td class="text-end">${{ number_format($ad->spend, 2) }}</td>
                                <td class="text-end">${{ number_format($ad->cpc, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
