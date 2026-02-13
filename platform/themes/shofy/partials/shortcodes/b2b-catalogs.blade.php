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
                            <a href="{{ Storage::disk('public')->url($catalog->pdf_path) }}" target="_blank" class="btn btn-primary mt-auto" style="color: #fff !important;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px;">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                    <polyline points="7 10 12 15 17 10"/>
                                    <line x1="12" y1="15" x2="12" y2="3"/>
                                </svg>
                                {{ __('Download PDF') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @endif
    </div>
</section>
