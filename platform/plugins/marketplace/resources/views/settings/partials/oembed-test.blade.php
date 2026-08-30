<div class="card mb-3" style="max-width:700px;">
    <div class="card-body">
        <h6 class="mb-2">Test oEmbed Connection</h6>
        <p class="text-muted small mb-2">
            Save your settings above first, then paste any public Instagram or Facebook post/reel URL here to test
            whether the currently saved credentials can fetch a real embed.
        </p>
        <div class="d-flex gap-2 mb-2">
            <input type="text" id="oembed-test-url" class="form-control" placeholder="https://www.instagram.com/reel/xxxxxxxxx/">
            <button type="button" id="oembed-test-btn" class="btn btn-primary flex-shrink-0">Test Now</button>
        </div>
        <div id="oembed-test-result"></div>
    </div>
</div>

<script>
    (function () {
        var btn = document.getElementById('oembed-test-btn');
        if (!btn) {
            return;
        }

        btn.addEventListener('click', function () {
            var url = document.getElementById('oembed-test-url').value.trim();
            var result = document.getElementById('oembed-test-result');

            if (!url) {
                result.innerHTML = '<div class="alert alert-warning py-2 mb-0">Please paste a URL first.</div>';
                return;
            }

            btn.disabled = true;
            result.innerHTML = '<div class="text-muted">Testing…</div>';

            fetch('{{ route('marketplace.meta-ads-settings.test-oembed') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ url: url }),
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    btn.disabled = false;
                    if (data.success) {
                        result.innerHTML = '<div class="alert alert-success py-2 mb-0">'
                            + '<strong>It works!</strong> A real embed was returned. Credentials are valid and oEmbed Read is approved.'
                            + '</div>';
                    } else {
                        result.innerHTML = '<div class="alert alert-danger py-2 mb-0">'
                            + '<strong>Failed:</strong> ' + (data.message || 'Unknown error') + '</div>';
                    }
                })
                .catch(function () {
                    btn.disabled = false;
                    result.innerHTML = '<div class="alert alert-danger py-2 mb-0">Request failed. Check the browser console / server logs.</div>';
                });
        });
    })();
</script>
