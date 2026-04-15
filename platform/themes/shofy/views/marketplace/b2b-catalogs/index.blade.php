@php
    Theme::layout('full-width');
    Theme::set('pageTitle', __('B2B Catalogues'));
@endphp

<style>
    /* ── Catalog page ─────────────────────────────────────── */
    .bb-catalog-section {
        padding: 60px 0 90px;
        background: #f8f9fb;
        min-height: 60vh;
    }

    .bb-catalog-page-header {
        margin-bottom: 40px;
    }

    .bb-catalog-page-header h2 {
        font-size: 2rem;
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 6px;
    }

    .bb-catalog-page-header p {
        color: #6b7280;
        font-size: 1rem;
    }

    /* ── Card ─────────────────────────────────────────────── */
    .bb-catalog-card {
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 16px rgba(0,0,0,.07);
        transition: transform .25s ease, box-shadow .25s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        cursor: pointer;
    }

    .bb-catalog-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 32px rgba(0,0,0,.12);
    }

    .bb-catalog-card-cover {
        position: relative;
        height: 170px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    /* Subtle pattern overlay */
    .bb-catalog-card-cover::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: radial-gradient(circle at 20% 80%, rgba(255,255,255,.08) 0%, transparent 50%),
                          radial-gradient(circle at 80% 20%, rgba(255,255,255,.06) 0%, transparent 50%);
    }

    .bb-catalog-card-cover svg {
        width: 72px;
        height: 72px;
        color: rgba(255,255,255,.85);
        position: relative;
        z-index: 1;
    }

    .bb-catalog-discount-badge {
        position: absolute;
        top: 14px;
        right: 14px;
        background: #ef4444;
        color: #fff;
        font-size: .72rem;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 20px;
        z-index: 2;
        letter-spacing: .3px;
    }

    .bb-catalog-pdf-count {
        position: absolute;
        bottom: 14px;
        left: 14px;
        background: rgba(0,0,0,.45);
        backdrop-filter: blur(4px);
        color: #fff;
        font-size: .75rem;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 20px;
        z-index: 2;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .bb-catalog-card-body {
        padding: 20px 22px 16px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .bb-catalog-store-name {
        font-size: .78rem;
        font-weight: 600;
        color: #7c3aed;
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-bottom: 6px;
    }

    .bb-catalog-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 8px;
        line-height: 1.35;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .bb-catalog-desc {
        font-size: .85rem;
        color: #6b7280;
        margin-bottom: 16px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        flex: 1;
    }

    .bb-catalog-browse-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff !important;
        border: none;
        border-radius: 10px;
        padding: 10px 20px;
        font-size: .88rem;
        font-weight: 600;
        cursor: pointer;
        width: 100%;
        transition: opacity .2s;
        text-decoration: none;
    }

    .bb-catalog-browse-btn:hover {
        opacity: .9;
        color: #fff !important;
    }

    /* ── Modal ────────────────────────────────────────────── */
    #catalogModal .modal-dialog {
        max-width: 600px;
    }

    #catalogModal .modal-content {
        border-radius: 18px;
        border: none;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,.2);
    }

    .bb-modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 24px 28px 20px;
        color: #fff;
        position: relative;
    }

    .bb-modal-header .btn-close {
        position: absolute;
        top: 18px;
        right: 20px;
        filter: invert(1);
        opacity: .8;
    }

    .bb-modal-header .bb-modal-store {
        font-size: .8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .6px;
        opacity: .8;
        margin-bottom: 4px;
    }

    .bb-modal-header h5 {
        font-size: 1.35rem;
        font-weight: 700;
        margin: 0;
        line-height: 1.3;
    }

    .bb-modal-header .bb-modal-meta {
        margin-top: 10px;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .bb-modal-header .bb-modal-meta .badge {
        font-size: .75rem;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 20px;
    }

    .bb-modal-body {
        padding: 0;
    }

    .bb-modal-desc {
        padding: 16px 28px 0;
        font-size: .88rem;
        color: #4b5563;
        line-height: 1.6;
    }

    .bb-pdf-list-header {
        padding: 18px 28px 10px;
        font-size: .8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .7px;
        color: #9ca3af;
    }

    .bb-pdf-list {
        list-style: none;
        margin: 0;
        padding: 0 16px 16px;
    }

    .bb-pdf-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 12px;
        border-radius: 12px;
        transition: background .15s;
        margin-bottom: 4px;
    }

    .bb-pdf-item:hover {
        background: #f5f3ff;
    }

    .bb-pdf-item-icon {
        width: 44px;
        height: 44px;
        background: #f0ebff;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: #7c3aed;
    }

    .bb-pdf-item-icon svg {
        width: 22px;
        height: 22px;
    }

    .bb-pdf-item-title {
        flex: 1;
        font-size: .92rem;
        font-weight: 600;
        color: #1a1a2e;
        line-height: 1.3;
    }

    .bb-pdf-view-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff !important;
        font-size: .78rem;
        font-weight: 600;
        padding: 6px 14px;
        border-radius: 8px;
        text-decoration: none !important;
        white-space: nowrap;
        transition: opacity .2s;
        flex-shrink: 0;
    }

    .bb-pdf-view-btn:hover {
        opacity: .88;
    }

    .bb-modal-contact {
        padding: 16px 28px 24px;
        border-top: 1px solid #f3f4f6;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .bb-modal-contact a {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: .84rem;
        font-weight: 600;
        padding: 8px 18px;
        border-radius: 10px;
        text-decoration: none !important;
        transition: opacity .2s;
    }

    .bb-modal-contact a:hover {
        opacity: .88;
    }

    .bb-contact-call {
        background: #eff6ff;
        color: #1d4ed8 !important;
        border: 1.5px solid #bfdbfe;
    }

    .bb-contact-wa {
        background: #f0fdf4;
        color: #166534 !important;
        border: 1.5px solid #bbf7d0;
    }

    /* ── Empty / search ───────────────────────────────────── */
    .bb-catalog-empty {
        text-align: center;
        padding: 80px 20px;
        color: #9ca3af;
    }

    .bb-catalog-empty svg {
        width: 64px;
        height: 64px;
        margin-bottom: 16px;
        opacity: .4;
    }

    .bb-catalog-search-bar {
        max-width: 440px;
    }

    .bb-catalog-search-bar .input-group {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,.07);
    }

    .bb-catalog-search-bar input {
        border: 1.5px solid #e5e7eb;
        border-right: none;
        border-radius: 12px 0 0 12px !important;
        padding: 10px 16px;
        font-size: .9rem;
    }

    .bb-catalog-search-bar input:focus {
        box-shadow: none;
        border-color: #667eea;
    }

    .bb-catalog-search-bar button {
        background: linear-gradient(135deg, #667eea, #764ba2);
        border: none;
        color: #fff;
        padding: 10px 20px;
        border-radius: 0 12px 12px 0 !important;
        font-size: .88rem;
        font-weight: 600;
    }
</style>

<section class="bb-catalog-section">
    <div class="container">

        {{-- Page header --}}
        <div class="bb-catalog-page-header d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h2>{{ __('B2B Catalogues') }}</h2>
                <p>{{ __('Browse product catalogues from our vendors') }}</p>
            </div>

            {{-- Search --}}
            <form class="bb-catalog-search-bar" action="{{ route('public.b2b-catalogs.index') }}" method="get">
                <div class="input-group">
                    <input
                        type="search"
                        name="q"
                        class="form-control"
                        placeholder="{{ __('Search catalogues...') }}"
                        value="{{ old('q', request('q')) }}"
                    >
                    <button type="submit">
                        <svg width="16" height="16" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8.11111 15.2222C12.0385 15.2222 15.2222 12.0385 15.2222 8.11111C15.2222 4.18375 12.0385 1 8.11111 1C4.18375 1 1 4.18375 1 8.11111C1 12.0385 4.18375 15.2222 8.11111 15.2222Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M17 17L13.1333 13.1333" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        {{ __('Search') }}
                    </button>
                </div>
            </form>
        </div>

        @if ($catalogs->isEmpty())
            <div class="bb-catalog-empty">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 2V8H20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <p class="fs-5 fw-semibold mb-1">{{ __('No catalogues found') }}</p>
                <p class="fs-sm">{{ __('Check back later or try a different search.') }}</p>
            </div>
        @else
            <div class="row g-4 mb-48">
                @foreach ($catalogs as $catalog)
                    @php
                        $pdfCount  = $catalog->pdfs->count();
                        $hasLegacy = ! $pdfCount && $catalog->pdf_path;
                        $totalPdfs = $pdfCount ?: ($hasLegacy ? 1 : 0);
                        $discount  = (float) $catalog->discount_percentage;
                    @endphp

                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div
                            class="bb-catalog-card"
                            data-bs-toggle="modal"
                            data-bs-target="#catalogModal"
                            data-catalog-id="{{ $catalog->id }}"
                        >
                            {{-- Cover --}}
                            <div class="bb-catalog-card-cover">
                                @if ($discount > 0)
                                    <span class="bb-catalog-discount-badge">{{ $discount }}% OFF</span>
                                @endif

                                <span class="bb-catalog-pdf-count">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 2V8H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    {{ $totalPdfs }} {{ $totalPdfs === 1 ? __('PDF') : __('PDFs') }}
                                </span>

                                {{-- Document stack icon --}}
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4 6C4 4.89543 4.89543 4 6 4H14L20 10V20C20 21.1046 19.1046 22 18 22H6C4.89543 22 4 21.1046 4 20V6Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>
                                    <path d="M14 4V10H20" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M8 14H16" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                                    <path d="M8 17H13" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                                </svg>
                            </div>

                            {{-- Body --}}
                            <div class="bb-catalog-card-body">
                                @if ($catalog->store?->id)
                                    <div class="bb-catalog-store-name">{{ $catalog->store->name }}</div>
                                @endif

                                <div class="bb-catalog-title">{{ $catalog->title }}</div>

                                @if ($catalog->description)
                                    <div class="bb-catalog-desc">{{ $catalog->description }}</div>
                                @else
                                    <div class="bb-catalog-desc" style="min-height:2.4em;"></div>
                                @endif

                                <button
                                    type="button"
                                    class="bb-catalog-browse-btn"
                                    tabindex="-1"
                                    aria-label="{{ __('Browse catalogues for :title', ['title' => $catalog->title]) }}"
                                >
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14 2H6C4.89 2 4 2.89 4 4V20C4 21.11 4.89 22 6 22H18C19.11 22 20 21.11 20 20V8L14 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 2V8H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    {{ __('Browse Catalogues') }}
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            {{ $catalogs->withQueryString()->links(Theme::getThemeNamespace('partials.pagination')) }}
        @endif
    </div>
</section>

{{-- ── Catalog Detail Modal ──────────────────────────────────── --}}
<div class="modal fade" id="catalogModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            {{-- Dynamic header (filled by JS) --}}
            <div class="bb-modal-header" id="catalogModalHeader">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                <div class="bb-modal-store" id="catalogModalStore"></div>
                <h5 id="catalogModalTitle"></h5>
                <div class="bb-modal-meta" id="catalogModalMeta"></div>
            </div>

            <div class="bb-modal-body modal-body p-0">
                <div class="bb-modal-desc" id="catalogModalDesc" style="display:none"></div>
                <div class="bb-pdf-list-header" id="catalogModalPdfHeader"></div>
                <ul class="bb-pdf-list" id="catalogModalPdfList">
                    {{-- filled by JS --}}
                </ul>
            </div>

            <div class="bb-modal-contact" id="catalogModalContact" style="display:none"></div>
        </div>
    </div>
</div>

@php
    $catalogsForJs = $catalogs->map(function ($c) {
        return [
            'id'          => $c->id,
            'title'       => $c->title,
            'store'       => $c->store?->name ?? null,
            'description' => $c->description,
            'discount'    => (float) $c->discount_percentage,
            'contact'     => $c->contact_number,
            'whatsapp'    => $c->whatsapp_number,
            'pdfs'        => $c->pdfs->map(function ($p) use ($c) {
                return [
                    'title'    => $p->title,
                    'view_url' => route('public.b2b-catalogs.pdf.view', [$c->id, $p->id]),
                ];
            })->values()->all(),
        ];
    })->keyBy('id')->all();
@endphp

{{-- Catalog data passed to JS --}}
<script>
    var BB_CATALOGS = @json($catalogsForJs);

    document.addEventListener('DOMContentLoaded', function () {
        var modal = document.getElementById('catalogModal');

        modal.addEventListener('show.bs.modal', function (event) {
            var trigger  = event.relatedTarget;
            var catalogId = trigger.closest('[data-catalog-id]')
                            ?.getAttribute('data-catalog-id');
            var cat = BB_CATALOGS[catalogId];
            if (!cat) return;

            // Header
            var storeEl = document.getElementById('catalogModalStore');
            storeEl.textContent = cat.store || '';
            storeEl.style.display = cat.store ? '' : 'none';

            document.getElementById('catalogModalTitle').textContent = cat.title;

            // Meta badges
            var metaEl = document.getElementById('catalogModalMeta');
            metaEl.innerHTML = '';
            if (cat.discount > 0) {
                metaEl.innerHTML += '<span class="badge" style="background:rgba(255,255,255,.25);color:#fff;">'
                    + cat.discount + '% ' + @json(__('Special Discount'))
                    + '</span>';
            }
            metaEl.innerHTML += '<span class="badge" style="background:rgba(255,255,255,.2);color:#fff;">'
                + cat.pdfs.length + ' ' + (cat.pdfs.length === 1 ? @json(__('Catalogue')) : @json(__('Catalogues')))
                + '</span>';

            // Description
            var descEl = document.getElementById('catalogModalDesc');
            if (cat.description) {
                descEl.textContent = cat.description;
                descEl.style.display = '';
            } else {
                descEl.style.display = 'none';
            }

            // PDF list header
            document.getElementById('catalogModalPdfHeader').textContent =
                @json(__('Available Catalogues')) + ' (' + cat.pdfs.length + ')';

            // PDF rows
            var listEl = document.getElementById('catalogModalPdfList');
            listEl.innerHTML = '';

            if (cat.pdfs.length === 0) {
                listEl.innerHTML = '<li style="padding:16px 12px;color:#9ca3af;font-size:.88rem;">'
                    + @json(__('No PDFs available.'))
                    + '</li>';
            } else {
                cat.pdfs.forEach(function (pdf, idx) {
                    listEl.innerHTML += `
                        <li class="bb-pdf-item">
                            <div class="bb-pdf-item-icon">
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M14 2H6C4.89 2 4 2.89 4 4V20C4 21.11 4.89 22 6 22H18C19.11 22 20 21.11 20 20V8L14 2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M14 2V8H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M9 13H15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    <path d="M9 17H13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <span class="bb-pdf-item-title">${escHtml(pdf.title)}</span>
                            <a href="${escHtml(pdf.view_url)}" target="_blank" rel="noopener" class="bb-pdf-view-btn">
                                <svg width="13" height="13" viewBox="0 0 20 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.99938 5.64111C8.66938 5.64111 7.58838 6.72311 7.58838 8.05311C7.58838 9.38211 8.66938 10.4631 9.99938 10.4631C11.3294 10.4631 12.4114 9.38211 12.4114 8.05311C12.4114 6.72311 11.3294 5.64111 9.99938 5.64111ZM9.99938 11.9631C7.84238 11.9631 6.08838 10.2091 6.08838 8.05311C6.08838 5.89611 7.84238 4.14111 9.99938 4.14111C12.1564 4.14111 13.9114 5.89611 13.9114 8.05311C13.9114 10.2091 12.1564 11.9631 9.99938 11.9631Z" fill="currentColor"/><path fill-rule="evenodd" clip-rule="evenodd" d="M1.56975 8.05226C3.42975 12.1613 6.56275 14.6043 9.99975 14.6053C13.4368 14.6043 16.5697 12.1613 18.4298 8.05226C16.5697 3.94426 13.4368 1.50126 9.99975 1.50026C6.56375 1.50126 3.42975 3.94426 1.56975 8.05226Z" fill="currentColor"/></svg>
                                ${@json(__('View'))}
                            </a>
                        </li>`;
                });
            }

            // Contact buttons
            var contactEl = document.getElementById('catalogModalContact');
            var contactHtml = '';
            if (cat.contact) {
                contactHtml += `<a href="tel:${escHtml(cat.contact)}" class="bb-contact-call">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.47 9.81a19.79 19.79 0 01-3.07-8.68A2 2 0 012.38 1h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 8.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    ${@json(__('Call'))} ${escHtml(cat.contact)}
                </a>`;
            }
            if (cat.whatsapp) {
                var waDigits = cat.whatsapp.replace(/\D/g, '');
                contactHtml += `<a href="https://wa.me/${waDigits}" target="_blank" rel="noopener" class="bb-contact-wa">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.464 3.488z"/></svg>
                    ${@json(__('WhatsApp'))}
                </a>`;
            }
            if (contactHtml) {
                contactEl.innerHTML = contactHtml;
                contactEl.style.display = '';
            } else {
                contactEl.style.display = 'none';
            }
        });
    });

    function escHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }
</script>
