@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    @include(MarketplaceHelper::viewPath('vendor-dashboard.meta-ads.partials.breadcrumb'), ['items' => [['label' => 'Dashboard']]])

    @if (! $adAccount || ! $adAccount->is_connected)
        <div class="alert alert-warning">
            <x-core::icon name="ti ti-alert-triangle" class="me-1" />
            {{ __('Your Facebook account is not connected.') }}
            <a href="{{ route('marketplace.vendor.meta-ads.connection') }}">{{ __('Connect now') }}</a>
        </div>
    @endif

    {{-- Metric Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4 col-lg-2">
            <div class="card text-center">
                <div class="card-body py-3">
                    <div class="text-muted small">{{ __('Campaigns') }}</div>
                    <h4 class="mb-0">{{ $totalCampaigns }}</h4>
                    <small class="text-success">{{ $activeCampaigns }} {{ __('active') }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card text-center">
                <div class="card-body py-3">
                    <div class="text-muted small">{{ __('Active Ads') }}</div>
                    <h4 class="mb-0">{{ $activeAds }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card text-center">
                <div class="card-body py-3">
                    <div class="text-muted small">{{ __('Total Spend') }}</div>
                    <h4 class="mb-0">₹{{ number_format($totalSpend, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card text-center">
                <div class="card-body py-3">
                    <div class="text-muted small">{{ __('Impressions') }}</div>
                    <h4 class="mb-0">{{ number_format($totalImpressions) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card text-center">
                <div class="card-body py-3">
                    <div class="text-muted small">{{ __('Clicks') }}</div>
                    <h4 class="mb-0">{{ number_format($totalClicks) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card text-center">
                <div class="card-body py-3">
                    <div class="text-muted small">{{ __('Avg CTR') }}</div>
                    <h4 class="mb-0">{{ $avgCtr }}%</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart --}}
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">{{ __('Last 7 Days Performance') }}</h6>
        </div>
        <div class="card-body">
            <canvas id="performanceChart" height="80"></canvas>
        </div>
    </div>

    {{-- Recent Campaigns --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">{{ __('Recent Campaigns') }}</h6>
            <a href="{{ route('marketplace.vendor.meta-ads.campaigns.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View All') }}</a>
        </div>
        <div class="card-body p-0">
            @if ($recentCampaigns->isEmpty())
                <div class="text-center py-4 text-muted">
                    {{ __('No campaigns yet.') }}
                    <a href="{{ route('marketplace.vendor.meta-ads.campaigns.create') }}">{{ __('Create your first campaign') }}</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('Campaign') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Objective') }}</th>
                                <th>{{ __('Budget') }}</th>
                                <th>{{ __('Impressions') }}</th>
                                <th>{{ __('Clicks') }}</th>
                                <th>{{ __('Spend') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentCampaigns as $campaign)
                                <tr>
                                    <td><a href="{{ route('marketplace.vendor.meta-ads.campaigns.show', $campaign->id) }}">{{ $campaign->name }}</a></td>
                                    <td>@include(MarketplaceHelper::viewPath('vendor-dashboard.meta-ads.partials.status-badge'), ['status' => $campaign->status])</td>
                                    <td>{{ str_replace('OUTCOME_', '', $campaign->objective) }}</td>
                                    <td>₹{{ number_format($campaign->daily_budget ?? $campaign->lifetime_budget ?? 0, 2) }}</td>
                                    <td>{{ number_format($campaign->impressions) }}</td>
                                    <td>{{ number_format($campaign->clicks) }}</td>
                                    <td>₹{{ number_format($campaign->spend, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@stop

@push('footer')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var ctx = document.getElementById('performanceChart').getContext('2d');
            var chartData = @json($chartData);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.map(d => d.date),
                    datasets: [
                        {
                            label: 'Impressions',
                            data: chartData.map(d => d.impressions),
                            borderColor: '#1877F2',
                            backgroundColor: 'rgba(24, 119, 242, 0.1)',
                            fill: true,
                            tension: 0.3,
                        },
                        {
                            label: 'Clicks',
                            data: chartData.map(d => d.clicks),
                            borderColor: '#28a745',
                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                            fill: true,
                            tension: 0.3,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'top' } },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        });
    </script>
@endpush
