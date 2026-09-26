@php
    $existingVideos = $store && $store->id ? $store->sponsoredVideos()->get() : collect();
    $maxVideos = \Botble\Marketplace\Models\Store::MAX_SPONSORED_VIDEOS;
@endphp

<div id="sponsored-videos-wrapper">
    <h4 style="margin-top: 20px; margin-bottom: 10px; padding-top: 15px; border-top: 1px solid #eee;">
        {{ __('Sponsored Videos (Admin Only)') }}
        <small class="text-muted" style="font-weight: normal;">
            {{ __('- up to :max videos | Videos auto-delete 10 days after expiry', ['max' => $maxVideos]) }}
        </small>
    </h4>

    <div id="sponsored-videos-rows">
        @foreach ($existingVideos as $video)
            <div class="sponsored-video-row border rounded p-3 mb-3" data-row>
                <input type="hidden" name="sponsored_videos[{{ $video->id }}][id]" value="{{ $video->id }}">

                <div class="row">
                    {{-- Video Source (Upload or URL) --}}
                    <div class="col-md-6 mb-3">
                        <div class="nav nav-pills mb-2" role="tablist">
                            <button class="nav-link {{ $video->isLocalVideo() ? 'active' : '' }}"
                                    id="tab-local-{{ $video->id }}" type="button" role="tab"
                                    onclick="switchVideoType(this, '{{ $video->id }}', 'local')">
                                {{ __('Upload Video') }}
                            </button>
                            <button class="nav-link {{ $video->isExternalVideo() ? 'active' : '' }}"
                                    id="tab-external-{{ $video->id }}" type="button" role="tab"
                                    onclick="switchVideoType(this, '{{ $video->id }}', 'external')">
                                {{ __('External URL') }}
                            </button>
                        </div>

                        {{-- Local Upload Section --}}
                        <div id="local-video-{{ $video->id }}" class="local-video-section {{ $video->isLocalVideo() ? '' : 'd-none' }}">
                            <label class="form-label">{{ __('Video File') }}</label>
                            <div class="mb-2">
                                <button class="btn btn-primary" type="button"
                                        onclick="openMediaPickerForVideo(this, '{{ $video->id }}')">
                                    {{ __('Choose from Media Library') }}
                                </button>
                            </div>
                            <small class="text-muted d-block">
                                {{ __('Formats: MP4, WebM, OGG, MOV, AVI, MKV (Max: 500MB)') }}
                            </small>

                            @if ($video->isLocalVideo())
                                <div class="alert alert-info mt-2 mb-0">
                                    <strong>{{ __('Current Video:') }}</strong>
                                    {{ basename($video->video_file) }}
                                    <span class="badge bg-secondary">{{ $video->getFormattedSize() }}</span>
                                </div>
                            @endif

                            <input type="hidden" name="sponsored_videos[{{ $video->id }}][video_file]"
                                   class="sponsored-video-file-input" value="{{ $video->isLocalVideo() ? $video->video_file : '' }}">
                        </div>

                        {{-- External URL Section --}}
                        <div id="external-video-{{ $video->id }}" class="external-video-section {{ $video->isExternalVideo() ? '' : 'd-none' }}">
                            <label class="form-label">{{ __('Video URL') }}</label>
                            <input type="text" class="form-control"
                                   name="sponsored_videos[{{ $video->id }}][video_url]"
                                   value="{{ $video->video_url }}"
                                   placeholder="{{ __('YouTube, Vimeo, Instagram, or Facebook video URL') }}">
                        </div>

                        <input type="hidden" name="sponsored_videos[{{ $video->id }}][video_type]"
                               class="video-type-input"
                               value="{{ $video->video_type ?? 'external' }}">
                    </div>

                    {{-- Expiry Date --}}
                    <div class="col-md-3 mb-3">
                        <label class="form-label">
                            {{ __('Expiry Date') }}
                            <small class="text-muted">({{ __('Auto-delete in 10 days') }})</small>
                        </label>
                        <input type="date" class="form-control"
                               name="sponsored_videos[{{ $video->id }}][expires_at]"
                               value="{{ $video->expires_at ? $video->expires_at->format('Y-m-d') : '' }}">
                        @if ($video->scheduled_deletion_at)
                            <small class="text-danger d-block mt-1">
                                {{ __('Scheduled for deletion:') }}
                                {{ $video->scheduled_deletion_at->format('M d, Y') }}
                            </small>
                        @endif
                    </div>

                    {{-- Thumbnail --}}
                    <div class="col-md-3 mb-3">
                        <label class="form-label">{{ __('Thumbnail') }}</label>
                        <input type="file" class="form-control"
                               name="sponsored_videos[{{ $video->id }}][thumbnail]"
                               accept="image/*">
                        @if ($video->thumbnail)
                            <img src="{{ \Botble\Media\Facades\RvMedia::getImageUrl($video->thumbnail) }}"
                                 alt="" style="max-height: 40px; margin-top: 6px;">
                        @endif
                    </div>
                </div>

                {{-- Stats --}}
                <div class="row">
                    <div class="col-md-12">
                        <span class="badge bg-info">
                            {{ __(':count clicks', ['count' => number_format($video->clicks)]) }}
                        </span>
                        <span class="badge {{ $video->isActive() ? 'bg-success' : 'bg-danger' }}">
                            {{ $video->isActive() ? __('Active') : __('Expired') }}
                        </span>
                    </div>
                </div>

                <hr class="my-3">

                <label class="form-check">
                    <input type="checkbox" class="form-check-input"
                           name="sponsored_videos[{{ $video->id }}][remove]" value="1">
                    <span class="form-check-label text-danger">{{ __('Remove this video') }}</span>
                </label>
            </div>
        @endforeach
    </div>

    <button type="button" class="btn btn-sm btn-outline-primary" id="add-sponsored-video-btn">
        {{ __('+ Add Video') }}
    </button>

    {{-- Template for new videos --}}
    <template id="sponsored-video-row-template">
        <div class="sponsored-video-row border rounded p-3 mb-3" data-row>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="nav nav-pills mb-2" role="tablist">
                        <button class="nav-link active" type="button" role="tab"
                                onclick="switchVideoType(this, '__INDEX__', 'local')">
                            {{ __('Upload Video') }}
                        </button>
                        <button class="nav-link" type="button" role="tab"
                                onclick="switchVideoType(this, '__INDEX__', 'external')">
                            {{ __('External URL') }}
                        </button>
                    </div>

                    <div id="local-video-__INDEX__" class="local-video-section">
                        <label class="form-label">{{ __('Video File') }}</label>
                        <div class="mb-2">
                            <button class="btn btn-primary" type="button"
                                    onclick="openMediaPickerForVideo('__INDEX__')">
                                {{ __('Choose from Media Library') }}
                            </button>
                        </div>
                        <small class="text-muted d-block">
                            {{ __('Formats: MP4, WebM, OGG, MOV, AVI, MKV (Max: 500MB)') }}
                        </small>
                        <input type="hidden" name="sponsored_videos[__INDEX__][video_file]"
                               class="sponsored-video-file-input" value="">
                    </div>

                    <div id="external-video-__INDEX__" class="external-video-section d-none">
                        <label class="form-label">{{ __('Video URL') }}</label>
                        <input type="text" class="form-control"
                               name="sponsored_videos[__INDEX__][video_url]"
                               placeholder="{{ __('YouTube, Vimeo, Instagram, or Facebook video URL') }}">
                    </div>

                    <input type="hidden" name="sponsored_videos[__INDEX__][video_type]"
                           class="video-type-input" value="local">
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">
                        {{ __('Expiry Date') }}
                        <small class="text-muted">({{ __('Auto-delete in 10 days') }})</small>
                    </label>
                    <input type="date" class="form-control"
                           name="sponsored_videos[__INDEX__][expires_at]">
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">{{ __('Thumbnail') }}</label>
                    <input type="file" class="form-control"
                           name="sponsored_videos[__INDEX__][thumbnail]"
                           accept="image/*">
                </div>
            </div>

            <button type="button" class="btn btn-sm btn-outline-danger remove-new-sponsored-video-row">
                {{ __('Remove') }}
            </button>
        </div>
    </template>
