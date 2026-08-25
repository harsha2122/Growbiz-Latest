@if ($store->hasActiveSponsoredVideo())
    @php
        $sponsoredVideosData = [];
    @endphp

    <div class="bb-sponsored-video-section">
        <div class="row g-3">
            @foreach ($store->activeSponsoredVideos() as $sponsoredVideo)
                @php
                    $videoUrl = $sponsoredVideo->video_url;
                    $provider = 'generic';
                    $embedUrl = $videoUrl;
                    $embedHtml = null;

                    if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $videoUrl, $matches)) {
                        $provider = 'iframe';
                        $embedUrl = 'https://www.youtube.com/embed/' . $matches[1] . '?autoplay=1';
                    } elseif (preg_match('/vimeo\.com\/(?:video\/)?(\d+)/', $videoUrl, $matches)) {
                        $provider = 'iframe';
                        $embedUrl = 'https://player.vimeo.com/video/' . $matches[1] . '?autoplay=1';
                    } elseif (preg_match('/\.(mp4|webm|ogg)(\?|$)/i', $videoUrl)) {
                        $provider = 'direct';
                        $embedUrl = $videoUrl;
                    } elseif (preg_match('#^https?://(?:www\.)?instagram\.com/(p|reel|tv)/#', $videoUrl)
                        && ($scrapedVideoUrl = get_instagram_video_url($videoUrl))
                    ) {
                        // Real, guaranteed-correct playback of the actual video.
                        $provider = 'direct';
                        $embedUrl = $scrapedVideoUrl;
                    } elseif (preg_match('#^https?://(?:www\.)?instagram\.com/(p|reel|tv)/#', $videoUrl)) {
                        // Fallback for non-video posts (or if scraping fails): Instagram's
                        // own embed widget, best-effort without an oEmbed API token.
                        $provider = 'instagram-oembed';
                        $embedHtml = build_instagram_embed_html($videoUrl);
                    } elseif (preg_match('#^https?://(?:www\.|m\.|web\.)?facebook\.com/.*/videos/#', $videoUrl)
                        || preg_match('#^https?://fb\.watch/#', $videoUrl)
                    ) {
                        // facebook.com blocks direct framing, but their video plugin endpoint is iframe-able.
                        $provider = 'iframe';
                        $embedUrl = 'https://www.facebook.com/plugins/video.php?href=' . urlencode($videoUrl) . '&show_text=false&autoplay=true';
                    } elseif (preg_match('#^https?://(?:www\.)?instagram\.com/#', $videoUrl)
                        || preg_match('#^https?://(?:www\.|m\.|web\.)?facebook\.com/#', $videoUrl)
                        || preg_match('#^https?://fb\.watch/#', $videoUrl)
                    ) {
                        // Profile/page links (not a single post/reel/video) - no single
                        // piece of content to embed. Link out instead.
                        $provider = 'external';
                        $embedUrl = $videoUrl;
                    } else {
                        $provider = 'iframe';
                    }

                    $videoIndex = count($sponsoredVideosData);
                    $sponsoredVideosData[] = [
                        'provider' => $provider,
                        'url' => $embedUrl,
                        'html' => $embedHtml,
                    ];
                @endphp

                <div class="col-md-6">
                    <div class="bb-sponsored-video-card">
                        <span class="bb-sponsored-badge">{{ __('Sponsored') }}</span>
                        <div class="bb-sponsored-video-link" onclick="openSponsoredVideo({{ $videoIndex }})" style="cursor: pointer;">
                            <div class="bb-sponsored-video-thumbnail">
                                @if ($sponsoredVideo->thumbnail)
                                    {{ RvMedia::image($sponsoredVideo->thumbnail, $store->name . ' - Sponsored Video', attributes: ['class' => 'bb-sponsored-thumb-img']) }}
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
                                @if ($sponsoredVideo->expires_at)
                                    <span class="bb-sponsored-video-expiry">
                                        {{ __('Available until :date', ['date' => $sponsoredVideo->expires_at->format('M d, Y')]) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
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
            height: 100%;
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
        .bb-video-modal-body.bb-video-modal-body-auto {
            padding-bottom: 0;
            height: auto;
            max-height: 80vh;
            overflow-y: auto;
            padding: 20px;
            background: #fff;
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
        var BB_SPONSORED_VIDEOS = @json($sponsoredVideosData);

        function openSponsoredVideo(index) {
            var data = BB_SPONSORED_VIDEOS[index];
            if (!data) {
                return;
            }

            if (data.provider === 'external') {
                window.open(data.url, '_blank', 'noopener');
                return;
            }

            var modal = document.getElementById('sponsoredVideoModal');
            var body = document.getElementById('sponsoredVideoBody');
            body.classList.toggle('bb-video-modal-body-auto', data.provider === 'instagram-oembed');

            // Make the modal visible BEFORE injecting the Instagram widget - it measures
            // its container on process(), and a still-hidden (display:none) container
            // has zero width/height, so the embed renders collapsed to just its header.
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';

            if (data.provider === 'direct') {
                body.innerHTML = '<video controls autoplay><source src="' + data.url + '"></video>';
            } else if (data.provider === 'instagram-oembed') {
                body.innerHTML = data.html;

                if (window.instgrm && window.instgrm.Embeds) {
                    window.instgrm.Embeds.process();
                } else {
                    var script = document.createElement('script');
                    script.async = true;
                    script.src = 'https://www.instagram.com/embed.js';
                    document.body.appendChild(script);
                }
            } else {
                body.innerHTML = '<iframe src="' + data.url + '" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe>';
            }
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
