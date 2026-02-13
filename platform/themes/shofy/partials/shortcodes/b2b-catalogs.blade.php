@php
    $b2bCallNumber = MarketplaceHelper::getSetting('b2b_contact_call_number', '');
    $b2bWhatsappNumber = MarketplaceHelper::getSetting('b2b_contact_whatsapp_number', '');
@endphp

<section class="tp-b2b-catalogs-area pt-70 pb-70"
    @if ($shortcode->background_color)
        style="background-color: {{ $shortcode->background_color }} !important;"
    @endif
>
    <div class="container">
        @if ($shortcode->title)
            <div class="mb-40">
                {!! Theme::partial('section-title', compact('shortcode')) !!}
            </div>
        @endif

        @if ($catalogs->isEmpty())
            <div class="alert alert-info text-center">
                {{ __('No B2B catalogs available yet. Please check back later.') }}
            </div>
        @else
        <div class="row">
            @foreach ($catalogs as $catalog)
                <div class="col-lg-4 col-md-6 mb-30">
                    <div class="card h-100 border shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-center mb-3">
                                <div class="b2b-pdf-icon me-3" style="color: #e74c3c; font-size: 40px;">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                        <polyline points="14 2 14 8 20 8"/>
                                        <line x1="16" y1="13" x2="8" y2="13"/>
                                        <line x1="16" y1="17" x2="8" y2="17"/>
                                        <polyline points="10 9 9 9 8 9"/>
                                    </svg>
                                </div>
                                <div>
                                    <h5 class="card-title mb-1">{{ $catalog->title }}</h5>
                                    <small class="text-muted">{{ $catalog->created_at->format('d M Y') }}</small>
                                </div>
                            </div>
                            @if ($catalog->description)
                                <p class="card-text text-muted flex-grow-1">{{ Str::limit($catalog->description, 150) }}</p>
                            @endif
                            <div class="d-flex gap-2 mt-auto">
                                <a href="{{ Storage::disk('public')->url($catalog->pdf_path) }}" target="_blank" class="btn btn-primary flex-grow-1" style="color: #fff !important;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px;">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                        <polyline points="7 10 12 15 17 10"/>
                                        <line x1="12" y1="15" x2="12" y2="3"/>
                                    </svg>
                                    {{ __('Download PDF') }}
                                </a>
                                @if ($b2bCallNumber)
                                    <a href="tel:{{ $b2bCallNumber }}" class="btn btn-outline-success" title="{{ __('Call') }}" style="min-width: 44px;">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                        </svg>
                                    </a>
                                @endif
                                @if ($b2bWhatsappNumber)
                                    <a href="https://wa.me/{{ $b2bWhatsappNumber }}?text={{ urlencode(__('Hi, I am interested in the B2B catalog: ') . $catalog->title) }}" target="_blank" class="btn btn-outline-success" title="{{ __('WhatsApp') }}" style="min-width: 44px; border-color: #25D366; color: #25D366;">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @endif
    </div>
</section>
