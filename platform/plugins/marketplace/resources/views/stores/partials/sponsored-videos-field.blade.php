@php
    $existingVideos = $store && $store->id ? $store->sponsoredVideos()->get() : collect();
    $maxVideos = \Botble\Marketplace\Models\Store::MAX_SPONSORED_VIDEOS;
@endphp

<div id="sponsored-videos-wrapper">
    <h4 style="margin-top: 20px; margin-bottom: 10px; padding-top: 15px; border-top: 1px solid #eee;">
        {{ __('Sponsored Videos (Admin Only)') }}
        <small class="text-muted" style="font-weight: normal;">{{ __('- up to :max videos', ['max' => $maxVideos]) }}</small>
    </h4>

    <div id="sponsored-videos-rows">
        @foreach ($existingVideos as $video)
            <div class="sponsored-video-row border rounded p-3 mb-3" data-row>
                <input type="hidden" name="sponsored_videos[{{ $video->id }}][id]" value="{{ $video->id }}">

                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">{{ __('Video URL') }}</label>
                        <input
                            type="text"
                            class="form-control"
                            name="sponsored_videos[{{ $video->id }}][video_url]"
                            value="{{ $video->video_url }}"
                            placeholder="{{ __('YouTube, Vimeo, Instagram, or Facebook video URL') }}"
                        >
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">{{ __('Expiry Date') }}</label>
                        <input
                            type="date"
                            class="form-control"
                            name="sponsored_videos[{{ $video->id }}][expires_at]"
                            value="{{ $video->expires_at ? $video->expires_at->format('Y-m-d') : '' }}"
                        >
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">{{ __('Thumbnail') }}</label>
                        <input type="file" class="form-control" name="sponsored_videos[{{ $video->id }}][thumbnail]" accept="image/*">
                        @if ($video->thumbnail)
                            <img src="{{ \Botble\Media\Facades\RvMedia::getImageUrl($video->thumbnail) }}" alt="" style="max-height: 40px; margin-top: 6px;">
                        @endif
                    </div>
                </div>

                <label class="form-check">
                    <input type="checkbox" class="form-check-input" name="sponsored_videos[{{ $video->id }}][remove]" value="1">
                    <span class="form-check-label text-danger">{{ __('Remove this video') }}</span>
                </label>
            </div>
        @endforeach
    </div>

    <button type="button" class="btn btn-sm btn-outline-primary" id="add-sponsored-video-btn">
        {{ __('+ Add Video') }}
    </button>

    <template id="sponsored-video-row-template">
        <div class="sponsored-video-row border rounded p-3 mb-3" data-row>
            <div class="row">
                <div class="col-md-6 mb-2">
                    <label class="form-label">{{ __('Video URL') }}</label>
                    <input
                        type="text"
                        class="form-control"
                        name="sponsored_videos[__INDEX__][video_url]"
                        placeholder="{{ __('YouTube, Vimeo, Instagram, or Facebook video URL') }}"
                    >
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">{{ __('Expiry Date') }}</label>
                    <input type="date" class="form-control" name="sponsored_videos[__INDEX__][expires_at]">
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">{{ __('Thumbnail') }}</label>
                    <input type="file" class="form-control" name="sponsored_videos[__INDEX__][thumbnail]" accept="image/*">
                </div>
            </div>

            <button type="button" class="btn btn-sm btn-outline-danger remove-new-sponsored-video-row">
                {{ __('Remove') }}
            </button>
        </div>
    </template>
</div>

<script>
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
