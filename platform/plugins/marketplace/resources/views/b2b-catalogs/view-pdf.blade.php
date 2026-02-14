@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">{{ $catalog->title }}</h4>
            <a href="{{ route('marketplace.b2b-catalogs.index') }}" class="btn btn-secondary btn-sm">
                {{ __('Back to Catalogs') }}
            </a>
        </div>
        <div class="card-body p-0">
            <div id="pdf-viewer-container">
                <div id="pdf-toolbar" style="display: flex; align-items: center; justify-content: center; gap: 10px; padding: 10px; background: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                    <button id="prev-page" class="btn btn-sm btn-outline-secondary" disabled>&laquo; {{ __('Previous') }}</button>
                    <span>{{ __('Page') }} <span id="page-num">0</span> / <span id="page-count">0</span></span>
                    <button id="next-page" class="btn btn-sm btn-outline-secondary" disabled>{{ __('Next') }} &raquo;</button>
                    <span style="margin-left: 20px;">
                        <button id="zoom-out" class="btn btn-sm btn-outline-secondary">-</button>
                        <span id="zoom-level">100%</span>
                        <button id="zoom-in" class="btn btn-sm btn-outline-secondary">+</button>
                    </span>
                    <span id="page-loading" style="margin-left: 10px; color: #6c757d; display: none;">
                        <span class="spinner-border spinner-border-sm" role="status"></span>
                    </span>
                    <span id="loading-indicator" style="margin-left: 15px; color: #6c757d;">
                        <span class="spinner-border spinner-border-sm" role="status"></span> {{ __('Loading...') }}
                    </span>
                </div>
                <div id="pdf-canvas-wrapper" style="overflow: auto; text-align: center; background: #525659; padding: 20px; min-height: 600px;">
                    <canvas id="pdf-canvas" style="box-shadow: 0 2px 10px rgba(0,0,0,0.3);"></canvas>
                </div>
            </div>
        </div>
    </div>

    @push('footer')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const url = @json($streamUrl);
                const pdfjsLib = window['pdfjs-dist/build/pdf'];
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

                let pdfDoc = null;
                let pageNum = 1;
                let scale = 1.5;
                let pageRendering = false;
                let pageNumPending = null;
                const canvas = document.getElementById('pdf-canvas');
                const ctx = canvas.getContext('2d');

                function renderPage(num) {
                    if (pageRendering) {
                        pageNumPending = num;
                        return;
                    }
                    pageRendering = true;
                    document.getElementById('page-loading').style.display = 'inline';

                    pdfDoc.getPage(num).then(function (page) {
                        const viewport = page.getViewport({ scale: scale });
                        canvas.height = viewport.height;
                        canvas.width = viewport.width;

                        const renderContext = {
                            canvasContext: ctx,
                            viewport: viewport,
                        };

                        page.render(renderContext).promise.then(function () {
                            pageRendering = false;
                            document.getElementById('page-loading').style.display = 'none';
                            document.getElementById('page-num').textContent = num;
                            if (pageNumPending !== null) {
                                renderPage(pageNumPending);
                                pageNumPending = null;
                            }
                        });
                    });
                }

                const loadingTask = pdfjsLib.getDocument({
                    url: url,
                    rangeChunkSize: 65536,
                    disableAutoFetch: false,
                    disableStream: false,
                });

                let firstPageRendered = false;

                loadingTask.onProgress = function (data) {
                    if (data.total > 0) {
                        const pct = Math.round((data.loaded / data.total) * 100);
                        document.getElementById('loading-indicator').innerHTML =
                            '<span class="spinner-border spinner-border-sm" role="status"></span> {{ __("Loading") }} ' + pct + '%';
                    }
                };

                loadingTask.promise.then(function (pdf) {
                    pdfDoc = pdf;
                    document.getElementById('page-count').textContent = pdf.numPages;
                    document.getElementById('loading-indicator').style.display = 'none';
                    document.getElementById('prev-page').disabled = false;
                    document.getElementById('next-page').disabled = false;
                    if (!firstPageRendered) {
                        firstPageRendered = true;
                        renderPage(pageNum);
                    }
                });

                document.getElementById('prev-page').addEventListener('click', function () {
                    if (pageNum <= 1) return;
                    pageNum--;
                    renderPage(pageNum);
                });

                document.getElementById('next-page').addEventListener('click', function () {
                    if (!pdfDoc || pageNum >= pdfDoc.numPages) return;
                    pageNum++;
                    renderPage(pageNum);
                });

                document.getElementById('zoom-in').addEventListener('click', function () {
                    scale += 0.25;
                    document.getElementById('zoom-level').textContent = Math.round(scale / 1.5 * 100) + '%';
                    renderPage(pageNum);
                });

                document.getElementById('zoom-out').addEventListener('click', function () {
                    if (scale <= 0.5) return;
                    scale -= 0.25;
                    document.getElementById('zoom-level').textContent = Math.round(scale / 1.5 * 100) + '%';
                    renderPage(pageNum);
                });
            });
        </script>
    @endpush
@endsection
