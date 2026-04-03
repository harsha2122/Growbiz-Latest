@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    @include(MarketplaceHelper::viewPath('vendor-dashboard.meta-ads.partials.breadcrumb'), ['items' => [
        ['label' => 'Campaigns', 'url' => route('marketplace.vendor.meta-ads.campaigns.index')],
        ['label' => $ad->adSet->campaign->name, 'url' => route('marketplace.vendor.meta-ads.campaigns.show', $ad->adSet->campaign_id)],
        ['label' => $ad->adSet->name, 'url' => route('marketplace.vendor.meta-ads.ad-sets.show', $ad->ad_set_id)],
        ['label' => $ad->name],
    ]])

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">{{ $ad->name }}</h4>
            @include(MarketplaceHelper::viewPath('vendor-dashboard.meta-ads.partials.status-badge'), ['status' => $ad->status])
            <small class="text-muted ms-2">{{ str_replace('_', ' ', $ad->format) }}</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('marketplace.vendor.meta-ads.ads.preview', $ad->id) }}" class="btn btn-outline-info">
                <x-core::icon name="ti ti-eye" /> {{ __('Preview') }}
            </a>
            <a href="{{ route('marketplace.vendor.meta-ads.ads.edit', $ad->id) }}" class="btn btn-outline-secondary">
                <x-core::icon name="ti ti-edit" /> {{ __('Edit') }}
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            {{-- Metrics --}}
            <div class="row g-3 mb-3">
                @foreach ([
                    ['label' => 'Impressions', 'value' => number_format($ad->impressions)],
                    ['label' => 'Clicks', 'value' => number_format($ad->clicks)],
                    ['label' => 'CTR', 'value' => $ad->ctr . '%'],
                    ['label' => 'CPC', 'value' => '₹' . number_format($ad->cpc, 2)],
                    ['label' => 'Spend', 'value' => '₹' . number_format($ad->spend, 2)],
                ] as $metric)
                    <div class="col">
                        <div class="card text-center">
                            <div class="card-body py-2">
                                <small class="text-muted">{{ $metric['label'] }}</small>
                                <h6 class="mb-0">{{ $metric['value'] }}</h6>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Ad Details --}}
            <div class="card">
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr><th style="width: 150px;">{{ __('Primary Text') }}</th><td>{{ $ad->primary_text }}</td></tr>
                        <tr><th>{{ __('Headline') }}</th><td>{{ $ad->headline }}</td></tr>
                        <tr><th>{{ __('Description') }}</th><td>{{ $ad->description ?? '-' }}</td></tr>
                        <tr><th>{{ __('CTA') }}</th><td>{{ str_replace('_', ' ', $ad->cta_button) }}</td></tr>
                        <tr><th>{{ __('Destination URL') }}</th><td><a href="{{ $ad->destination_url }}" target="_blank">{{ $ad->destination_url }}</a></td></tr>
                        @if ($ad->product)
                            <tr><th>{{ __('Product') }}</th><td>{{ $ad->product->name }}</td></tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            @if ($ad->image_url)
                <div class="card">
                    <div class="card-header"><h6 class="mb-0">{{ __('Creative') }}</h6></div>
                    <div class="card-body p-0">
                        <img src="{{ $ad->image_url }}" class="img-fluid" alt="{{ $ad->headline }}" onerror="this.style.display='none'">
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop
