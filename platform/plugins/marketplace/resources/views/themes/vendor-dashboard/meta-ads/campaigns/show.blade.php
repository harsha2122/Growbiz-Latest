@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    @include(MarketplaceHelper::viewPath('vendor-dashboard.meta-ads.partials.breadcrumb'), ['items' => [
        ['label' => 'Campaigns', 'url' => route('marketplace.vendor.meta-ads.campaigns.index')],
        ['label' => $campaign->name],
    ]])

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">{{ $campaign->name }}</h4>
            <div class="d-flex align-items-center gap-2">
                @include(MarketplaceHelper::viewPath('vendor-dashboard.meta-ads.partials.status-badge'), ['status' => $campaign->status])
                <small class="text-muted">{{ str_replace('OUTCOME_', '', $campaign->objective) }} &bull; ₹{{ number_format($campaign->daily_budget ?? $campaign->lifetime_budget ?? 0, 2) }}/day</small>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('marketplace.vendor.meta-ads.campaigns.ad-sets.create', $campaign->id) }}" class="btn btn-primary">
                <x-core::icon name="ti ti-plus" /> {{ __('Create Ad Set') }}
            </a>
            <a href="{{ route('marketplace.vendor.meta-ads.campaigns.edit', $campaign->id) }}" class="btn btn-outline-secondary">
                <x-core::icon name="ti ti-edit" /> {{ __('Edit') }}
            </a>
        </div>
    </div>

    {{-- Campaign Metrics --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body py-3">
                    <div class="text-muted small">{{ __('Impressions') }}</div>
                    <h5 class="mb-0">{{ number_format($campaign->impressions) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body py-3">
                    <div class="text-muted small">{{ __('Clicks') }}</div>
                    <h5 class="mb-0">{{ number_format($campaign->clicks) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body py-3">
                    <div class="text-muted small">{{ __('CTR') }}</div>
                    <h5 class="mb-0">{{ $campaign->ctr }}%</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body py-3">
                    <div class="text-muted small">{{ __('Spend') }}</div>
                    <h5 class="mb-0">₹{{ number_format($campaign->spend, 2) }}</h5>
                </div>
            </div>
        </div>
    </div>

    {{-- Ad Sets Table --}}
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">{{ __('Ad Sets') }} ({{ $campaign->adSets->count() }})</h6>
        </div>
        @if ($campaign->adSets->isEmpty())
            <div class="card-body text-center py-4">
                <p class="text-muted mb-2">{{ __('No ad sets in this campaign yet.') }}</p>
                <a href="{{ route('marketplace.vendor.meta-ads.campaigns.ad-sets.create', $campaign->id) }}" class="btn btn-sm btn-primary">
                    <x-core::icon name="ti ti-plus" /> {{ __('Create Ad Set') }}
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Ad Set') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Budget/day') }}</th>
                            <th>{{ __('Targeting') }}</th>
                            <th>{{ __('Ads') }}</th>
                            <th>{{ __('Impressions') }}</th>
                            <th>{{ __('Clicks') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($campaign->adSets as $adSet)
                            <tr>
                                <td><a href="{{ route('marketplace.vendor.meta-ads.ad-sets.show', $adSet->id) }}">{{ $adSet->name }}</a></td>
                                <td>@include(MarketplaceHelper::viewPath('vendor-dashboard.meta-ads.partials.status-badge'), ['status' => $adSet->status])</td>
                                <td>₹{{ number_format($adSet->daily_budget ?? 0, 2) }}</td>
                                <td>
                                    <small>
                                        {{ $adSet->targeting_age_min }}-{{ $adSet->targeting_age_max }}, {{ ucfirst($adSet->targeting_genders) }}
                                        @if ($adSet->targeting_locations)
                                            <br>{{ implode(', ', array_slice($adSet->targeting_locations, 0, 2)) }}{{ count($adSet->targeting_locations) > 2 ? '...' : '' }}
                                        @endif
                                    </small>
                                </td>
                                <td>{{ $adSet->ads_count }}</td>
                                <td>{{ number_format($adSet->impressions) }}</td>
                                <td>{{ number_format($adSet->clicks) }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <form action="{{ route('marketplace.vendor.meta-ads.ad-sets.toggle-status', $adSet->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-{{ $adSet->status === 'ACTIVE' ? 'warning' : 'success' }}">
                                                <x-core::icon name="ti ti-{{ $adSet->status === 'ACTIVE' ? 'player-pause' : 'player-play' }}" />
                                            </button>
                                        </form>
                                        <a href="{{ route('marketplace.vendor.meta-ads.ad-sets.edit', $adSet->id) }}" class="btn btn-outline-secondary">
                                            <x-core::icon name="ti ti-edit" />
                                        </a>
                                        <form action="{{ route('marketplace.vendor.meta-ads.ad-sets.destroy', $adSet->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Delete this ad set?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger"><x-core::icon name="ti ti-trash" /></button>
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
@stop
