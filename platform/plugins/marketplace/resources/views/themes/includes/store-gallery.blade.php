@php
    $storeGalleryImages = $store->galleryImages;
@endphp

@if ($storeGalleryImages->isNotEmpty())
    @once
        <style>
            .bb-store-gallery {
                margin: 24px 0;
            }

            .bb-store-gallery-title {
                font-size: 16px;
                font-weight: 700;
                color: #050505;
                margin-bottom: 12px;
            }

            .bb-store-gallery-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
                gap: 12px;
            }

            .bb-store-gallery-item {
                display: block;
                aspect-ratio: 1 / 1;
                border-radius: 8px;
                overflow: hidden;
                border: 1px solid #eceef1;
            }

            .bb-store-gallery-item img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform .2s ease;
            }

            .bb-store-gallery-item:hover img {
                transform: scale(1.05);
            }
        </style>
    @endonce

    <div class="bb-store-gallery">
        <div class="bb-store-gallery-title">{{ __('Gallery') }}</div>
        <div class="bb-store-gallery-grid">
            @foreach ($storeGalleryImages as $galleryImage)
                <a href="{{ RvMedia::getImageUrl($galleryImage->image) }}" target="_blank" rel="noopener" class="bb-store-gallery-item">
                    <img src="{{ RvMedia::getImageUrl($galleryImage->image) }}" alt="{{ $store->name }}">
                </a>
            @endforeach
        </div>
    </div>
@endif
