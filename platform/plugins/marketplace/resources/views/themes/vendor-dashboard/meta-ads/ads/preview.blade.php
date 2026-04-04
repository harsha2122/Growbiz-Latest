@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Ad Preview — {{ $ad->name }}</h4>
        <a href="{{ route('marketplace.vendor.meta-ads.ads.show', $ad->id) }}" class="btn btn-outline-secondary">← Back</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm" style="max-width:420px;margin:auto;border-radius:12px;overflow:hidden">
                <div class="card-header d-flex align-items-center gap-2 bg-white border-0 pt-3 px-3">
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center" style="width:36px;height:36px">
                        <span class="text-white fw-bold small">{{ strtoupper(substr($storeName, 0, 1)) }}</span>
                    </div>
                    <div>
                        <div class="fw-semibold small">{{ $storeName }}</div>
                        <div class="text-muted" style="font-size:11px">Sponsored</div>
                    </div>
                </div>
                <div class="px-3 pb-2">
                    <p class="mb-0 small">{{ $ad->primary_text }}</p>
                </div>
                @if($ad->image_url)
                    <img src="{{ $ad->image_url }}" class="w-100" style="max-height:280px;object-fit:cover" alt="">
                @else
                    <div class="bg-light d-flex align-items-center justify-content-center" style="height:200px">
                        <span class="text-muted">No image</span>
                    </div>
                @endif
                <div class="p-3 bg-light d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted" style="font-size:11px">{{ parse_url($ad->destination_url, PHP_URL_HOST) }}</div>
                        <div class="fw-semibold small">{{ $ad->headline }}</div>
                        @if($ad->description)<div class="text-muted" style="font-size:12px">{{ $ad->description }}</div>@endif
                    </div>
                    <a href="{{ $ad->destination_url }}" target="_blank"
                       class="btn btn-sm btn-primary ms-2" style="white-space:nowrap">
                        {{ str_replace('_', ' ', $ad->cta_button) }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
