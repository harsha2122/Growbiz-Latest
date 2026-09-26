{{-- Video file picker widget - reuses Botble's real media picker (rvMedia jQuery plugin) --}}
<div class="attachment-wrapper mb-2">
    <input type="hidden" name="video_file" class="attachment-url"
           value="{{ $ad && $ad->isLocalVideo() ? $ad->video_file : '' }}">
    <a href="javascript:void(0);" class="btn btn-primary btn_gallery_video" data-action="attachment">
        {{ __('Choose from Media Library') }}
    </a>
    <div class="attachment-info mt-2">
        @if ($ad && $ad->isLocalVideo())
            <div class="alert alert-info mb-0">
                <strong>{{ __('Current Video:') }}</strong>
                {{ basename($ad->video_file) }}
                <span class="badge bg-secondary">{{ $ad->getFormattedSize() }}</span>
            </div>
        @endif
    </div>
</div>
<small class="text-muted d-block mb-2">
    {{ __('Formats: MP4, WebM, OGG, MOV, AVI, MKV (Max: 500MB)') }}
</small>

<script>
    // Bind the real Botble media picker (rvMedia jQuery plugin) - reuses the same
    // #rv_media_modal already present on admin pages, filtered to videos only.
    (function bindAdsVideoPicker() {
        if (typeof jQuery === 'undefined' || !jQuery.fn.rvMedia) {
            setTimeout(bindAdsVideoPicker, 300);
            return;
        }

        jQuery('.btn_gallery_video').each(function () {
            var $btn = jQuery(this);
            if ($btn.data('rv-media-bound')) {
                return;
            }
            $btn.data('rv-media-bound', true);

            $btn.rvMedia({
                multiple: false,
                filter: 'video',
                view_in: 'all_media',
                onSelectFiles: function (files, $el) {
                    var file = files && files[0];
                    if (!file) {
                        return;
                    }

                    var $wrapper = $el.closest('.attachment-wrapper');
                    $wrapper.find('.attachment-url').val(file.url).trigger('change');
                    $wrapper.find('.attachment-info').html(
                        '<div class="alert alert-info mb-0">' +
                        '<strong>{{ __('Current Video:') }}</strong> ' +
                        (file.name || file.url.split('/').pop()) +
                        (file.size ? ' <span class="badge bg-secondary">' + file.size + '</span>' : '') +
                        '</div>'
                    );
                },
            });
        });
    })();
</script>
