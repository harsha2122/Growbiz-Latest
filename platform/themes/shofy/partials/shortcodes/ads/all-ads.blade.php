@php
    $columnClass = match((int) $columns) {
        2 => 'col-lg-6 col-md-6 col-sm-12',
        4 => 'col-xl-3 col-lg-4 col-md-6 col-sm-12',
        default => 'col-xl-4 col-lg-4 col-md-6 col-sm-12',
    };
@endphp

<section class="tp-all-ads-area pt-50 pb-50">
    <div class="container">
        @if($title)
            <div class="row">
                <div class="col-12">
                    <div class="tp-section-title-wrapper mb-40 text-center">
                        <h3 class="tp-section-title">{{ $title }}</h3>
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            @foreach($ads as $ad)
                <div class="{{ $columnClass }} mb-30">
                    <div class="tp-ad-card p-relative overflow-hidden rounded-3" style="border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); transition: all 0.3s ease;">
                        <div class="tp-ad-card-thumb">
                            @if ($ad->isVideoAd() && $ad->video_thumbnail)
                                {{-- Video Ad --}}
                                <div class="ad-video-container" data-video-url="{{ $ad->getEmbedVideoUrl() }}">
                                    <div class="ad-video-wrapper" style="position: relative; cursor: pointer;">
                                        <img
                                            src="{{ $ad->video_thumbnail_url }}"
                                            alt="{{ $ad->name }}"
                                            style="width: 100%; height: auto; display: block; border-radius: 10px;"
                                            class="ad-video-thumbnail"
                                        />
                                        <div class="ad-video-play-btn" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 60px; height: 60px; background: rgba(0,0,0,0.7); border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: transform 0.3s ease;">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
                                                <path d="M8 5v14l11-7z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ad-video-player" style="display: none; position: relative;">
                                        <button class="ad-video-close" style="position: absolute; top: -35px; right: 0; background: #333; color: white; border: none; padding: 8px 15px; cursor: pointer; z-index: 10; border-radius: 4px;">
                                            &times; Close
                                        </button>
                                        <iframe
                                            width="100%"
                                            height="auto"
                                            style="aspect-ratio: 16/9; display: block; border-radius: 10px;"
                                            src=""
                                            frameborder="0"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                            allowfullscreen
                                        ></iframe>
                                    </div>
                                </div>
                            @else
                                {{-- Image Ad --}}
                                @if ($ad->url)
                                    <a href="{{ $ad->click_url }}" @if($ad->open_in_new_tab) target="_blank" @endif>
                                @endif
                                    <picture>
                                        <source srcset="{{ $ad->image_url }}" media="(min-width: 1200px)" />
                                        <source srcset="{{ $ad->tablet_image_url }}" media="(min-width: 768px)" />
                                        <source srcset="{{ $ad->mobile_image_url }}" media="(max-width: 767px)" />
                                        {{ RvMedia::image($ad->image_url, $ad->name, attributes: ['style' => 'width: 100%; height: auto; display: block; border-radius: 10px;']) }}
                                    </picture>
                                @if($ad->url)
                                    </a>
                                @endif
                            @endif
                        </div>

                        @if($ad->name && !$ad->isVideoAd())
                            <div class="tp-ad-card-content p-3" style="padding: 15px;">
                                <h4 class="tp-ad-card-title mb-0" style="font-size: 16px; font-weight: 600;">
                                    @if ($ad->url)
                                        <a href="{{ $ad->click_url }}" @if($ad->open_in_new_tab) target="_blank" @endif style="color: #333; text-decoration: none;">
                                            {{ $ad->name }}
                                        </a>
                                    @else
                                        {{ $ad->name }}
                                    @endif
                                </h4>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if($ads->isEmpty())
            <div class="row">
                <div class="col-12 text-center">
                    <p class="text-muted">{{ __('No promotions available at the moment.') }}</p>
                </div>
            </div>
        @endif
    </div>
</section>

<style>
    .tp-ad-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.12) !important;
    }
    .tp-ad-card-thumb img {
        transition: transform 0.3s ease;
    }
    .tp-ad-card:hover .tp-ad-card-thumb img {
        transform: scale(1.02);
    }
    .ad-video-wrapper:hover .ad-video-play-btn {
        transform: translate(-50%, -50%) scale(1.1);
    }
</style>

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
