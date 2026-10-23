@php
    $storeGalleryImages = $store->galleryImages;
    $galleryInstanceId = 'bb-store-gallery-' . $store->id;
@endphp

@if ($storeGalleryImages->isNotEmpty())
    @once
        <style>
            .bb-store-gallery {
                margin: 28px 0;
            }

            .bb-store-gallery-title {
                font-size: 18px;
                font-weight: 700;
                color: #050505;
                margin-bottom: 14px;
            }

            .bb-store-gallery-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                grid-auto-rows: 130px;
                gap: 12px;
            }

            .bb-store-gallery-item {
                display: block;
                width: 100%;
                height: 100%;
                border-radius: 10px;
                overflow: hidden;
                border: 1px solid #eceef1;
                cursor: zoom-in;
                position: relative;
            }

            /* A touch of masonry rhythm - every 5th tile spans taller. */
            .bb-store-gallery-item:nth-child(5n+1) {
                grid-row: span 2;
            }

            .bb-store-gallery-item img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform .25s ease;
                display: block;
            }

            .bb-store-gallery-item::after {
                content: '';
                position: absolute;
                inset: 0;
                background: rgba(0, 0, 0, 0);
                transition: background .2s ease;
            }

            .bb-store-gallery-item:hover::after {
                background: rgba(0, 0, 0, .12);
            }

            .bb-store-gallery-item:hover img {
                transform: scale(1.08);
            }

            /* Lightbox */
            .bb-gallery-lightbox {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, .9);
                z-index: 99999;
                align-items: center;
                justify-content: center;
            }

            .bb-gallery-lightbox.active {
                display: flex;
            }

            .bb-gallery-lightbox-img {
                max-width: 90vw;
                max-height: 85vh;
                border-radius: 6px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, .5);
            }

            .bb-gallery-lightbox-close,
            .bb-gallery-lightbox-prev,
            .bb-gallery-lightbox-next {
                position: absolute;
                background: rgba(255, 255, 255, .12);
                border: none;
                color: #fff;
                border-radius: 50%;
                width: 44px;
                height: 44px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                font-size: 20px;
                transition: background .2s ease;
            }

            .bb-gallery-lightbox-close:hover,
            .bb-gallery-lightbox-prev:hover,
            .bb-gallery-lightbox-next:hover {
                background: rgba(255, 255, 255, .25);
            }

            .bb-gallery-lightbox-close {
                top: 20px;
                right: 20px;
            }

            .bb-gallery-lightbox-prev {
                left: 20px;
                top: 50%;
                transform: translateY(-50%);
            }

            .bb-gallery-lightbox-next {
                right: 20px;
                top: 50%;
                transform: translateY(-50%);
            }

            @media (max-width: 576px) {
                .bb-store-gallery-grid {
                    grid-auto-rows: 110px;
                }

                .bb-gallery-lightbox-prev,
                .bb-gallery-lightbox-next {
                    width: 36px;
                    height: 36px;
                    font-size: 16px;
                }
            }
        </style>
    @endonce

    <div class="bb-store-gallery">
        <div class="bb-store-gallery-title">{{ __('Gallery') }}</div>
        <div class="bb-store-gallery-grid" id="{{ $galleryInstanceId }}" data-bb-gallery>
            @foreach ($storeGalleryImages as $galleryImage)
                <a href="javascript:void(0)" data-index="{{ $loop->index }}" data-full="{{ RvMedia::getImageUrl($galleryImage->image) }}" class="bb-store-gallery-item">
                    <img src="{{ RvMedia::getImageUrl($galleryImage->image) }}" alt="{{ $store->name }}" loading="lazy">
                </a>
            @endforeach
        </div>
    </div>

    @once
        <div class="bb-gallery-lightbox" id="bb-gallery-lightbox">
            <button type="button" class="bb-gallery-lightbox-close" data-bb-gallery-close>&times;</button>
            <button type="button" class="bb-gallery-lightbox-prev" data-bb-gallery-prev>&#8249;</button>
            <img src="" alt="" class="bb-gallery-lightbox-img" id="bb-gallery-lightbox-img">
            <button type="button" class="bb-gallery-lightbox-next" data-bb-gallery-next>&#8250;</button>
        </div>

        <script>
            // Must stay inline here, not a Blade footer push - the public storefront
            // layout doesn't render Laravel's footer stack.
            (function () {
                var lightbox = document.getElementById('bb-gallery-lightbox');
                var lightboxImg = document.getElementById('bb-gallery-lightbox-img');
                var urls = [];
                var currentIndex = 0;

                function openAt(index) {
                    if (!urls.length) return;
                    currentIndex = (index + urls.length) % urls.length;
                    lightboxImg.src = urls[currentIndex];
                    lightbox.classList.add('active');
                    document.body.style.overflow = 'hidden';
                }

                function close() {
                    lightbox.classList.remove('active');
                    document.body.style.overflow = '';
                }

                document.addEventListener('click', function (e) {
                    var item = e.target.closest('[data-bb-gallery] .bb-store-gallery-item');
                    if (item) {
                        var gallery = item.closest('[data-bb-gallery]');
                        urls = Array.prototype.map.call(
                            gallery.querySelectorAll('.bb-store-gallery-item'),
                            function (el) { return el.getAttribute('data-full'); }
                        );
                        openAt(parseInt(item.getAttribute('data-index'), 10) || 0);
                        return;
                    }

                    if (e.target.closest('[data-bb-gallery-close]') || e.target === lightbox) {
                        close();
                    } else if (e.target.closest('[data-bb-gallery-prev]')) {
                        openAt(currentIndex - 1);
                    } else if (e.target.closest('[data-bb-gallery-next]')) {
                        openAt(currentIndex + 1);
                    }
                });

                document.addEventListener('keydown', function (e) {
                    if (!lightbox.classList.contains('active')) return;
                    if (e.key === 'Escape') close();
                    if (e.key === 'ArrowLeft') openAt(currentIndex - 1);
                    if (e.key === 'ArrowRight') openAt(currentIndex + 1);
                });
            })();
        </script>
    @endonce
@endif
