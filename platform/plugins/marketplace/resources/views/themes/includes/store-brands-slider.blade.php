@php
    $storeBrands = $store->brands;
@endphp

@if ($storeBrands->isNotEmpty())
    @once
        <style>
            .bb-store-brands {
                margin: 24px 0;
            }

            .bb-store-brands-title {
                font-size: 16px;
                font-weight: 700;
                color: #050505;
                margin-bottom: 12px;
            }

            .bb-store-brands-track {
                display: flex;
                gap: 16px;
                overflow-x: auto;
                padding-bottom: 6px;
            }

            .bb-store-brands-item {
                flex: 0 0 auto;
                width: 96px;
                text-align: center;
            }

            .bb-store-brands-logo {
                width: 72px;
                height: 72px;
                border-radius: 50%;
                border: 1px solid #eceef1;
                object-fit: contain;
                padding: 8px;
                background-color: #fff;
            }

            .bb-store-brands-name {
                display: block;
                margin-top: 6px;
                font-size: 12px;
                color: #65676b;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
        </style>
    @endonce

    <div class="bb-store-brands">
        <div class="bb-store-brands-title">{{ __(':count Brands', ['count' => $storeBrands->count()]) }}</div>
        <div class="bb-store-brands-track">
            @foreach ($storeBrands as $brand)
                <div class="bb-store-brands-item">
                    <img src="{{ RvMedia::getImageUrl($brand->logo) }}" alt="{{ $brand->name }}" class="bb-store-brands-logo">
                    <span class="bb-store-brands-name">{{ $brand->name }}</span>
                </div>
            @endforeach
        </div>
    </div>
@endif
