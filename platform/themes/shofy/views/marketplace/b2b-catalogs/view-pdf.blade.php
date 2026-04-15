@php
    Theme::layout('full-width');
    Theme::set('pageTitle', $pdf->title . ' — ' . $catalog->title);
    use Botble\Marketplace\Facades\MarketplaceHelper;
    $contactNumber  = $catalog->contact_number  ?: MarketplaceHelper::getSetting('b2b_contact_call_number', '');
    $whatsappNumber = $catalog->whatsapp_number ?: MarketplaceHelper::getSetting('b2b_contact_whatsapp_number', '');
    $whatsappDigits = preg_replace('/\D/', '', $whatsappNumber);
@endphp

<style>
    .bb-pdf-viewer-wrap {
        background: #1e1e2e;
        min-height: calc(100vh - 120px);
        display: flex;
        flex-direction: column;
    }

    /* ── Top bar ─────────────────────────────────────────── */
    .bb-pdf-topbar {
        background: #16213e;
        padding: 0 24px;
        height: 58px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-shrink: 0;
        border-bottom: 1px solid rgba(255,255,255,.08);
        position: sticky;
        top: 0;
        z-index: 100;
    }

    .bb-pdf-topbar-left {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .bb-pdf-back-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: rgba(255,255,255,.7);
        font-size: .82rem;
        font-weight: 600;
        text-decoration: none !important;
        padding: 6px 12px;
        border-radius: 8px;
        background: rgba(255,255,255,.08);
        transition: background .15s;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .bb-pdf-back-btn:hover {
        background: rgba(255,255,255,.14);
        color: #fff !important;
    }

    .bb-pdf-topbar-title {
        min-width: 0;
    }

    .bb-pdf-topbar-title .catalog-name {
        font-size: .72rem;
        color: rgba(255,255,255,.45);
        text-transform: uppercase;
        letter-spacing: .5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .bb-pdf-topbar-title .pdf-name {
        font-size: .92rem;
        font-weight: 600;
        color: #fff;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .bb-pdf-topbar-right {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    .bb-pdf-topbar-right a {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: .8rem;
        font-weight: 600;
        padding: 6px 14px;
        border-radius: 8px;
        text-decoration: none !important;
        transition: opacity .2s;
    }

    .bb-pdf-topbar-right a:hover { opacity: .85; }

    .bb-pdf-contact-call {
        background: rgba(59,130,246,.2);
        color: #93c5fd !important;
        border: 1px solid rgba(59,130,246,.3);
    }

    .bb-pdf-contact-wa {
        background: rgba(37,211,102,.15);
        color: #86efac !important;
        border: 1px solid rgba(37,211,102,.25);
    }

    /* ── Controls bar ────────────────────────────────────── */
    .bb-pdf-controls {
        background: #1a1a2e;
        padding: 0 24px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        flex-shrink: 0;
        border-bottom: 1px solid rgba(255,255,255,.06);
    }

    .bb-pdf-controls .ctrl-btn {
        background: rgba(255,255,255,.08);
        border: 1px solid rgba(255,255,255,.1);
        color: rgba(255,255,255,.8);
        font-size: .8rem;
        font-weight: 600;
        padding: 5px 14px;
        border-radius: 7px;
        cursor: pointer;
        transition: background .15s;
        line-height: 1.5;
    }

    .bb-pdf-controls .ctrl-btn:hover:not(:disabled) {
        background: rgba(255,255,255,.15);
        color: #fff;
    }

    .bb-pdf-controls .ctrl-btn:disabled {
        opacity: .35;
        cursor: not-allowed;
    }

    .bb-pdf-controls .ctrl-sep {
        width: 1px;
        height: 22px;
        background: rgba(255,255,255,.12);
        margin: 0 4px;
    }

    .bb-pdf-controls .page-info {
        font-size: .82rem;
        color: rgba(255,255,255,.6);
        min-width: 80px;
        text-align: center;
    }

    .bb-pdf-controls .zoom-label {
        font-size: .78rem;
        color: rgba(255,255,255,.5);
        min-width: 44px;
        text-align: center;
    }

    .bb-pdf-loading-bar {
        height: 3px;
        background: rgba(255,255,255,.05);
        position: relative;
        overflow: hidden;
        flex-shrink: 0;
    }

    .bb-pdf-loading-bar-inner {
        height: 100%;
        background: linear-gradient(90deg, #667eea, #764ba2);
        width: 0%;
        transition: width .3s;
    }

    /* ── Canvas area ─────────────────────────────────────── */
    .bb-pdf-canvas-area {
        flex: 1;
        overflow: auto;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding: 30px 20px;
        background: #2b2b3b;
    }

    #bb-pdf-canvas {
        box-shadow: 0 4px 32px rgba(0,0,0,.5);
        display: block;
        max-width: 100%;
    }

    .bb-pdf-spinner {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: rgba(255,255,255,.5);
        font-size: .88rem;
        gap: 16px;
        padding: 80px 20px;
    }

    .bb-pdf-spinner .spinner-border {
        width: 2.5rem;
        height: 2.5rem;
        border-width: .25rem;
        color: #667eea;
    }
</style>

<div class="bb-pdf-viewer-wrap">

    {{-- Top bar --}}
    <div class="bb-pdf-topbar">
        <div class="bb-pdf-topbar-left">
            <a href="{{ route('public.b2b-catalogs.index') }}" class="bb-pdf-back-btn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M19 12H5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                {{ __('Catalogues') }}
            </a>
            <div class="bb-pdf-topbar-title">
                <div class="catalog-name">{{ $catalog->title }}</div>
                <div class="pdf-name">{{ $pdf->title }}</div>
            </div>
        </div>

        <div class="bb-pdf-topbar-right">
            @if ($contactNumber)
                <a href="tel:{{ $contactNumber }}" class="bb-pdf-contact-call">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.47 9.81 19.79 19.79 0 01.4 1.13 2 2 0 012.38 1h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 8.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="d-none d-md-inline">{{ __('Call') }}</span>
                </a>
            @endif
            @if ($whatsappDigits)
                <a href="https://wa.me/{{ $whatsappDigits }}" target="_blank" rel="noopener" class="bb-pdf-contact-wa">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.464 3.488z"/></svg>
                    <span class="d-none d-md-inline">{{ __('WhatsApp') }}</span>
                </a>
            @endif
        </div>
    </div>

    {{-- Loading progress bar --}}
    <div class="bb-pdf-loading-bar">
        <div class="bb-pdf-loading-bar-inner" id="bbPdfProgress"></div>
    </div>

    {{-- Controls --}}
    <div class="bb-pdf-controls">
        <button id="bbPrevPage" class="ctrl-btn" disabled>&#8249; {{ __('Prev') }}</button>
        <span class="page-info">
            {{ __('Page') }} <span id="bbPageNum">—</span> / <span id="bbPageCount">—</span>
        </span>
        <button id="bbNextPage" class="ctrl-btn" disabled>{{ __('Next') }} &#8250;</button>

        <div class="ctrl-sep"></div>

        <button id="bbZoomOut" class="ctrl-btn">&#8722;</button>
        <span class="zoom-label" id="bbZoomLabel">100%</span>
        <button id="bbZoomIn" class="ctrl-btn">&#43;</button>
    </div>

    {{-- Canvas --}}
    <div class="bb-pdf-canvas-area" id="bbCanvasArea">
        <div class="bb-pdf-spinner" id="bbSpinner">
            <div class="spinner-border" role="status"></div>
            <span id="bbSpinnerText">{{ __('Loading PDF...') }}</span>
        </div>
        <canvas id="bb-pdf-canvas" style="display:none;"></canvas>
    </div>
</div>

@push('footer')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
(function () {
    'use strict';

    var STREAM_URL = @json($streamUrl);

    var pdfjsLib = window['pdfjs-dist/build/pdf'];
    pdfjsLib.GlobalWorkerOptions.workerSrc =
        'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    var pdfDoc        = null;
    var pageNum       = 1;
    var scale         = 1.5;
    var pageRendering = false;
    var pending       = null;

    var canvas      = document.getElementById('bb-pdf-canvas');
    var ctx         = canvas.getContext('2d');
    var spinner     = document.getElementById('bbSpinner');
    var spinnerText = document.getElementById('bbSpinnerText');
    var progress    = document.getElementById('bbPdfProgress');

    function setZoomLabel() {
        document.getElementById('bbZoomLabel').textContent =
            Math.round((scale / 1.5) * 100) + '%';
    }

    function renderPage(num) {
        if (pageRendering) { pending = num; return; }
        pageRendering = true;

        pdfDoc.getPage(num).then(function (page) {
            var viewport = page.getViewport({ scale: scale });
            canvas.width  = viewport.width;
            canvas.height = viewport.height;

            page.render({ canvasContext: ctx, viewport: viewport }).promise.then(function () {
                pageRendering = false;
                document.getElementById('bbPageNum').textContent = num;

                if (pending !== null) {
                    var n = pending;
                    pending = null;
                    renderPage(n);
                }
            });
        });
    }

    var task = pdfjsLib.getDocument({
        url:              STREAM_URL,
        rangeChunkSize:   65536,
        disableAutoFetch: false,
        disableStream:    false,
    });

    task.onProgress = function (data) {
        if (data.total > 0) {
            var pct = Math.round((data.loaded / data.total) * 100);
            progress.style.width = pct + '%';
            spinnerText.textContent = @json(__('Loading')) + ' ' + pct + '%…';
        }
    };

    task.promise.then(function (pdf) {
        pdfDoc = pdf;
        progress.style.width = '100%';
        setTimeout(function () { progress.style.opacity = '0'; }, 400);

        document.getElementById('bbPageCount').textContent = pdf.numPages;

        spinner.style.display = 'none';
        canvas.style.display  = 'block';

        document.getElementById('bbPrevPage').disabled = false;
        document.getElementById('bbNextPage').disabled = false;

        renderPage(pageNum);
    }).catch(function (err) {
        spinnerText.textContent = @json(__('Failed to load PDF. Please try again.'));
        console.error(err);
    });

    document.getElementById('bbPrevPage').addEventListener('click', function () {
        if (pageNum <= 1) return;
        renderPage(--pageNum);
    });

    document.getElementById('bbNextPage').addEventListener('click', function () {
        if (!pdfDoc || pageNum >= pdfDoc.numPages) return;
        renderPage(++pageNum);
    });

    document.getElementById('bbZoomIn').addEventListener('click', function () {
        scale += 0.25;
        setZoomLabel();
        renderPage(pageNum);
    });

    document.getElementById('bbZoomOut').addEventListener('click', function () {
        if (scale <= 0.5) return;
        scale -= 0.25;
        setZoomLabel();
        renderPage(pageNum);
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
            if (pdfDoc && pageNum < pdfDoc.numPages) renderPage(++pageNum);
        } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
            if (pageNum > 1) renderPage(--pageNum);
        }
    });
})();
</script>
@endpush
