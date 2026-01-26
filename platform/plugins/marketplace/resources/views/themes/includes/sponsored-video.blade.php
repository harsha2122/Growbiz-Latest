@if ($store->hasActiveSponsoredVideo())
    @php
        $videoUrl = $store->sponsored_video_url;
        $embedUrl = '';

        // Convert YouTube URL to embed
        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $videoUrl, $matches)) {
            $embedUrl = 'https://www.youtube.com/embed/' . $matches[1] . '?autoplay=1';
        }
        // Convert Vimeo URL to embed
        elseif (preg_match('/vimeo\.com\/(?:video\/)?(\d+)/', $videoUrl, $matches)) {
            $embedUrl = 'https://player.vimeo.com/video/' . $matches[1] . '?autoplay=1';
        }
        // Instagram - use original URL in iframe
        elseif (str_contains($videoUrl, 'instagram.com')) {
            $embedUrl = str_replace('/reel/', '/reel/', $videoUrl);
            if (!str_contains($embedUrl, '/embed')) {
                $embedUrl = rtrim($embedUrl, '/') . '/embed';
            }
        }
        // Direct video URL (mp4, webm, etc.)
        elseif (preg_match('/\.(mp4|webm|ogg)(\?|$)/i', $videoUrl)) {
            $embedUrl = 'direct:' . $videoUrl;
        }
        // Fallback - try to embed as-is
        else {
            $embedUrl = $videoUrl;
        }
    @endphp

    <div class="bb-sponsored-video-section">
        <div class="bb-sponsored-video-card">
            <span class="bb-sponsored-badge">{{ __('Sponsored') }}</span>
            <div class="bb-sponsored-video-link" onclick="openSponsoredVideo('{{ $embedUrl }}')" style="cursor: pointer;">
                <div class="bb-sponsored-video-thumbnail">
                    @if ($store->sponsored_video_thumbnail)
                        {{ RvMedia::image($store->sponsored_video_thumbnail, $store->name . ' - Sponsored Video', attributes: ['class' => 'bb-sponsored-thumb-img']) }}
                    @else
                        <div class="bb-sponsored-thumb-placeholder">
                            <x-core::icon name="ti ti-video" />
                        </div>
                    @endif
                    <div class="bb-sponsored-play-overlay">
                        <div class="bb-sponsored-play-btn">
                            <x-core::icon name="ti ti-player-play-filled" />
                        </div>
                    </div>
                </div>
                <div class="bb-sponsored-video-info">
                    <span class="bb-sponsored-video-text">{{ __('Watch Promotional Video') }}</span>
                    @if ($store->sponsored_video_expires_at)
                        <span class="bb-sponsored-video-expiry">
                            {{ __('Available until :date', ['date' => $store->sponsored_video_expires_at->format('M d, Y')]) }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Video Modal -->
    <div class="bb-video-modal" id="sponsoredVideoModal" onclick="closeSponsoredVideo(event)">
        <div class="bb-video-modal-content" onclick="event.stopPropagation()">
            <button class="bb-video-modal-close" onclick="closeSponsoredVideo(event)">&times;</button>
            <div class="bb-video-modal-body" id="sponsoredVideoBody"></div>
        </div>
    </div>

    <style>
        .bb-sponsored-video-section {
            margin: 20px 0;
        }
        .bb-sponsored-video-card {
            position: relative;
            background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
        }
        .bb-sponsored-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a5a 100%);
            color: #fff;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            z-index: 10;
            box-shadow: 0 2px 8px rgba(238, 90, 90, 0.3);
        }
        .bb-sponsored-video-link {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 15px;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
        }
        .bb-sponsored-video-link:hover {
            background: rgba(0, 0, 0, 0.02);
        }
        .bb-sponsored-video-thumbnail {
            position: relative;
            width: 180px;
            min-width: 180px;
            height: 100px;
            border-radius: 8px;
            overflow: hidden;
            background: #e9ecef;
        }
        .bb-sponsored-thumb-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .bb-sponsored-thumb-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            font-size: 32px;
        }
        .bb-sponsored-play-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }
        .bb-sponsored-video-link:hover .bb-sponsored-play-overlay {
            background: rgba(0, 0, 0, 0.5);
        }
        .bb-sponsored-play-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.95);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ee5a5a;
            font-size: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        .bb-sponsored-video-link:hover .bb-sponsored-play-btn {
            transform: scale(1.1);
            background: #fff;
        }
        .bb-sponsored-video-info {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .bb-sponsored-video-text {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }
        .bb-sponsored-video-expiry {
            font-size: 12px;
            color: #888;
        }

        /* Modal Styles */
        .bb-video-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            z-index: 99999;
            align-items: center;
            justify-content: center;
        }
        .bb-video-modal.active {
            display: flex;
        }
        .bb-video-modal-content {
            position: relative;
            width: 90%;
            max-width: 900px;
            background: #000;
            border-radius: 12px;
            overflow: hidden;
        }
        .bb-video-modal-close {
            position: absolute;
            top: -40px;
            right: 0;
            background: none;
            border: none;
            color: #fff;
            font-size: 32px;
            cursor: pointer;
            z-index: 10;
            padding: 5px 10px;
        }
        .bb-video-modal-close:hover {
            color: #ff6b6b;
        }
        .bb-video-modal-body {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
        }
        .bb-video-modal-body iframe,
        .bb-video-modal-body video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }

        @media (max-width: 576px) {
            .bb-sponsored-video-link {
                flex-direction: column;
                text-align: center;
            }
            .bb-sponsored-video-thumbnail {
                width: 100%;
                min-width: 100%;
                height: 150px;
            }
            .bb-sponsored-badge {
                top: 8px;
                right: 8px;
            }
            .bb-video-modal-content {
                width: 95%;
            }
            .bb-video-modal-close {
                top: -35px;
                font-size: 28px;
            }
        }
    </style>

    <script>
        function openSponsoredVideo(url) {
            var modal = document.getElementById('sponsoredVideoModal');
            var body = document.getElementById('sponsoredVideoBody');

            if (url.startsWith('direct:')) {
                // Direct video file
                var videoUrl = url.replace('direct:', '');
                body.innerHTML = '<video controls autoplay><source src="' + videoUrl + '"></video>';
            } else {
                // Embed URL (YouTube, Vimeo, Instagram, etc.)
                body.innerHTML = '<iframe src="' + url + '" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe>';
            }

            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeSponsoredVideo(event) {
            var modal = document.getElementById('sponsoredVideoModal');
            var body = document.getElementById('sponsoredVideoBody');

            modal.classList.remove('active');
            body.innerHTML = '';
            document.body.style.overflow = '';
        }

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeSponsoredVideo(e);
            }
        });
    </script>
@endif
