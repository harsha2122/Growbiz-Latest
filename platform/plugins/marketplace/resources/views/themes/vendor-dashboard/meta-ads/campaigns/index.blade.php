@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    @include(MarketplaceHelper::viewPath('vendor-dashboard.meta-ads.partials.breadcrumb'), ['items' => [['label' => 'Campaigns']]])

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">{{ __('Campaigns') }}</h4>
        <a href="{{ route('marketplace.vendor.meta-ads.campaigns.create') }}" class="btn btn-primary">
            <x-core::icon name="ti ti-plus" /> {{ __('Create Campaign') }}
        </a>
    </div>

    @if ($campaigns->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <x-core::icon name="ti ti-speakerphone" style="font-size: 48px; color: #ccc;" />
                <h5 class="mt-3">{{ __('No campaigns yet') }}</h5>
                <p class="text-muted">{{ __('Create your first campaign to start advertising on Facebook & Instagram.') }}</p>
                <a href="{{ route('marketplace.vendor.meta-ads.campaigns.create') }}" class="btn btn-primary">
                    <x-core::icon name="ti ti-plus" /> {{ __('Create Campaign') }}
                </a>
            </div>
        </div>
    @else
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Campaign') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Objective') }}</th>
                            <th>{{ __('Budget/day') }}</th>
                            <th>{{ __('Ad Sets') }}</th>
                            <th>{{ __('Impressions') }}</th>
                            <th>{{ __('Clicks') }}</th>
                            <th>{{ __('Spend') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($campaigns as $campaign)
                            <tr>
                                <td><a href="{{ route('marketplace.vendor.meta-ads.campaigns.show', $campaign->id) }}">{{ $campaign->name }}</a></td>
                                <td>@include(MarketplaceHelper::viewPath('vendor-dashboard.meta-ads.partials.status-badge'), ['status' => $campaign->status])</td>
                                <td><small>{{ str_replace('OUTCOME_', '', $campaign->objective) }}</small></td>
                                <td>₹{{ number_format($campaign->daily_budget ?? 0, 2) }}</td>
                                <td>{{ $campaign->ad_sets_count }}</td>
                                <td>{{ number_format($campaign->impressions) }}</td>
                                <td>{{ number_format($campaign->clicks) }}</td>
                                <td>₹{{ number_format($campaign->spend, 2) }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <form action="{{ route('marketplace.vendor.meta-ads.campaigns.toggle-status', $campaign->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-{{ $campaign->status === 'ACTIVE' ? 'warning' : 'success' }}" title="{{ $campaign->status === 'ACTIVE' ? 'Pause' : 'Activate' }}">
                                                <x-core::icon name="ti ti-{{ $campaign->status === 'ACTIVE' ? 'player-pause' : 'player-play' }}" />
                                            </button>
                                        </form>
                                        <a href="{{ route('marketplace.vendor.meta-ads.campaigns.edit', $campaign->id) }}" class="btn btn-outline-secondary">
                                            <x-core::icon name="ti ti-edit" />
                                        </a>
                                        <form action="{{ route('marketplace.vendor.meta-ads.campaigns.destroy', $campaign->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Delete this campaign?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger">
                                                <x-core::icon name="ti ti-trash" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        {{ $campaigns->links() }}
    @endif
@stop
