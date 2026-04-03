@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    @include(MarketplaceHelper::viewPath('vendor-dashboard.meta-ads.partials.breadcrumb'), ['items' => [
        ['label' => 'Campaigns', 'url' => route('marketplace.vendor.meta-ads.campaigns.index')],
        ['label' => $adSet->campaign->name, 'url' => route('marketplace.vendor.meta-ads.campaigns.show', $adSet->campaign_id)],
        ['label' => $adSet->name],
    ]])

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">{{ $adSet->name }}</h4>
            <div class="d-flex align-items-center gap-2">
                @include(MarketplaceHelper::viewPath('vendor-dashboard.meta-ads.partials.status-badge'), ['status' => $adSet->status])
                <small class="text-muted">
                    ₹{{ number_format($adSet->daily_budget ?? 0, 2) }}/day &bull;
                    {{ $adSet->targeting_age_min }}-{{ $adSet->targeting_age_max }}, {{ ucfirst($adSet->targeting_genders) }}
                    @if ($adSet->targeting_locations) &bull; {{ implode(', ', $adSet->targeting_locations) }} @endif
                </small>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('marketplace.vendor.meta-ads.ad-sets.ads.create', $adSet->id) }}" class="btn btn-primary">
                <x-core::icon name="ti ti-plus" /> {{ __('Create Ad') }}
            </a>
            <a href="{{ route('marketplace.vendor.meta-ads.ad-sets.edit', $adSet->id) }}" class="btn btn-outline-secondary">
                <x-core::icon name="ti ti-edit" /> {{ __('Edit') }}
            </a>
        </div>
    </div>

    {{-- Targeting Summary --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <small class="text-muted d-block">{{ __('Placements') }}</small>
                    @if ($adSet->placements)
                        @foreach ($adSet->placements as $p)
                            <span class="badge bg-light text-dark me-1 mb-1">{{ str_replace('_', ' ', ucfirst($p)) }}</span>
                        @endforeach
                    @else
                        <span class="text-muted">{{ __('All') }}</span>
                    @endif
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">{{ __('Interests') }}</small>
                    @if ($adSet->targeting_interests)
                        @foreach ($adSet->targeting_interests as $interest)
                            <span class="badge bg-light text-dark me-1 mb-1">{{ $interest }}</span>
                        @endforeach
                    @else
                        <span class="text-muted">{{ __('Broad') }}</span>
                    @endif
                </div>
                <div class="col-md-2">
                    <small class="text-muted d-block">{{ __('Optimization') }}</small>
                    <strong>{{ str_replace('_', ' ', $adSet->optimization_goal) }}</strong>
                </div>
                <div class="col-md-2">
                    <small class="text-muted d-block">{{ __('Impressions') }}</small>
                    <strong>{{ number_format($adSet->impressions) }}</strong>
                </div>
                <div class="col-md-2">
                    <small class="text-muted d-block">{{ __('Clicks') }}</small>
                    <strong>{{ number_format($adSet->clicks) }}</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- Ads Table --}}
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">{{ __('Ads') }} ({{ $adSet->ads->count() }})</h6>
        </div>
        @if ($adSet->ads->isEmpty())
            <div class="card-body text-center py-4">
                <p class="text-muted mb-2">{{ __('No ads in this ad set yet.') }}</p>
                <a href="{{ route('marketplace.vendor.meta-ads.ad-sets.ads.create', $adSet->id) }}" class="btn btn-sm btn-primary">
                    <x-core::icon name="ti ti-plus" /> {{ __('Create Ad') }}
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Ad') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Format') }}</th>
                            <th>{{ __('Headline') }}</th>
                            <th>{{ __('Impressions') }}</th>
                            <th>{{ __('Clicks') }}</th>
                            <th>{{ __('CTR') }}</th>
                            <th>{{ __('Spend') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($adSet->ads as $ad)
                            <tr>
                                <td><a href="{{ route('marketplace.vendor.meta-ads.ads.show', $ad->id) }}">{{ $ad->name }}</a></td>
                                <td>@include(MarketplaceHelper::viewPath('vendor-dashboard.meta-ads.partials.status-badge'), ['status' => $ad->status])</td>
                                <td><small>{{ str_replace('_', ' ', $ad->format) }}</small></td>
                                <td>{{ Str::limit($ad->headline, 30) }}</td>
                                <td>{{ number_format($ad->impressions) }}</td>
                                <td>{{ number_format($ad->clicks) }}</td>
                                <td>{{ $ad->ctr }}%</td>
                                <td>₹{{ number_format($ad->spend, 2) }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('marketplace.vendor.meta-ads.ads.preview', $ad->id) }}" class="btn btn-outline-info" title="Preview">
                                            <x-core::icon name="ti ti-eye" />
                                        </a>
                                        <form action="{{ route('marketplace.vendor.meta-ads.ads.toggle-status', $ad->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-{{ $ad->status === 'ACTIVE' ? 'warning' : 'success' }}">
                                                <x-core::icon name="ti ti-{{ $ad->status === 'ACTIVE' ? 'player-pause' : 'player-play' }}" />
                                            </button>
                                        </form>
                                        <a href="{{ route('marketplace.vendor.meta-ads.ads.edit', $ad->id) }}" class="btn btn-outline-secondary">
                                            <x-core::icon name="ti ti-edit" />
                                        </a>
                                        <form action="{{ route('marketplace.vendor.meta-ads.ads.destroy', $ad->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Delete?') }}')">
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
