@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="mb-0">Meta Ads — Dashboard</h4>
        <div class="d-flex align-items-center gap-2">
            <button id="meta-ads-date-range" type="button" class="btn btn-outline-secondary btn-sm">
                <i class="ti ti-calendar"></i>
                <span id="meta-ads-date-range-label">{{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}</span>
            </button>
            <a href="{{ route('marketplace.vendor.meta-ads.campaigns.create') }}" class="btn btn-primary">
                <i class="ti ti-plus"></i> New Campaign
            </a>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(!$adAccount || !$adAccount->is_connected)
        <div class="alert alert-warning">
            Your Facebook account is not connected.
            <a href="{{ route('marketplace.vendor.meta-ads.connection') }}" class="alert-link">Connect now →</a>
        </div>
    @else
        <div class="alert alert-success">
            Connected as <strong>{{ $adAccount->fb_user_name }}</strong>
            @if($adAccount->ad_account_name)
                · Ad Account: <strong>{{ $adAccount->ad_account_name }}</strong>
                @if($adAccount->token_expires_at)
                    · Token expires: <strong>{{ $adAccount->token_expires_at->format('d M Y') }}</strong>
                @endif
            @endif
        </div>
    @endif

    {{-- Payment / Account Status Notice --}}
    @if($hasPaymentMethod === false)
        <div class="alert alert-danger d-flex align-items-start gap-2">
            <i class="ti ti-credit-card-off fs-5 mt-1 flex-shrink-0"></i>
            <div>
                <strong>No payment method found on your ad account.</strong>
                Campaigns cannot be pushed to Meta or go live until you add a payment method.
                <a href="https://adsmanager.facebook.com/billing" target="_blank" class="alert-link fw-semibold ms-1">
                    Add payment method on Meta Ads Manager →
                </a>
                <div class="mt-1 small text-muted">
                    Go to: Ads Manager → Account Overview → Set up billing → Add card/UPI
                </div>
            </div>
        </div>
    @elseif($accountStatus === 3)
        <div class="alert alert-warning d-flex align-items-start gap-2">
            <i class="ti ti-alert-triangle fs-5 mt-1 flex-shrink-0"></i>
            <div>
                <strong>Your ad account has an unpaid balance.</strong>
                Campaigns are paused until the overdue amount is settled.
                <a href="https://adsmanager.facebook.com/billing" target="_blank" class="alert-link ms-1">Settle payment →</a>
            </div>
        </div>
    @elseif($accountStatus !== null && $accountStatus !== 1)
        <div class="alert alert-warning d-flex align-items-start gap-2">
            <i class="ti ti-alert-circle fs-5 mt-1 flex-shrink-0"></i>
            <div>
                <strong>Your Meta ad account is not active</strong> (status: {{ $accountStatus }}).
                <a href="https://adsmanager.facebook.com" target="_blank" class="alert-link ms-1">Check account in Ads Manager →</a>
            </div>
        </div>
    @elseif($hasPaymentMethod === true)
        <div class="alert alert-success d-flex align-items-start gap-2">
            <i class="ti ti-circle-check fs-5 mt-1 flex-shrink-0"></i>
            <div>
                <strong>Ad account is ready.</strong>
                Payment method is configured and your account is active. Campaigns you activate will go live on Meta.
            </div>
        </div>
    @else
        <div class="alert alert-info d-flex align-items-start gap-2">
            <i class="ti ti-info-circle fs-5 mt-1 flex-shrink-0"></i>
            <div>
                <strong>Before activating any campaign:</strong>
                Make sure your Facebook Ad Account has a valid payment method set up. Meta will charge your ad account in <strong>INR</strong> as ads run.
                <a href="https://adsmanager.facebook.com/billing" target="_blank" class="alert-link ms-1">Set up payment on Meta →</a>
            </div>
        </div>
    @endif

    {{-- Ad account financial snapshot (cached from Meta by the hourly sync) --}}
    @if($adAccount && $adAccount->is_connected)
        <div class="row g-3 mb-3">
            <div class="col-sm-4">
                <div class="card card-body py-2">
                    <div class="text-muted small">Amount Spent (Lifetime)</div>
                    <strong>{{ $currencySymbol }}{{ number_format((float) ($adAccount->amount_spent ?? 0), 2) }}</strong>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card card-body py-2">
                    <div class="text-muted small">Account Balance</div>
                    <strong>{{ $adAccount->balance !== null ? $currencySymbol . number_format((float) $adAccount->balance, 2) : '—' }}</strong>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card card-body py-2">
                    <div class="text-muted small">Spend Cap</div>
                    <strong>{{ $adAccount->spend_cap !== null ? $currencySymbol . number_format((float) $adAccount->spend_cap, 2) : 'No cap set' }}</strong>
                </div>
            </div>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted mb-1">Total Campaigns</div>
                    <h3 class="mb-0">{{ $totalCampaigns }}</h3>
                    <div class="small text-success">{{ $activeCampaigns }} active</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted mb-1">Spend ({{ $predefinedRange }})</div>
                    <h3 class="mb-0">{{ $currencySymbol }}{{ number_format($totalSpend, 2) }}</h3>
                    <div class="small text-muted">CPM {{ $currencySymbol }}{{ $avgCpm }} · CPC {{ $currencySymbol }}{{ $avgCpc }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted mb-1">Reach &amp; CTR</div>
                    <h3 class="mb-0">{{ number_format($totalReach) }}</h3>
                    <div class="small text-muted">{{ number_format($totalImpressions) }} impressions · {{ $avgCtr }}% CTR</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted mb-1">Conversions</div>
                    <h3 class="mb-0">{{ number_format($totalConversions) }}</h3>
                    <div class="small text-muted">{{ $currencySymbol }}{{ number_format($totalConversionValue, 2) }} value</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Performance Trend</h5></div>
        <div class="card-body">
            @if(collect($chartData['dates'])->isEmpty())
                <div class="text-center text-muted py-4">No trend data yet — data appears once campaigns start delivering and the hourly sync has run.</div>
            @else
                <div id="meta-ads-trend-chart"></div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5 class="mb-0">Recent Campaigns</h5>
            <a href="{{ route('marketplace.vendor.meta-ads.campaigns.index') }}">View all →</a>
        </div>
        <div class="card-body p-0">
            @if($recentCampaigns->isEmpty())
                <div class="p-4 text-center text-muted">No campaigns yet.
                    <a href="{{ route('marketplace.vendor.meta-ads.campaigns.create') }}">Create one →</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr>
                            <th>Name</th><th>Objective</th><th>Status</th><th>Spend</th><th>Impressions</th><th>Clicks</th>
                        </tr></thead>
                        <tbody>
                            @foreach($recentCampaigns as $campaign)
                                <tr>
                                    <td><a href="{{ route('marketplace.vendor.meta-ads.campaigns.show', $campaign->id) }}">{{ $campaign->name }}</a></td>
                                    <td>{{ str_replace('OUTCOME_', '', $campaign->objective) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $campaign->status === 'ACTIVE' ? 'success' : 'secondary' }}">
                                            {{ $campaign->status }}
                                        </span>
                                    </td>
                                    <td>{{ $currencySymbol }}{{ number_format((float)($campaign->spend ?? 0), 2) }}</td>
                                    <td>{{ number_format((int)($campaign->impressions ?? 0)) }}</td>
                                    <td>{{ number_format((int)($campaign->clicks ?? 0)) }}</td>
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
            var chartData = @json($chartData);

            if (chartData.dates.length && window.ApexCharts) {
                new ApexCharts(document.querySelector('#meta-ads-trend-chart'), {
                    series: [
                        { name: 'Spend ({{ $currencySymbol }})', type: 'area', data: chartData.spend },
                        { name: 'Clicks', type: 'line', data: chartData.clicks },
                    ],
                    chart: { height: 320, type: 'line', toolbar: { show: false } },
                    stroke: { curve: 'smooth', width: [0, 2] },
                    fill: { type: ['gradient', 'solid'], gradient: { opacityFrom: 0.4, opacityTo: 0.05 } },
                    colors: ['#0d6efd', '#fcb800'],
                    dataLabels: { enabled: false },
                    xaxis: { type: 'datetime', categories: chartData.dates },
                    yaxis: [
                        { title: { text: 'Spend' } },
                        { opposite: true, title: { text: 'Clicks' } },
                    ],
                    tooltip: { x: { format: 'dd MMM yy' } },
                }).render();
            }

            if (window.jQuery && jQuery.fn.daterangepicker) {
                jQuery('#meta-ads-date-range').daterangepicker({
                    startDate: moment('{{ $startDate->format('Y-m-d') }}'),
                    endDate: moment('{{ $endDate->format('Y-m-d') }}'),
                    maxDate: moment(),
                    ranges: {
                        'Today': [moment(), moment()],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    },
                }, function (start, end) {
                    var url = new URL(window.location.href);
                    url.searchParams.set('date_from', start.format('YYYY-MM-DD'));
                    url.searchParams.set('date_to', end.format('YYYY-MM-DD'));
                    window.location.href = url.toString();
                });
            }
        })();
    </script>
@endsection
