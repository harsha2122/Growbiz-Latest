@php
    $videoType = $ad->video_type ?? 'local';
@endphp

<div class="form-group">
    <label class="form-label">{{ __('Video Source') }}</label>
    <div class="nav nav-pills mb-2" role="tablist">
        <button class="nav-link {{ $videoType === 'local' ? 'active' : '' }}"
                id="tab-local-video" type="button" role="tab"
                onclick="switchAdsVideoType(this, 'local')">
            {{ __('Upload Video') }}
        </button>
        <button class="nav-link {{ $videoType === 'external' ? 'active' : '' }}"
                id="tab-external-video" type="button" role="tab"
                onclick="switchAdsVideoType(this, 'external')">
            {{ __('External URL') }}
        </button>
    </div>

    {{-- Local Upload Section --}}
    <div id="local-video-section" class="local-video-section {{ $videoType === 'local' ? '' : 'd-none' }}">
        <label class="form-label">{{ __('Video File') }}</label>
        <div class="input-group mb-2">
            <input type="file" class="form-control" id="ads-video-file"
                   accept="video/mp4,video/webm,video/ogg,.mov,.avi,.mkv">
            <button class="btn btn-outline-secondary" type="button"
                    onclick="uploadAdsVideo()">
                {{ __('Upload') }}
            </button>
        </div>
        <small class="text-muted d-block">
            {{ __('Formats: MP4, WebM, OGG, MOV, AVI, MKV (Max: 500MB)') }}
        </small>

        @if ($ad && $ad->isLocalVideo())
            <div class="alert alert-info mt-2 mb-0">
                <strong>{{ __('Current Video:') }}</strong>
                {{ basename($ad->video_file) }}
                <span class="badge bg-secondary">{{ $ad->getFormattedSize() }}</span>
            </div>
        @endif

        <input type="hidden" name="video_file" id="video_file_input"
               value="{{ $ad && $ad->isLocalVideo() ? $ad->video_file : '' }}">
    </div>

    {{-- External URL Section --}}
    <div id="external-video-section" class="external-video-section {{ $videoType === 'external' ? '' : 'd-none' }}">
        <label class="form-label">{{ __('Video URL') }}</label>
        <input type="text" class="form-control" name="video_url"
               value="{{ $ad->video_url ?? '' }}"
               placeholder="{{ __('https://www.youtube.com/watch?v=... or https://vimeo.com/...') }}">
    </div>

    <input type="hidden" name="video_type" id="video_type_input"
           value="{{ $videoType }}">
</div>

<script>
    function switchAdsVideoType(btn, type) {
        // Update type input
        document.getElementById('video_type_input').value = type;

        // Toggle tabs
        document.querySelectorAll('.nav-link').forEach(tab => tab.classList.remove('active'));
        btn.classList.add('active');

        // Toggle sections
        document.getElementById('local-video-section').classList.toggle('d-none', type !== 'local');
        document.getElementById('external-video-section').classList.toggle('d-none', type !== 'external');
    }

    function uploadAdsVideo() {
        const fileInput = document.getElementById('ads-video-file');
        const file = fileInput.files[0];

        if (!file) {
            alert('{{ __("Please select a video file") }}');
            return;
        }

        const formData = new FormData();
        formData.append('video', file);
        formData.append('ad_id', {{ $ad->id ?? 0 }});

        const btn = event.target;
        btn.disabled = true;
        const originalText = btn.textContent;
        btn.textContent = '{{ __("Uploading...") }}';

        fetch('{{ route("admin.ads.upload-video") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            btn.textContent = originalText;

            if (data.success || data.data) {
                alert('{{ __("Video uploaded successfully!") }}');
                if (data.data && data.data.path) {
                    document.getElementById('video_file_input').value = data.data.path;
                }
            } else {
                alert('{{ __("Upload failed:") }} ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            btn.disabled = false;
            btn.textContent = originalText;
            alert('{{ __("Upload error:") }} ' + error.message);
        });
    }
</script>
