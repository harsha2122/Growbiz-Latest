@php
    $storeGalleryImages = $store->galleryImages;
    $galleryInstanceId = 'bb-store-gallery-' . $store->id;
@endphp

@if ($storeGalleryImages->isNotEmpty())
    @once
        <style>
            .bb-store-gallery { margin: 28px 0; }
            .bb-store-gallery-heading { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
            .bb-store-gallery-title { font-size: 18px; font-weight: 700; color: #182026; margin: 0; }
            .bb-store-gallery-count { font-size: 12px; color: #626970; padding: 3px 9px; border-radius: 20px; background: #f3f5f7; }
            .bb-store-gallery-grid { display: grid; grid-template-columns: repeat(var(--bb-gallery-columns), minmax(0, 1fr)); gap: 14px; }
            .bb-store-gallery-grid--single { max-width: 640px; }
            .bb-store-gallery-item { display: block; aspect-ratio: 4 / 3; border-radius: 14px; overflow: hidden; border: 1px solid #e9ecef; background: #f4f6f8; cursor: zoom-in; position: relative; }
            .bb-store-gallery-item img { width: 100%; height: 100%; max-height: none; object-fit: cover; display: block; transition: transform .25s ease; }
            .bb-store-gallery-item:hover img { transform: scale(1.035); }
            .bb-store-gallery-item:focus-visible { outline: 3px solid var(--primary-color, #0989ff); outline-offset: 3px; }
            .bb-store-gallery-zoom { position: absolute; inset-inline-end: 12px; bottom: 12px; display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 50%; background: rgba(0, 0, 0, .55); color: #fff; pointer-events: none; }
            .bb-store-gallery-zoom .icon { width: 18px; height: 18px; }
            .bb-gallery-lightbox { position: fixed; inset: 0; width: 100%; max-width: none; height: 100dvh; max-height: none; margin: 0; padding: 0; border: 0; background: #101418; color: #fff; }
            .bb-gallery-lightbox::backdrop { background: rgba(0, 0, 0, .9); }
            .bb-gallery-lightbox[open] { display: flex; align-items: center; justify-content: center; }
            .bb-gallery-lightbox-img { display: block; width: calc(100% - 140px); height: calc(100% - 150px); object-fit: contain; }
            .bb-gallery-lightbox-counter { position: absolute; top: 28px; inset-inline-start: 24px; font-size: 14px; color: #e2e7ec; }
            .bb-gallery-lightbox button { position: absolute; display: flex; align-items: center; justify-content: center; width: 44px; height: 44px; padding: 0; border: 1px solid rgba(255, 255, 255, .2); border-radius: 50%; background: rgba(255, 255, 255, .1); color: #fff; cursor: pointer; }
            .bb-gallery-lightbox button:hover { background: rgba(255, 255, 255, .25); }
            .bb-gallery-lightbox button:focus-visible { outline: 2px solid #fff; outline-offset: 3px; }
            .bb-gallery-lightbox button[hidden] { display: none; }
            .bb-gallery-lightbox-close { top: 20px; inset-inline-end: 24px; }
            .bb-gallery-lightbox-prev { left: 20px; top: calc(50% - 22px); }
            .bb-gallery-lightbox-next { right: 20px; top: calc(50% - 22px); }
            @media (max-width: 991px) {
                .bb-store-gallery-grid { grid-template-columns: repeat(var(--bb-gallery-tablet-columns), minmax(0, 1fr)); }
            }
            @media (max-width: 575px) {
                .bb-store-gallery-grid { grid-template-columns: repeat(var(--bb-gallery-mobile-columns), minmax(0, 1fr)); gap: 10px; }
                .bb-store-gallery-item { border-radius: 10px; }
                .bb-gallery-lightbox-img { width: calc(100% - 24px); height: calc(100% - 180px); }
                .bb-gallery-lightbox-prev, .bb-gallery-lightbox-next { top: auto; bottom: 22px; }
                .bb-gallery-lightbox-prev { left: calc(50% - 56px); }
                .bb-gallery-lightbox-next { right: calc(50% - 56px); }
            }
            @media (prefers-reduced-motion: reduce) {
                .bb-store-gallery-item img { transition: none; }
            }
        </style>
    @endonce

    <section class="bb-store-gallery" aria-labelledby="{{ $galleryInstanceId }}-title">
        <div class="bb-store-gallery-heading">
            <h3 class="bb-store-gallery-title" id="{{ $galleryInstanceId }}-title">{{ __('Gallery') }}</h3>
            <span class="bb-store-gallery-count">{{ $storeGalleryImages->count() }}</span>
        </div>
        <div
            @class(['bb-store-gallery-grid', 'bb-store-gallery-grid--single' => $storeGalleryImages->count() === 1])
            id="{{ $galleryInstanceId }}"
            data-bb-gallery
            style="--bb-gallery-columns: {{ min(4, $storeGalleryImages->count()) }}; --bb-gallery-tablet-columns: {{ min(3, $storeGalleryImages->count()) }}; --bb-gallery-mobile-columns: {{ min(2, $storeGalleryImages->count()) }}"
        >
            @foreach ($storeGalleryImages as $galleryImage)
                <a href="{{ RvMedia::getImageUrl($galleryImage->image) }}" data-index="{{ $loop->index }}" class="bb-store-gallery-item" aria-label="{{ __('View gallery image :number', ['number' => $loop->iteration]) }}" aria-haspopup="dialog">
                    <img src="{{ RvMedia::getImageUrl($galleryImage->image) }}" alt="{{ __(':store — gallery image :number', ['store' => $store->name, 'number' => $loop->iteration]) }}" width="640" height="480" loading="lazy" decoding="async">
                    <span class="bb-store-gallery-zoom" aria-hidden="true"><x-core::icon name="ti ti-arrows-maximize" /></span>
                </a>
            @endforeach
        </div>
    </section>

    @once
        <dialog class="bb-gallery-lightbox" id="bb-gallery-lightbox" aria-label="{{ __('Store gallery') }}">
            <span class="bb-gallery-lightbox-counter" aria-live="polite"></span>
            <button type="button" class="bb-gallery-lightbox-close" aria-label="{{ __('Close gallery') }}" autofocus><x-core::icon name="ti ti-x" /></button>
            <button type="button" class="bb-gallery-lightbox-prev" aria-label="{{ __('Previous image') }}"><x-core::icon name="ti ti-chevron-left" /></button>
            <img alt="" class="bb-gallery-lightbox-img" id="bb-gallery-lightbox-img">
            <button type="button" class="bb-gallery-lightbox-next" aria-label="{{ __('Next image') }}"><x-core::icon name="ti ti-chevron-right" /></button>
        </dialog>

        <script>
            (function () {
                var lightbox = document.getElementById('bb-gallery-lightbox');
                var image = lightbox.querySelector('img');
                var counter = lightbox.querySelector('.bb-gallery-lightbox-counter');
                var previous = lightbox.querySelector('.bb-gallery-lightbox-prev');
                var next = lightbox.querySelector('.bb-gallery-lightbox-next');
                var items = [];
                var index = 0;
                var trigger;
                var previousOverflow;
                var counterLabel = {{ Illuminate\Support\Js::from(__('Image :current of :total')) }};
                // A native dialog stays above sticky headers and traps keyboard focus.
                document.body.appendChild(lightbox);

                function showAt(value) {
                    index = (value + items.length) % items.length;
                    image.src = items[index].href;
                    image.alt = items[index].querySelector('img').alt;
                    counter.textContent = counterLabel.replace(':current', index + 1).replace(':total', items.length);
                    previous.hidden = next.hidden = items.length < 2;
                }

                document.addEventListener('click', function (event) {
                    var item = event.target.closest('[data-bb-gallery] .bb-store-gallery-item');
                    if (!item || typeof lightbox.showModal !== 'function') return;
                    event.preventDefault();
                    items = Array.from(item.closest('[data-bb-gallery]').querySelectorAll('.bb-store-gallery-item'));
                    trigger = item;
                    showAt(items.indexOf(item));
                    previousOverflow = document.body.style.overflow;
                    lightbox.showModal();
                    document.body.style.overflow = 'hidden';
                });

                lightbox.querySelector('.bb-gallery-lightbox-close').addEventListener('click', function () { lightbox.close(); });
                previous.addEventListener('click', function () { showAt(index - 1); });
                next.addEventListener('click', function () { showAt(index + 1); });
                lightbox.addEventListener('click', function (event) { if (event.target === lightbox) lightbox.close(); });
                lightbox.addEventListener('keydown', function (event) {
                    if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
                        event.preventDefault();
                        showAt(index + (event.key === 'ArrowLeft' ? -1 : 1));
                    }
                });
                lightbox.addEventListener('close', function () {
                    document.body.style.overflow = previousOverflow;
                    if (trigger) trigger.focus({preventScroll: true});
                });
            })();
        </script>
    @endonce
@endif
