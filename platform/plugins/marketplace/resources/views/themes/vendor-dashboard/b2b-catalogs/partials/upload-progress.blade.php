{{-- Upload Progress Toast --}}
<div id="upload-progress-toast" style="display:none; position:fixed; bottom:30px; right:30px; z-index:9999; min-width:320px;">
    <div class="card shadow-lg border-0">
        <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <strong id="upload-status-text">{{ __('Uploading PDF...') }}</strong>
                <span id="upload-percent-text" class="text-primary fw-bold">0%</span>
            </div>
            <div class="progress" style="height: 8px;">
                <div id="upload-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
            </div>
            <small id="upload-size-text" class="text-muted mt-1 d-block"></small>
        </div>
    </div>
</div>

@push('pre-footer')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('b2b-catalog-form');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        var fileInput = document.getElementById('pdf_file');
        if (!fileInput || !fileInput.files.length) return;

        e.preventDefault();

        var toast = document.getElementById('upload-progress-toast');
        var bar = document.getElementById('upload-progress-bar');
        var percentText = document.getElementById('upload-percent-text');
        var statusText = document.getElementById('upload-status-text');
        var sizeText = document.getElementById('upload-size-text');
        var submitBtn = document.getElementById('b2b-submit-btn');

        var fileSize = fileInput.files[0].size;
        toast.style.display = 'block';
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> {{ __("Uploading...") }}';

        var formData = new FormData(form);
        var xhr = new XMLHttpRequest();

        xhr.upload.addEventListener('progress', function (e) {
            if (e.lengthComputable) {
                var percent = Math.round((e.loaded / e.total) * 100);
                bar.style.width = percent + '%';
                percentText.textContent = percent + '%';
                sizeText.textContent = formatBytes(e.loaded) + ' / ' + formatBytes(e.total);

                if (percent >= 100) {
                    statusText.textContent = '{{ __("Processing...") }}';
                    bar.classList.remove('progress-bar-animated');
                    bar.classList.add('bg-success');
                }
            }
        });

        xhr.addEventListener('load', function () {
            if (xhr.status >= 200 && xhr.status < 400) {
                statusText.textContent = '{{ __("Upload Complete!") }}';
                bar.classList.remove('progress-bar-animated');
                bar.classList.add('bg-success');
                percentText.textContent = '100%';

                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.next_url) {
                        window.location.href = response.next_url;
                        return;
                    }
                } catch (ex) {}

                window.location.href = '{{ route("marketplace.vendor.b2b-catalogs.index") }}';
            } else {
                statusText.textContent = '{{ __("Upload Failed!") }}';
                bar.classList.remove('progress-bar-animated', 'bg-success');
                bar.classList.add('bg-danger');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '{{ __("Retry") }}';

                try {
                    var errResponse = JSON.parse(xhr.responseText);
                    if (errResponse.message) {
                        sizeText.textContent = errResponse.message;
                    }
                } catch (ex) {
                    sizeText.textContent = '{{ __("Server error. Please try again.") }}';
                }
            }
        });

        xhr.addEventListener('error', function () {
            statusText.textContent = '{{ __("Upload Failed!") }}';
            bar.classList.add('bg-danger');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '{{ __("Retry") }}';
            sizeText.textContent = '{{ __("Network error. Please check your connection.") }}';
        });

        xhr.open(form.method, form.action, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.send(formData);
    });

    function formatBytes(bytes) {
        if (bytes === 0) return '0 B';
        var k = 1024;
        var sizes = ['B', 'KB', 'MB', 'GB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }
});
</script>
@endpush
