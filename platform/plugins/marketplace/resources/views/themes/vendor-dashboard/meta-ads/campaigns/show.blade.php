@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('marketplace.vendor.meta-ads.campaigns.index') }}" class="text-muted small">← Campaigns</a>
            <h4 class="mb-0">{{ $campaign->name }}</h4>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('marketplace.vendor.meta-ads.campaigns.ad-sets.create', $campaign->id) }}" class="btn btn-primary">
                <i class="ti ti-plus"></i> New Ad Set
            </a>
            <a href="{{ route('marketplace.vendor.meta-ads.campaigns.edit', $campaign->id) }}" class="btn btn-outline-secondary">Edit</a>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    {{-- Meta Sync Status for Campaign --}}
    @if(!$campaign->meta_campaign_id)
        <div class="alert alert-warning d-flex justify-content-between align-items-center">
            <span><i class="ti ti-cloud-off me-1"></i> <strong>Not synced to Meta Ads Manager.</strong> Push this campaign to Meta before creating ad sets.</span>
            <form action="{{ route('marketplace.vendor.meta-ads.campaigns.push-to-meta', $campaign->id) }}" method="POST" class="ms-3 flex-shrink-0">
                @csrf
                <button type="submit" class="btn btn-warning btn-sm"><i class="ti ti-upload me-1"></i> Push to Meta</button>
            </form>
        </div>
    @else
        <div class="alert alert-success py-2 d-flex align-items-center gap-2">
            <i class="ti ti-cloud-check fs-5"></i>
            <span>Synced to Meta Ads Manager &nbsp;·&nbsp; Campaign ID: <code>{{ $campaign->meta_campaign_id }}</code></span>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-sm-4"><div class="card card-body">
            <div class="text-muted small">Objective</div>
            <strong>{{ str_replace('OUTCOME_', '', $campaign->objective) }}</strong>
        </div></div>
        <div class="col-sm-4"><div class="card card-body">
            <div class="text-muted small">Status</div>
            <span class="badge bg-{{ $campaign->status === 'ACTIVE' ? 'success' : 'warning' }} text-dark">{{ $campaign->status }}</span>
        </div></div>
        <div class="col-sm-4"><div class="card card-body">
            <div class="text-muted small">Total Spend</div>
            <strong>₹{{ number_format((float)($campaign->spend ?? 0), 2) }}</strong>
        </div></div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3"><div class="card card-body py-2">
            <div class="text-muted small">Reach / Frequency</div>
            <strong>{{ number_format((int) $campaign->reach) }}</strong> <span class="text-muted small">/ {{ $campaign->frequency }}x</span>
        </div></div>
        <div class="col-sm-6 col-lg-3"><div class="card card-body py-2">
            <div class="text-muted small">CPM / CPC</div>
            <strong>₹{{ $campaign->cpm }}</strong> <span class="text-muted small">/ ₹{{ $campaign->cpc }}</span>
        </div></div>
        <div class="col-sm-6 col-lg-3"><div class="card card-body py-2">
            <div class="text-muted small">CTR</div>
            <strong>{{ $campaign->ctr }}%</strong>
        </div></div>
        <div class="col-sm-6 col-lg-3"><div class="card card-body py-2">
            <div class="text-muted small">Conversions</div>
            <strong>{{ number_format((int) $campaign->conversions) }}</strong>
            <span class="text-muted small">₹{{ number_format((float) $campaign->conversion_value, 2) }}</span>
        </div></div>
    </div>

    @if(collect($chartData['dates'] ?? [])->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Performance Trend</h5></div>
            <div class="card-body"><div id="campaign-trend-chart"></div></div>
        </div>
    @endif

    @if(collect($ageGenderChart['series'] ?? [])->isNotEmpty() || collect($placementChart['series'] ?? [])->isNotEmpty())
        <div class="row g-3 mb-4">
            @if(collect($ageGenderChart['series'] ?? [])->isNotEmpty())
                <div class="col-lg-7">
                    <div class="card h-100">
                        <div class="card-header"><h5 class="mb-0">Audience — Age &amp; Gender (last 30 days)</h5></div>
                        <div class="card-body"><div id="campaign-age-gender-chart"></div></div>
                    </div>
                </div>
            @endif
            @if(collect($placementChart['series'] ?? [])->isNotEmpty())
                <div class="col-lg-5">
                    <div class="card h-100">
                        <div class="card-header"><h5 class="mb-0">Placements (last 30 days)</h5></div>
                        <div class="card-body"><div id="campaign-placement-chart"></div></div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <div class="card">
        <div class="card-header"><h5 class="mb-0">Ad Sets ({{ $campaign->adSets->count() }})</h5></div>
        <div class="card-body p-0">
            @if($campaign->adSets->isEmpty())
                <div class="p-4 text-center text-muted">
                    No ad sets yet.
                    <a href="{{ route('marketplace.vendor.meta-ads.campaigns.ad-sets.create', $campaign->id) }}">Create one →</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Name</th><th>Status</th><th>Budget/day</th><th>Ads</th><th></th></tr></thead>
                        <tbody>
                            @foreach($campaign->adSets as $adSet)
                                <tr>
                                    <td><a href="{{ route('marketplace.vendor.meta-ads.ad-sets.show', $adSet->id) }}">{{ $adSet->name }}</a></td>
                                    <td><span class="badge bg-{{ $adSet->status === 'ACTIVE' ? 'success' : 'warning' }} text-dark">{{ $adSet->status }}</span></td>
                                    <td>₹{{ $adSet->daily_budget }}</td>
                                    <td>{{ $adSet->ads_count }}</td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('marketplace.vendor.meta-ads.ad-sets.edit', $adSet->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                            <form action="{{ route('marketplace.vendor.meta-ads.ad-sets.destroy', $adSet->id) }}" method="POST" onsubmit="return confirm('Delete?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">Del</button>
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
    </div>

    <script>
        (function () {
            if (!window.ApexCharts) return;

            var chartData = @json($chartData ?? ['dates' => [], 'spend' => [], 'clicks' => []]);
            if (chartData.dates.length) {
                new ApexCharts(document.querySelector('#campaign-trend-chart'), {
                    series: [
                        { name: 'Spend (₹)', type: 'area', data: chartData.spend },
                        { name: 'Clicks', type: 'line', data: chartData.clicks },
                    ],
                    chart: { height: 300, type: 'line', toolbar: { show: false } },
                    stroke: { curve: 'smooth', width: [0, 2] },
                    fill: { type: ['gradient', 'solid'], gradient: { opacityFrom: 0.4, opacityTo: 0.05 } },
                    colors: ['#0d6efd', '#fcb800'],
                    dataLabels: { enabled: false },
                    xaxis: { type: 'datetime', categories: chartData.dates },
                    yaxis: [{ title: { text: 'Spend' } }, { opposite: true, title: { text: 'Clicks' } }],
                    tooltip: { x: { format: 'dd MMM yy' } },
                }).render();
            }

            var ageGender = @json($ageGenderChart ?? ['categories' => [], 'series' => []]);
            if (ageGender.series.length) {
                new ApexCharts(document.querySelector('#campaign-age-gender-chart'), {
                    series: ageGender.series,
                    chart: { height: 280, type: 'bar', toolbar: { show: false } },
                    plotOptions: { bar: { horizontal: false, columnWidth: '55%' } },
                    dataLabels: { enabled: false },
                    xaxis: { categories: ageGender.categories, title: { text: 'Impressions' } },
                    colors: ['#0d6efd', '#fc6b00', '#6c757d'],
                }).render();
            }

            var placement = @json($placementChart ?? ['labels' => [], 'series' => []]);
            if (placement.series.length) {
                new ApexCharts(document.querySelector('#campaign-placement-chart'), {
                    series: placement.series,
                    labels: placement.labels,
                    chart: { height: 280, type: 'donut' },
                    legend: { position: 'bottom' },
                }).render();
            }
        })();
    </script>
@endsection
