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

        toast.style.display = 'block';
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Uploading...';

        var formData = new FormData(form);
        var xhr = new XMLHttpRequest();

        xhr.upload.addEventListener('progress', function (ev) {
            if (ev.lengthComputable) {
                var pct = Math.round((ev.loaded / ev.total) * 100);
                bar.style.width = pct + '%';
                percentText.textContent = pct + '%';
                sizeText.textContent = formatBytes(ev.loaded) + ' / ' + formatBytes(ev.total);
                if (pct >= 100) {
                    statusText.textContent = 'Processing...';
                    bar.classList.remove('progress-bar-animated');
                    bar.classList.add('bg-success');
                }
            }
        });

        xhr.addEventListener('load', function () {
            if (xhr.status >= 200 && xhr.status < 400) {
                statusText.textContent = 'Upload Complete!';
                bar.classList.add('bg-success');
                percentText.textContent = '100%';
                try {
                    var resp = JSON.parse(xhr.responseText);
                    if (resp.next_url) { window.location.href = resp.next_url; return; }
                } catch (ex) {}
                window.location.href = '{{ $redirectUrl }}';
            } else {
                statusText.textContent = 'Upload Failed!';
                bar.classList.add('bg-danger');
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Retry';
                try {
                    var err = JSON.parse(xhr.responseText);
                    if (err.message) sizeText.textContent = err.message;
                } catch (ex) { sizeText.textContent = 'Server error.'; }
            }
        });

        xhr.addEventListener('error', function () {
            statusText.textContent = 'Upload Failed!';
            bar.classList.add('bg-danger');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Retry';
            sizeText.textContent = 'Network error.';
        });

        xhr.open(form.method, form.action, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.send(formData);
    });

    function formatBytes(b) {
        if (b === 0) return '0 B';
        var k = 1024, s = ['B', 'KB', 'MB', 'GB'], i = Math.floor(Math.log(b) / Math.log(k));
        return parseFloat((b / Math.pow(k, i)).toFixed(1)) + ' ' + s[i];
    }
});
</script>
