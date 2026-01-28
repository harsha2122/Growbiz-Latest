<section class="tp-banner-area pt-30 pb-30" @if(BaseHelper::isRtlEnabled()) dir="ltr" @endif>
    <div class="container">
        <div class="row">
            @foreach($ads as $ad)
                @php($countAds = count($ads))
                <div
                    class="
                        @if($countAds > 2)
                            col-xl-4
                        @elseif($countAds > 1)
                            @if($loop->first)
                                col-xl-8 col-lg-7
                            @else
                                col-xl-4 col-lg-5
                            @endif
                        @else
                            col-xl-12
                        @endif
                    "
                >
                    <div @class(['tp-banner-item tp-banner-height p-relative mb-30 z-index-1 fix', 'tp-banner-item-sm' => $countAds > 2])>
                        <div class="tp-banner-thumb include-bg transition-3" >
                            {!! Theme::partial('shortcodes.ads.includes.item', ['item' => $ad]) !!}
                        </div>

                        <div class="tp-banner-content">
                            @if($subtitle = $ad->getMetaData('subtitle', true))
                                <span>{!! BaseHelper::clean(nl2br($subtitle)) !!}</span>
                            @endif
                            @if($title = $ad->getMetaData('title', true))
                                <h3 class="tp-banner-title">
                                    @if ($ad->url)
                                        <a href="{{ $ad->click_url }}" @if($ad->open_in_new_tab) target="_blank" @endif>
                                    @endif
                                        {!! BaseHelper::clean(nl2br($title)) !!}</a>
                                    @if ($ad->url)
                                        </a>
                                    @endif
                                </h3>
                            @endif
                            @if($buttonLabel = $ad->getMetaData('button_label', true))
                                <div class="tp-banner-btn">
                                    @if ($ad->url)
                                        <a href="{{ $ad->click_url }}" @if($ad->open_in_new_tab) target="_blank" @endif class="tp-link-btn">
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
    </div>
</section>

<style>
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
            const ytMatch = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
            if (ytMatch) return 'https://www.youtube.com/embed/' + ytMatch[1] + '?autoplay=1';
            const vimeoMatch = url.match(/vimeo\.com\/(\d+)/);
            if (vimeoMatch) return 'https://player.vimeo.com/video/' + vimeoMatch[1] + '?autoplay=1';
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
                    player.innerHTML = '<div class="tp-video-close-btn" style="position: absolute; top: 10px; right: 10px; width: 36px; height: 36px; background: rgba(0,0,0,0.8); border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 10;"><svg width="18" height="18" viewBox="0 0 24 24" fill="white"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg></div><video src="' + embedUrl + '" style="width: 100%; height: 100%; object-fit: cover;" autoplay controls></video>';
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
