@php
    Theme::layout('full-width');
    Theme::set('pageTitle', __('B2B Catalogues'));
    Theme::breadcrumb()->add(__('Home'), route('public.index'))->add(__('B2B Catalogues'));
@endphp

<div class="tp-page-area pt-30 pb-120">
    <div class="container">

        {{-- Top bar: result count + search --}}
        <div class="tp-shop-top mb-45">
            <div class="tp-shop-top-left d-flex flex-wrap gap-3 justify-content-between align-items-center">
                <div class="tp-shop-top-result">
                    @if ($catalogs->total())
                        <p>{{ __('Showing :from–:to of :total catalogues', ['from' => $catalogs->firstItem(), 'to' => $catalogs->lastItem(), 'total' => $catalogs->total()]) }}</p>
                    @else
                        <p>{{ __('No catalogues found') }}</p>
                    @endif
                </div>

                <form action="{{ route('public.b2b-catalogs.index') }}" method="get">
                    <div class="tp-sidebar-search-input">
                        <input type="search" name="q" placeholder="{{ __('Search catalogues...') }}" value="{{ old('q', request('q')) }}">
                        <button type="submit" title="{{ __('Search') }}">
                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M8.11111 15.2222C12.0385 15.2222 15.2222 12.0385 15.2222 8.11111C15.2222 4.18375 12.0385 1 8.11111 1C4.18375 1 1 4.18375 1 8.11111C1 12.0385 4.18375 15.2222 8.11111 15.2222Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M16.9995 17L13.1328 13.1333" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if ($catalogs->isEmpty())
            <div class="text-center py-5">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="opacity:.3;margin-bottom:12px">
                    <path d="M14 2H6C4.89 2 4 2.89 4 4V20C4 21.11 4.89 22 6 22H18C19.11 22 20 21.11 20 20V8L14 2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M14 2V8H20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <p class="text-muted">{{ __('No catalogues available yet.') }}</p>
            </div>
        @else
            <div class="row g-4 mb-40">
                @foreach ($catalogs as $catalog)
                    @php
                        $pdfCount        = $catalog->pdfs->count();
                        $totalPdfs       = $pdfCount ?: ($catalog->pdf_path ? 1 : 0);
                        $discount        = (float) $catalog->discount_percentage;
                        $isSheet         = ($catalog->type ?? 'pdf') === 'google_sheet';
                        $firstThumbnail  = $isSheet ? null : $catalog->pdfs->first()?->thumbnail_path;
                    @endphp

                    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                        <div class="card bb-catalog-card h-100" style="border:1px solid var(--tp-border-primary,#eaebed);border-radius:8px;overflow:hidden;">

                            {{-- Card header: PDF page preview, Excel/Sheet icon, or fallback PDF icon --}}
                            <div class="bb-catalog-cover" style="background:var(--tp-grey-1,#f6f7f9);position:relative;{{ $firstThumbnail ? '' : 'padding:28px 20px 20px;' }}text-align:center;border-bottom:1px solid var(--tp-border-primary,#eaebed);">

                                @if ($discount > 0)
                                    <span style="position:absolute;top:12px;right:12px;z-index:2;background:var(--tp-pink-1,#fd4b6b);color:#fff;font-size:.7rem;font-weight:700;padding:3px 9px;border-radius:20px;">
                                        {{ $discount }}% OFF
                                    </span>
                                @endif

                                @if ($firstThumbnail)
                                    {{-- Real preview of the first PDF's first page, like a WhatsApp document preview --}}
                                    <img
                                        src="{{ Storage::disk('public')->url($firstThumbnail) }}"
                                        alt="{{ $catalog->title }}"
                                        style="width:100%;aspect-ratio:4/3;object-fit:cover;object-position:top;display:block;"
                                        loading="lazy"
                                    >
                                    <span style="position:absolute;left:10px;bottom:10px;z-index:2;display:inline-flex;align-items:center;gap:4px;background:#e02424;color:#fff;font-size:.68rem;font-weight:700;padding:3px 8px;border-radius:4px;box-shadow:0 2px 6px rgba(0,0,0,.25);">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14 2H6C4.89 2 4 2.89 4 4V20C4 21.11 4.89 22 6 22H18C19.11 22 20 21.11 20 20V8L14 2Z" stroke="currentColor" stroke-width="2"/></svg>
                                        PDF
                                    </span>
                                    <span style="position:absolute;right:10px;bottom:10px;z-index:2;background:rgba(0,0,0,.6);color:#fff;font-size:.68rem;font-weight:600;padding:3px 8px;border-radius:4px;">
                                        {{ $totalPdfs }} {{ $totalPdfs === 1 ? __('PDF') : __('PDFs') }}
                                    </span>
                                @elseif ($isSheet)
                                    {{-- Glossy "3D" app-icon-style spreadsheet graphic --}}
                                    <svg width="56" height="56" viewBox="0 0 56 56" xmlns="http://www.w3.org/2000/svg" style="margin-bottom:10px;filter:drop-shadow(0 6px 10px rgba(13,92,44,.35));">
                                        <defs>
                                            <linearGradient id="bbExcelBody{{ $catalog->id }}" x1="4" y1="4" x2="52" y2="52" gradientUnits="userSpaceOnUse">
                                                <stop offset="0" stop-color="#22a556"/>
                                                <stop offset="1" stop-color="#0d5c2c"/>
                                            </linearGradient>
                                            <linearGradient id="bbExcelShine{{ $catalog->id }}" x1="28" y1="2" x2="28" y2="30" gradientUnits="userSpaceOnUse">
                                                <stop offset="0" stop-color="#ffffff" stop-opacity=".35"/>
                                                <stop offset="1" stop-color="#ffffff" stop-opacity="0"/>
                                            </linearGradient>
                                        </defs>
                                        <rect x="2" y="2" width="52" height="52" rx="13" fill="url(#bbExcelBody{{ $catalog->id }})"/>
                                        <rect x="2" y="2" width="52" height="52" rx="13" fill="url(#bbExcelShine{{ $catalog->id }})"/>
                                        <rect x="14" y="12" width="28" height="32" rx="2.5" fill="#ffffff"/>
                                        <rect x="14" y="12" width="28" height="8" rx="2.5" fill="#dcf1e3"/>
                                        <path d="M14 20H42M14 27.3H42M14 34.7H42M22.6 12V44M31.2 12V44" stroke="#178a44" stroke-width="1.1"/>
                                    </svg>

                                    <div style="font-size:.78rem;color:var(--tp-text-1,#767a7d);font-weight:600;">
                                        {{ __('Google Sheet') }}
                                    </div>
                                @else
                                    <svg width="44" height="44" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="color:var(--tp-theme-primary,#821f40);opacity:.7;margin-bottom:10px;">
                                        <path d="M14 2H6C4.89 2 4 2.89 4 4V20C4 21.11 4.89 22 6 22H18C19.11 22 20 21.11 20 20V8L14 2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M14 2V8H20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M9 13H15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                        <path d="M9 17H13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>

                                    <div style="font-size:.78rem;color:var(--tp-text-1,#767a7d);font-weight:600;">
                                        {{ $totalPdfs }} {{ $totalPdfs === 1 ? __('PDF') : __('PDFs') }}
                                    </div>
                                @endif
                            </div>

                            {{-- Card body --}}
                            <div class="card-body d-flex flex-column" style="padding:16px 18px 18px;">
                                @if ($catalog->store?->id)
                                    <div style="font-size:.72rem;font-weight:700;color:var(--tp-theme-primary,#821f40);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">
                                        {{ $catalog->store->name }}
                                    </div>
                                @endif

                                <h6 style="font-size:.95rem;font-weight:700;color:var(--tp-heading-primary,#010f1c);margin-bottom:6px;line-height:1.4;">
                                    {{ $catalog->title }}
                                </h6>

                                @if ($catalog->description)
                                    <p style="font-size:.82rem;color:var(--tp-text-body,#55585b);margin-bottom:14px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;flex:1;">
                                        {{ $catalog->description }}
                                    </p>
                                @else
                                    <div style="flex:1;"></div>
                                @endif

                                <button
                                    type="button"
                                    class="tp-btn w-100"
                                    style="font-size:.82rem;padding:9px 16px;"
                                    data-bs-toggle="modal"
                                    data-bs-target="#catalogModal"
                                    data-catalog-id="{{ $catalog->id }}"
                                >
                                    {{ __('Browse Catalogues') }}
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{ $catalogs->withQueryString()->links(Theme::getThemeNamespace('partials.pagination')) }}
        @endif
    </div>
</div>

{{-- ── Modal ───────────────────────────────────────────────── --}}
<div class="modal fade" id="catalogModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:520px;">
        <div class="modal-content" style="border-radius:8px;border:1px solid var(--tp-border-primary,#eaebed);box-shadow:0 8px 32px rgba(0,0,0,.1);">

            <div class="modal-header" style="border-bottom:1px solid var(--tp-border-primary,#eaebed);padding:18px 22px;">
                <div>
                    <div id="modalStoreName" style="font-size:.72rem;font-weight:700;color:var(--tp-theme-primary,#821f40);text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px;display:none;"></div>
                    <h5 class="modal-title" id="modalTitle" style="font-size:1rem;font-weight:700;color:var(--tp-heading-primary,#010f1c);margin:0;"></h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>

            <div class="modal-body p-0">
                {{-- Description + discount --}}
                <div id="modalMeta" style="padding:14px 22px 0;display:none;">
                    <div id="modalDesc" style="font-size:.84rem;color:var(--tp-text-body,#55585b);margin-bottom:8px;display:none;"></div>
                    <span id="modalDiscount" style="display:none;font-size:.75rem;font-weight:700;background:#fff0f3;color:var(--tp-pink-1,#fd4b6b);border:1px solid #ffd6de;padding:3px 10px;border-radius:20px;"></span>
                </div>

                {{-- PDF instruction message --}}
                <div id="modalPdfInstruction" style="display:none;margin:14px 22px 0;padding:10px 14px;background:var(--tp-grey-1,#f6f7f9);border-radius:6px;font-size:.82rem;color:var(--tp-text-body,#55585b);">
                    {{ __('Select the PDF(s) you want to enquire about, then tap WhatsApp below to send your query.') }}
                </div>

                {{-- PDF list header --}}
                <div id="modalPdfHeader" style="padding:16px 22px 8px;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--tp-text-1,#767a7d);border-bottom:1px solid var(--tp-border-primary,#eaebed);"></div>

                {{-- PDF rows --}}
                <ul id="modalPdfList" style="list-style:none;margin:0;padding:8px 12px 12px;"></ul>

                {{-- Google Sheet link --}}
                <div id="modalSheetSection" style="display:none;padding:16px 22px 4px;">
                    <a id="modalSheetLink" href="#" target="_blank" rel="noopener" class="tp-btn w-100" style="font-size:.85rem;padding:10px 16px;display:inline-flex;align-items:center;justify-content:center;gap:8px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14 2H6C4.89 2 4 2.89 4 4V20C4 21.11 4.89 22 6 22H18C19.11 22 20 21.11 20 20V8L14 2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 2V8H20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 13H16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M8 17H16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M11 13V19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                        {{ __('Open Google Sheet') }}
                    </a>
                    <div style="font-size:.78rem;color:var(--tp-text-1,#767a7d);margin-top:8px;text-align:center;">
                        {{ __('Have a question about this catalogue? Contact us on WhatsApp below.') }}
                    </div>
                </div>
            </div>

            {{-- Contact buttons --}}
            <div id="modalContact" class="modal-footer" style="display:none!important;border-top:1px solid var(--tp-border-primary,#eaebed);padding:12px 22px;gap:8px;flex-wrap:wrap;justify-content:flex-start;"></div>
        </div>
    </div>
</div>

@php
    $catalogsForJs = $catalogs->map(function ($c) {
        return [
            'id'          => $c->id,
            'title'       => $c->title,
            'type'        => $c->type ?? 'pdf',
            'sheetUrl'    => $c->google_sheet_url,
            'store'       => $c->store?->id ? $c->store->name : null,
            'description' => $c->description,
            'discount'    => (float) $c->discount_percentage,
            'contact'     => $c->contact_number,
            'whatsapp'    => $c->whatsapp_number,
            'pdfs'        => $c->pdfs->map(function ($p) use ($c) {
                return [
                    'title'    => $p->title,
                    'view_url' => route('public.b2b-catalogs.pdf.stream', [$c->id, $p->id]),
                ];
            })->values()->all(),
        ];
    })->keyBy('id')->all();
@endphp

<script>
var BB_CATALOGS = @json($catalogsForJs);

document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('catalogModal');
    modal.addEventListener('show.bs.modal', function (e) {
        var btn       = e.relatedTarget;
        var id        = btn ? btn.getAttribute('data-catalog-id') : null;
        var cat       = BB_CATALOGS[id];
        if (!cat) return;

        // Store name
        var storeEl = document.getElementById('modalStoreName');
        if (cat.store) { storeEl.textContent = cat.store; storeEl.style.display = ''; }
        else           { storeEl.style.display = 'none'; }

        document.getElementById('modalTitle').textContent = cat.title;

        // Description + discount
        var metaEl    = document.getElementById('modalMeta');
        var descEl    = document.getElementById('modalDesc');
        var discountEl = document.getElementById('modalDiscount');
        var hasMeta   = false;
        if (cat.description) { descEl.textContent = cat.description; descEl.style.display = ''; hasMeta = true; }
        else                  { descEl.style.display = 'none'; }
        if (cat.discount > 0) { discountEl.textContent = cat.discount + '% ' + @json(__('Special Discount')); discountEl.style.display = 'inline'; hasMeta = true; }
        else                  { discountEl.style.display = 'none'; }
        metaEl.style.display = hasMeta ? '' : 'none';

        var instructionEl = document.getElementById('modalPdfInstruction');
        var headerEl      = document.getElementById('modalPdfHeader');
        var list          = document.getElementById('modalPdfList');
        var sheetSection  = document.getElementById('modalSheetSection');

        if (cat.type === 'google_sheet') {
            // Google Sheet catalogue: just the sheet link + contact, no PDF picker.
            instructionEl.style.display = 'none';
            headerEl.style.display = 'none';
            list.style.display = 'none';
            list.innerHTML = '';

            document.getElementById('modalSheetLink').href = cat.sheetUrl || '#';
            sheetSection.style.display = '';
        } else {
            // PDF catalogue: show the enquiry instruction + selectable PDF list.
            sheetSection.style.display = 'none';
            headerEl.style.display = '';
            list.style.display = '';

            headerEl.textContent = @json(__('Available Catalogues')) + ' (' + cat.pdfs.length + ')';

            list.innerHTML = '';
            if (!cat.pdfs.length) {
                instructionEl.style.display = 'none';
                list.innerHTML = '<li style="padding:12px 10px;font-size:.84rem;color:var(--tp-text-1,#767a7d);">' + @json(__('No PDFs available.')) + '</li>';
            } else {
                instructionEl.style.display = '';
                cat.pdfs.forEach(function (pdf, idx) {
                    var li = document.createElement('li');
                    li.style.cssText = 'display:flex;align-items:center;gap:12px;padding:10px 10px;border-radius:6px;';
                    li.innerHTML =
                        '<input type="checkbox" class="bb-catalog-pdf-check" data-pdf-number="' + (idx + 1) + '" data-pdf-title="' + escHtml(pdf.title) + '" style="flex-shrink:0;width:16px;height:16px;cursor:pointer;">'
                        + '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0;color:var(--tp-theme-primary,#821f40)"><path d="M14 2H6C4.89 2 4 2.89 4 4V20C4 21.11 4.89 22 6 22H18C19.11 22 20 21.11 20 20V8L14 2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 2V8H20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
                        + '<span style="flex:1;font-size:.88rem;font-weight:600;color:var(--tp-heading-primary,#010f1c);">' + @json(__('PDF')) + ' ' + (idx + 1) + ': ' + escHtml(pdf.title) + '</span>'
                        + '<a href="' + escHtml(pdf.view_url) + '" target="_blank" rel="noopener" class="tp-btn" style="font-size:.75rem;padding:6px 14px;white-space:nowrap;">' + @json(__('View')) + '</a>';
                    li.addEventListener('mouseenter', function () { this.style.background = 'var(--tp-grey-1,#f6f7f9)'; });
                    li.addEventListener('mouseleave', function () { this.style.background = ''; });
                    list.appendChild(li);
                });
            }
        }

        // Contact
        var contactEl = document.getElementById('modalContact');
        var contactHtml = '';
        if (cat.contact) {
            contactHtml += '<a href="tel:' + escHtml(cat.contact) + '" class="tp-btn-border" style="font-size:.8rem;padding:7px 16px;display:inline-flex;align-items:center;gap:6px;text-decoration:none;">'
                + '<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.47 9.81 19.79 19.79 0 01.4 1.13 2 2 0 012.38 1h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 8.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>'
                + @json(__('Call')) + ': ' + escHtml(cat.contact) + '</a>';
        }
        if (cat.whatsapp) {
            contactHtml += '<a id="bbCatalogWaLink" href="#" target="_blank" rel="noopener" style="font-size:.8rem;padding:7px 16px;display:inline-flex;align-items:center;gap:6px;text-decoration:none;background:#25D366;color:#fff;border-radius:4px;font-weight:600;">'
                + '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.464 3.488z"/></svg>'
                + 'WhatsApp</a>';
        }
        if (contactHtml) {
            contactEl.innerHTML = contactHtml;
            contactEl.style.cssText = 'display:flex!important;border-top:1px solid var(--tp-border-primary,#eaebed);padding:12px 22px;gap:8px;flex-wrap:wrap;';
        } else {
            contactEl.style.display = 'none';
        }

        // Build the WhatsApp message from whichever PDFs are checked, and keep it
        // updated live as the visitor (un)checks rows.
        if (cat.whatsapp) {
            var waDigits = cat.whatsapp.replace(/\D/g, '');
            var waLink = document.getElementById('bbCatalogWaLink');

            var rebuildWaLink = function () {
                var checked = Array.prototype.slice.call(list.querySelectorAll('.bb-catalog-pdf-check:checked'));
                var msg = @json(__('Hi, I\'m interested in catalogue')) + ' "' + cat.title + '" (' + @json(__('Catalogue')) + ' #' + cat.id + ').';

                if (checked.length) {
                    msg += ' ' + @json(__('PDFs')) + ': ' + checked.map(function (cb) {
                        return @json(__('PDF')) + ' ' + cb.getAttribute('data-pdf-number') + ' (' + cb.getAttribute('data-pdf-title') + ')';
                    }).join(', ');
                }

                waLink.href = 'https://wa.me/' + waDigits + '?text=' + encodeURIComponent(msg);
            };

            list.addEventListener('change', function (e) {
                if (e.target.classList.contains('bb-catalog-pdf-check')) rebuildWaLink();
            });

            rebuildWaLink();
        }
    });
});

function escHtml(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
