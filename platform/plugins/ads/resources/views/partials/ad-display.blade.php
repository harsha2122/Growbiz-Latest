@foreach($data as $item)
    @if ($item->ads_type === 'google_adsense' && $item->google_adsense_slot_id)
        <div {!! Html::attributes($attributes) !!}>
            @include('plugins/ads::partials.google-adsense.unit-ads-slot', ['slotId' => $item->google_adsense_slot_id])
        </div>
        @continue
    @endif

    {{-- Video Ad --}}
    @if ($item->isVideoAd() && $item->video_thumbnail)
        <div {!! Html::attributes($attributes) !!} class="ad-video-container" data-video-url="{{ $item->getEmbedVideoUrl() }}">
            <div class="ad-video-wrapper" style="position: relative; cursor: pointer;">
                <img
                    src="{{ $item->video_thumbnail_url }}"
                    alt="{{ $item->name }}"
                    style="max-width: 100%; display: block;"
                    class="ad-video-thumbnail"
                />
                <div class="ad-video-play-btn" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 70px; height: 70px; background: rgba(0,0,0,0.7); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="white">
                        <path d="M8 5v14l11-7z"/>
                    </svg>
                </div>
            </div>
            <div class="ad-video-player" style="display: none; position: relative;">
                <button class="ad-video-close" style="position: absolute; top: -30px; right: 0; background: #333; color: white; border: none; padding: 5px 10px; cursor: pointer; z-index: 10;">
                    &times; Close
                </button>
                <iframe
                    width="100%"
                    height="auto"
                    style="aspect-ratio: 16/9;"
                    src=""
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                ></iframe>
            </div>
        </div>
        @continue
    @endif

    {{-- Image Ad --}}
    @continue(! $item->image)

    <div {!! Html::attributes($attributes) !!}>
        @if ($item->url)
            <a href="{{ $item->click_url }}" @if ($item->open_in_new_tab) target="_blank" @endif title="{{ __('Banner') }}">
        @endif
                <picture>
                    <source
                        srcset="{{ $item->image_url }}"
                        media="(min-width: 1200px)"
                    />
                    <source
                        srcset="{{ $item->tablet_image_url }}"
                        media="(min-width: 768px)"
                    />
                    <source
                        srcset="{{ $item->mobile_image_url }}"
                        media="(max-width: 767px)"
                    />

                    {{ RvMedia::image($item->image_url, $item->name, attributes: ['style' => 'max-width: 100%']) }}
                </picture>
        @if($item->url)
            </a>
        @endif
    </div>
@endforeach

@once
@push('footer')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.ad-video-container').forEach(function(container) {
        var wrapper = container.querySelector('.ad-video-wrapper');
        var player = container.querySelector('.ad-video-player');
        var iframe = player.querySelector('iframe');
        var closeBtn = player.querySelector('.ad-video-close');
        var videoUrl = container.dataset.videoUrl;

        wrapper.addEventListener('click', function() {
            iframe.src = videoUrl;
            wrapper.style.display = 'none';
            player.style.display = 'block';
        });

        closeBtn.addEventListener('click', function() {
            iframe.src = '';
            player.style.display = 'none';
            wrapper.style.display = 'block';
        });
    });
});
</script>
@endpush
@endonce
