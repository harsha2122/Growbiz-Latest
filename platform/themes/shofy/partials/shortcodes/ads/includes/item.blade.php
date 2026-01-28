@php
    $isVideo = $item->ad_media_type === 'video';
    $videoUrl = $item->video_url;
    $videoThumbnail = $item->video_thumbnail ? RvMedia::getImageUrl($item->video_thumbnail) : null;
@endphp

@if($isVideo && $videoUrl)
    {{-- Video Ad --}}
    <div class="tp-video-ad-wrapper" data-video-url="{{ $videoUrl }}" style="width: 100%; height: 100%; position: relative;">
        <div class="tp-video-thumbnail" style="width: 100%; height: 100%;">
            @if($videoThumbnail)
                <img src="{{ $videoThumbnail }}" alt="{{ $item->name }}" style="width: 100%; height: 100%; object-fit: cover;">
            @else
                <div style="width: 100%; height: 100%; min-height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center;">
                    <svg width="60" height="60" viewBox="0 0 24 24" fill="white"><path d="M8 5v14l11-7z"/></svg>
                </div>
            @endif
            <div class="tp-video-play-btn" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 70px; height: 70px; background: rgba(0,0,0,0.7); border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease; z-index: 5;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="white"><path d="M8 5v14l11-7z"/></svg>
            </div>
        </div>
        <div class="tp-video-player" style="display: none; width: 100%; height: 100%; position: absolute; top: 0; left: 0; min-height: 200px;">
            <div class="tp-video-close-btn" style="position: absolute; top: 10px; right: 10px; width: 36px; height: 36px; background: rgba(0,0,0,0.8); border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 10;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="white"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </div>
            <iframe class="tp-video-iframe" style="width: 100%; height: 100%; min-height: 200px; border: none;" allowfullscreen></iframe>
        </div>
    </div>
@else
    {{-- Image Ad --}}
    @if ($item->url)
        <a href="{{ $item->click_url }}" @if($item->open_in_new_tab) target="_blank" @endif>
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

                {{ RvMedia::image($item->image_url, $item->name, attributes: ['style' => 'width: 100%']) }}
            </picture>
    @if($item->url)
        </a>
    @endif
@endif
