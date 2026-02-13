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
        e.preventDefault();
        e.stopImmediatePropagation();

        var fileInput = document.getElementById('pdf_file');
        var hasFile = fileInput && fileInput.files.length > 0;

        var toast = document.getElementById('upload-progress-toast');
        var bar = document.getElementById('upload-progress-bar');
        var percentText = document.getElementById('upload-percent-text');
        var statusText = document.getElementById('upload-status-text');
        var sizeText = document.getElementById('upload-size-text');
        var submitBtn = document.getElementById('b2b-submit-btn');

        submitBtn.disabled = true;

        if (hasFile) {
            toast.style.display = 'block';
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> {{ __("Uploading...") }}';
        } else {
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> {{ __("Saving...") }}';
        }

        var formData = new FormData(form);
        var xhr = new XMLHttpRequest();

        xhr.upload.addEventListener('progress', function (ev) {
            if (hasFile && ev.lengthComputable) {
                var percent = Math.round((ev.loaded / ev.total) * 100);
                bar.style.width = percent + '%';
                percentText.textContent = percent + '%';
                sizeText.textContent = formatBytes(ev.loaded) + ' / ' + formatBytes(ev.total);

                if (percent >= 100) {
                    statusText.textContent = '{{ __("Processing...") }}';
                    bar.classList.remove('progress-bar-animated');
                    bar.classList.add('bg-success');
                }
            }
        });

        xhr.addEventListener('load', function () {
            if (xhr.status >= 200 && xhr.status < 400) {
                if (hasFile) {
                    statusText.textContent = '{{ __("Upload Complete!") }}';
                    bar.classList.remove('progress-bar-animated');
                    bar.classList.add('bg-success');
                    percentText.textContent = '100%';
                }

                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.next_url) {
                        window.location.href = response.next_url;
                        return;
                    }
                } catch (ex) {}

                window.location.href = '{{ route("marketplace.vendor.b2b-catalogs.index") }}';
            } else {
                if (hasFile) {
                    statusText.textContent = '{{ __("Upload Failed!") }}';
                    bar.classList.remove('progress-bar-animated', 'bg-success');
                    bar.classList.add('bg-danger');
                }
                submitBtn.disabled = false;
                submitBtn.innerHTML = '{{ __("Retry") }}';

                try {
                    var errResponse = JSON.parse(xhr.responseText);
                    if (errResponse.errors) {
                        var msgs = [];
                        for (var field in errResponse.errors) {
                            msgs.push(errResponse.errors[field][0]);
                        }
                        if (hasFile) {
                            sizeText.innerHTML = msgs.join('<br>');
                        } else {
                            toast.style.display = 'block';
                            statusText.textContent = '{{ __("Error") }}';
                            sizeText.innerHTML = msgs.join('<br>');
                            bar.style.display = 'none';
                            percentText.style.display = 'none';
                        }
                    } else if (errResponse.message) {
                        toast.style.display = 'block';
                        statusText.textContent = '{{ __("Error") }}';
                        sizeText.textContent = errResponse.message;
                        bar.style.display = 'none';
                        percentText.style.display = 'none';
                    }
                } catch (ex) {
                    toast.style.display = 'block';
                    statusText.textContent = '{{ __("Error") }}';
                    sizeText.textContent = '{{ __("Server error. Please try again.") }}';
                    bar.style.display = 'none';
                    percentText.style.display = 'none';
                }
            }
        });

        xhr.addEventListener('error', function () {
            if (hasFile) {
                statusText.textContent = '{{ __("Upload Failed!") }}';
                bar.classList.add('bg-danger');
            }
            toast.style.display = 'block';
            statusText.textContent = '{{ __("Error") }}';
            sizeText.textContent = '{{ __("Network error. Please check your connection.") }}';
            submitBtn.disabled = false;
            submitBtn.innerHTML = '{{ __("Retry") }}';
        });

        xhr.open('POST', form.action, true);
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