</div>

<script>
    // Switch between local upload and external URL
    function switchVideoType(btn, videoId, type) {
        const row = btn.closest('.sponsored-video-row');
        const typeInput = row.querySelector('.video-type-input');

        // Update type input
        typeInput.value = type;

        // Toggle tabs
        row.querySelectorAll('.nav-link').forEach(tab => tab.classList.remove('active'));
        btn.classList.add('active');

        // Toggle sections
        row.querySelector(`#local-video-${videoId}`).classList.toggle('d-none', type !== 'local');
        row.querySelector(`#external-video-${videoId}`).classList.toggle('d-none', type !== 'external');
    }

    // Store reference to currently selected video input
    let currentVideoInput = null;

    // Open media picker to select video from library
    function openMediaPickerForVideo(btn, videoId) {
        // Store reference to the input field for this video
        const row = btn.closest('.sponsored-video-row') || btn.closest('form');
        currentVideoInput = row ? row.querySelector('.sponsored-video-file-input') : null;

        if (!currentVideoInput) {
            alert('{{ __("Error: Could not find video input field") }}');
            return;
        }

        // Open media manager popup for file selection
        const mediaRoute = '{{ route('media.popup') }}?view_in=admin&folder_id=0';
        window.open(mediaRoute, 'media_manager', 'width=1200,height=700,resizable=yes');
    }

    // Handle media selection from media manager popup - called by media manager when file is selected
    window.RvMediaSelectCallback = function(selectedUrl) {
        if (currentVideoInput) {
            currentVideoInput.value = selectedUrl;
            alert('{{ __("Video selected: ") }}' + selectedUrl.split('/').pop());
            currentVideoInput = null;
        }
    };

    // Also support direct assignment from media manager
    window.RvMediaSelected = function(url) {
        if (currentVideoInput) {
            currentVideoInput.value = url;
            alert('{{ __("Video selected: ") }}' + url.split('/').pop());
            currentVideoInput = null;
        }
    };

    // Template management
    (function () {
        var wrapper = document.getElementById('sponsored-videos-wrapper');
        if (!wrapper || wrapper.dataset.initialized) {
            return;
        }
        wrapper.dataset.initialized = 'true';

        var maxVideos = {{ (int) $maxVideos }};
        var rowsContainer = document.getElementById('sponsored-videos-rows');
        var addBtn = document.getElementById('add-sponsored-video-btn');
        var template = document.getElementById('sponsored-video-row-template');
        var newRowCounter = 0;

        function countRows() {
            return rowsContainer.querySelectorAll('[data-row]').length;
        }

        function refreshAddButtonState() {
            addBtn.style.display = countRows() >= maxVideos ? 'none' : '';
        }

        addBtn.addEventListener('click', function () {
            if (countRows() >= maxVideos) {
                return;
            }

            newRowCounter++;
            var html = template.innerHTML.replace(/__INDEX__/g, 'new_' + newRowCounter);
            var wrapperDiv = document.createElement('div');
            wrapperDiv.innerHTML = html;
            var newRow = wrapperDiv.firstElementChild;
            rowsContainer.appendChild(newRow);

            newRow.querySelector('.remove-new-sponsored-video-row').addEventListener('click', function () {
                newRow.remove();
                refreshAddButtonState();
            });

            refreshAddButtonState();
        });

        refreshAddButtonState();
    })();
</script>
