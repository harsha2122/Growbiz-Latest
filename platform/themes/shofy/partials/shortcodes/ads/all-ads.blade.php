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
                @php
                    $adTitle = $ad->getMetaData('title', true);
                    $adSubtitle = $ad->getMetaData('subtitle', true);
                    $buttonLabel = $ad->getMetaData('button_label', true);
                    $isVideo = $ad->ad_media_type === 'video';
                    $videoUrl = $ad->video_url;
                    $videoThumbnail = $ad->video_thumbnail ? RvMedia::getImageUrl($ad->video_thumbnail) : null;
                @endphp
                <div class="{{ $columnClass }} mb-30">
                    <div class="tp-ad-card p-relative overflow-hidden" style="border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); transition: all 0.3s ease; background: #fff; height: 100%; display: flex; flex-direction: column;">
                        <div class="tp-ad-card-thumb p-relative" style="height: 220px; overflow: hidden;">
                            @if($isVideo && $videoUrl)
                                {{-- Video Ad --}}
                                <div class="tp-video-ad-wrapper" data-video-url="{{ $videoUrl }}" style="width: 100%; height: 100%; position: relative;">
                                    <div class="tp-video-thumbnail" style="width: 100%; height: 100%;">
                                        @if($videoThumbnail)
                                            <img src="{{ $videoThumbnail }}" alt="{{ $ad->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 12px 12px 0 0;">
                                        @else
                                            <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; border-radius: 12px 12px 0 0;">
                                                <svg width="60" height="60" viewBox="0 0 24 24" fill="white"><path d="M8 5v14l11-7z"/></svg>
                                            </div>
                                        @endif
                                        <div class="tp-video-play-btn" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 70px; height: 70px; background: rgba(0,0,0,0.7); border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease;">
                                            <svg width="28" height="28" viewBox="0 0 24 24" fill="white"><path d="M8 5v14l11-7z"/></svg>
                                        </div>
                                    </div>
                                    <div class="tp-video-player" style="display: none; width: 100%; height: 100%; position: absolute; top: 0; left: 0;">
                                        <div class="tp-video-close-btn" style="position: absolute; top: 10px; right: 10px; width: 36px; height: 36px; background: rgba(0,0,0,0.8); border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 10;">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="white"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                                        </div>
                                        <iframe class="tp-video-iframe" style="width: 100%; height: 100%; border: none; border-radius: 12px 12px 0 0;" allowfullscreen></iframe>
                                    </div>
                                </div>
                            @else
                                {{-- Image Ad --}}
                                @if ($ad->url)
                                    <a href="{{ $ad->click_url }}" @if($ad->open_in_new_tab) target="_blank" @endif style="display: block; width: 100%; height: 100%;">
                                @endif
                                    <picture>
                                        <source srcset="{{ $ad->image_url }}" media="(min-width: 1200px)" />
                                        <source srcset="{{ $ad->tablet_image_url }}" media="(min-width: 768px)" />
                                        <source srcset="{{ $ad->mobile_image_url }}" media="(max-width: 767px)" />
                                        {{ RvMedia::image($ad->image_url, $ad->name, attributes: ['style' => 'width: 100%; height: 100%; object-fit: cover; border-radius: 12px 12px 0 0;']) }}
                                    </picture>
                                @if($ad->url)
                                    </a>
                                @endif
                            @endif
                        </div>

                        <div class="tp-ad-card-content" style="padding: 20px; flex: 1; display: flex; flex-direction: column;">
                            @if($adSubtitle)
                                <span style="font-size: 13px; color: #e74c3c; font-weight: 500; margin-bottom: 6px; display: block;">{!! BaseHelper::clean(nl2br($adSubtitle)) !!}</span>
                            @endif

                            @if($adTitle)
                                <h3 style="font-size: 18px; font-weight: 700; color: #1a1a2e; margin-bottom: 8px; line-height: 1.3;">
                                    @if ($ad->url)
                                        <a href="{{ $ad->click_url }}" @if($ad->open_in_new_tab) target="_blank" @endif style="color: inherit; text-decoration: none;">
                                            {!! BaseHelper::clean(nl2br($adTitle)) !!}
                                        </a>
                                    @else
                                        {!! BaseHelper::clean(nl2br($adTitle)) !!}
                                    @endif
                                </h3>
                            @endif

                            @if($ad->name && !$adTitle)
                                <h4 style="font-size: 16px; font-weight: 600; color: #333; margin-bottom: 8px;">
                                    @if ($ad->url)
                                        <a href="{{ $ad->click_url }}" @if($ad->open_in_new_tab) target="_blank" @endif style="color: inherit; text-decoration: none;">
                                            {{ $ad->name }}
                                        </a>
                                    @else
                                        {{ $ad->name }}
                                    @endif
                                </h4>
                            @endif

                            @if($buttonLabel)
                                <div style="margin-top: auto; padding-top: 12px;">
                                    @if ($ad->url)
                                        <a href="{{ $ad->click_url }}" @if($ad->open_in_new_tab) target="_blank" @endif class="tp-ad-btn" style="display: inline-flex; align-items: center; gap: 8px; color: #3867d6; font-weight: 600; font-size: 14px; text-decoration: none; transition: all 0.3s ease;">
                                    @endif
                                        {{ $buttonLabel }}
                                        <svg width="15" height="13" viewBox="0 0 15 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M13.9998 6.19656L1 6.19656" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M8.75674 0.975394L14 6.19613L8.75674 11.4177" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    @if ($ad->url)
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
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
        box-shadow: 0 12px 30px rgba(0,0,0,0.15) !important;
    }
    .tp-ad-card-thumb img {
        transition: transform 0.3s ease;
    }
    .tp-ad-card:hover .tp-ad-card-thumb img {
        transform: scale(1.05);
    }
    .tp-ad-btn:hover {
        color: #2c54b8 !important;
        gap: 12px !important;
    }
    .tp-video-play-btn:hover {
        background: rgba(0,0,0,0.85) !important;
        transform: translate(-50%, -50%) scale(1.1);
    }
    .tp-video-close-btn:hover {
        background: rgba(220,53,69,0.9) !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.tp-video-ad-wrapper').forEach(function(wrapper) {
        const playBtn = wrapper.querySelector('.tp-video-play-btn');
        const closeBtn = wrapper.querySelector('.tp-video-close-btn');
        const thumbnail = wrapper.querySelector('.tp-video-thumbnail');
        const player = wrapper.querySelector('.tp-video-player');
        const iframe = wrapper.querySelector('.tp-video-iframe');
        const videoUrl = wrapper.dataset.videoUrl;

        function getEmbedUrl(url) {
            // YouTube
            const ytMatch = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
            if (ytMatch) return 'https://www.youtube.com/embed/' + ytMatch[1] + '?autoplay=1';

            // Vimeo
            const vimeoMatch = url.match(/vimeo\.com\/(\d+)/);
            if (vimeoMatch) return 'https://player.vimeo.com/video/' + vimeoMatch[1] + '?autoplay=1';

            // Direct video URL
            return url;
        }

        if (playBtn) {
            playBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const embedUrl = getEmbedUrl(videoUrl);

                if (embedUrl.includes('youtube.com') || embedUrl.includes('vimeo.com')) {
                    iframe.src = embedUrl;
                } else {
                    player.innerHTML = '<div class="tp-video-close-btn" style="position: absolute; top: 10px; right: 10px; width: 36px; height: 36px; background: rgba(0,0,0,0.8); border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 10;"><svg width="18" height="18" viewBox="0 0 24 24" fill="white"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg></div><video src="' + embedUrl + '" style="width: 100%; height: 100%; object-fit: cover; border-radius: 12px 12px 0 0;" autoplay controls></video>';
                    player.querySelector('.tp-video-close-btn').addEventListener('click', closeVideo);
                }

                thumbnail.style.display = 'none';
                player.style.display = 'block';
            });
        }

        function closeVideo() {
            iframe.src = '';
            const video = player.querySelector('video');
            if (video) video.pause();
            player.style.display = 'none';
            thumbnail.style.display = 'block';
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                closeVideo();
            });
        }
    });
});
</script>
