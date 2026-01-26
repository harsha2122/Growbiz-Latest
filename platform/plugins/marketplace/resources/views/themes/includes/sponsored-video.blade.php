@if ($store->hasActiveSponsoredVideo())
    <div class="bb-sponsored-video-section">
        <div class="bb-sponsored-video-card">
            <span class="bb-sponsored-badge">{{ __('Sponsored') }}</span>
            <a href="{{ $store->sponsored_video_url }}" target="_blank" rel="noopener noreferrer" class="bb-sponsored-video-link">
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
            </a>
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
        }
    </style>
@endif
