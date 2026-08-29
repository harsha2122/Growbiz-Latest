@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('marketplace.vendor.meta-ads.ad-sets.show', $ad->ad_set_id) }}" class="text-muted small">← {{ $ad->adSet->name }}</a>
            <h4 class="mb-0">{{ $ad->name }}</h4>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('marketplace.vendor.meta-ads.ads.preview', $ad->id) }}" class="btn btn-outline-info">Preview</a>
            <a href="{{ route('marketplace.vendor.meta-ads.ads.edit', $ad->id) }}" class="btn btn-outline-secondary">Edit</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr><td class="text-muted" width="140">Status</td><td>
                            <span class="badge bg-{{ $ad->status === 'ACTIVE' ? 'success' : ($ad->status === 'IN_REVIEW' ? 'warning' : 'secondary') }}">{{ $ad->status }}</span>
                        </td></tr>
                        <tr><td class="text-muted">Format</td><td>{{ $ad->format }}</td></tr>
                        <tr><td class="text-muted">Primary Text</td><td>{{ $ad->primary_text }}</td></tr>
                        <tr><td class="text-muted">Headline</td><td>{{ $ad->headline }}</td></tr>
                        <tr><td class="text-muted">Description</td><td>{{ $ad->description ?? '—' }}</td></tr>
                        <tr><td class="text-muted">CTA Button</td><td>{{ str_replace('_', ' ', $ad->cta_button) }}</td></tr>
                        <tr><td class="text-muted">Destination URL</td><td><a href="{{ $ad->destination_url }}" target="_blank">{{ $ad->destination_url }}</a></td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">Performance</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Impressions</span><strong>{{ number_format((int)($ad->impressions ?? 0)) }}</strong></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Reach</span><strong>{{ number_format((int)($ad->reach ?? 0)) }}</strong></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Clicks</span><strong>{{ number_format((int)($ad->clicks ?? 0)) }}</strong></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">CTR</span><strong>{{ $ad->ctr }}%</strong></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Spend</span><strong>₹{{ $ad->spend }}</strong></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">CPM / CPC</span><strong>₹{{ $ad->cpm }} / ₹{{ $ad->cpc }}</strong></div>
                    <div class="d-flex justify-content-between"><span class="text-muted">Conversions</span><strong>{{ number_format((int)($ad->conversions ?? 0)) }}</strong></div>
                </div>
            </div>

            @if($ad->quality_ranking || $ad->engagement_rate_ranking || $ad->conversion_rate_ranking)
                <div class="card">
                    <div class="card-header">Ad Relevance Diagnostics</div>
                    <div class="card-body">
                        @php
                            $rankColor = fn ($r) => match (true) {
                                str_contains((string) $r, 'ABOVE_AVERAGE') => '#198754',
                                str_contains((string) $r, 'BELOW_AVERAGE') => '#dc3545',
                                $r === 'AVERAGE' => '#fd7e14',
                                default => '#6c757d',
                            };
                        @endphp
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Quality Ranking</span>
                            <span class="badge" style="background-color:{{ $rankColor($ad->quality_ranking) }};color:#fff;">
                                {{ \Botble\Marketplace\Models\MetaAd::rankingLabel($ad->quality_ranking) }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Engagement Rate Ranking</span>
                            <span class="badge" style="background-color:{{ $rankColor($ad->engagement_rate_ranking) }};color:#fff;">
                                {{ \Botble\Marketplace\Models\MetaAd::rankingLabel($ad->engagement_rate_ranking) }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Conversion Rate Ranking</span>
                            <span class="badge" style="background-color:{{ $rankColor($ad->conversion_rate_ranking) }};color:#fff;">
                                {{ \Botble\Marketplace\Models\MetaAd::rankingLabel($ad->conversion_rate_ranking) }}
                            </span>
                        </div>
                        <div class="form-text mt-2">Meta compares your ad against others competing for the same audience.</div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
