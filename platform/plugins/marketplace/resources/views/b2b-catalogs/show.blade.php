@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    @php
        use Botble\Marketplace\Facades\MarketplaceHelper;
        $contactNumber  = $catalog->contact_number  ?: MarketplaceHelper::getSetting('b2b_contact_call_number', '');
        $whatsappNumber = $catalog->whatsapp_number ?: MarketplaceHelper::getSetting('b2b_contact_whatsapp_number', '');
        $whatsappDigits = preg_replace('/\D/', '', (string) $whatsappNumber);
        $isSheet        = $catalog->type === 'google_sheet';
    @endphp

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="card-title mb-1">{{ $catalog->title }}</h4>
                <span class="badge" style="background-color:{{ $isSheet ? '#17a2b8' : '#6c757d' }};color:#fff;">
                    {{ $isSheet ? __('Google Sheet') : __('PDF') }}
                </span>
                @if ($catalog->discount_percentage > 0)
                    <span class="badge bg-danger">
                        {{ rtrim(rtrim(number_format($catalog->discount_percentage, 2), '0'), '.') }}% OFF
                    </span>
                @endif
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ route('marketplace.b2b-catalogs.edit', $catalog->id) }}" class="btn btn-sm btn-primary">
                    <i class="ti ti-edit"></i> {{ __('Edit') }}
                </a>
                <a href="{{ route('marketplace.b2b-catalogs.index') }}" class="btn btn-sm btn-secondary">
                    {{ __('Back to Catalogs') }}
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <strong>{{ __('Store') }}:</strong>
                    {{ $catalog->store?->id ? $catalog->store->name : __('— (global catalogue)') }}
                </div>
                <div class="col-md-6">
                    <strong>{{ __('Uploaded By') }}:</strong>
                    {{ ucfirst($catalog->uploaded_by_type) }}
                </div>
            </div>

            @if ($catalog->description)
                <div class="mb-4">
                    <strong>{{ __('Description') }}:</strong>
                    <p class="mb-0">{{ $catalog->description }}</p>
                </div>
            @endif

            <div class="mb-4 d-flex align-items-center gap-2 flex-wrap">
                @if ($contactNumber)
                    <a href="tel:{{ $contactNumber }}" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1">
                        <i class="ti ti-phone"></i> {{ __('Call') }}: {{ $contactNumber }}
                    </a>
                @endif
                @if ($whatsappDigits)
                    <a
                        href="https://wa.me/{{ $whatsappDigits }}"
                        target="_blank"
                        rel="noopener"
                        class="btn btn-sm d-flex align-items-center gap-1"
                        style="background:#25D366;color:#fff;border-color:#25D366;"
                    >
                        <i class="ti ti-brand-whatsapp"></i> {{ __('WhatsApp') }}: {{ $whatsappNumber }}
                    </a>
                @endif
                @if (! $contactNumber && ! $whatsappDigits)
                    <span class="text-muted">{{ __('No contact number set for this catalogue (and no global fallback configured).') }}</span>
                @endif
            </div>

            <hr>

            @if ($isSheet)
                <div class="mt-4">
                    <h5>{{ __('Google Sheet') }}</h5>
                    @if ($catalog->google_sheet_url)
                        <a href="{{ $catalog->google_sheet_url }}" target="_blank" rel="noopener" class="btn btn-success">
                            <i class="ti ti-file-spreadsheet"></i> {{ __('Open Google Sheet') }}
                        </a>
                        <div class="text-muted small mt-2" style="word-break:break-all;">{{ $catalog->google_sheet_url }}</div>
                    @else
                        <span class="text-muted">{{ __('No Google Sheet link set.') }}</span>
                    @endif
                </div>
            @else
                <div class="mt-4">
                    <h5>{{ __('PDFs') }} ({{ $catalog->pdfs->count() ?: ($catalog->pdf_path ? 1 : 0) }})</h5>

                    @if ($catalog->pdfs->isNotEmpty())
                        <div class="list-group">
                            @foreach ($catalog->pdfs as $index => $pdf)
                                <div class="list-group-item d-flex align-items-center justify-content-between gap-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="ti ti-file-type-pdf text-danger fs-4"></i>
                                        <span>{{ __('PDF :number', ['number' => $index + 1]) }}: <strong>{{ $pdf->title }}</strong></span>
                                    </div>
                                    <a
                                        href="{{ route('marketplace.b2b-catalogs.pdfs.stream', [$catalog->id, $pdf->id]) }}"
                                        target="_blank"
                                        rel="noopener"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        <i class="ti ti-eye"></i> {{ __('View') }}
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @elseif ($catalog->pdf_path)
                        {{-- Legacy single pdf_path (backward compat) --}}
                        <div class="list-group">
                            <div class="list-group-item d-flex align-items-center justify-content-between gap-3">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="ti ti-file-type-pdf text-danger fs-4"></i>
                                    <span>{{ basename($catalog->pdf_path) }}</span>
                                </div>
                                <a
                                    href="{{ route('marketplace.b2b-catalogs.stream-pdf', $catalog->id) }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="btn btn-sm btn-outline-primary"
                                >
                                    <i class="ti ti-eye"></i> {{ __('View') }}
                                </a>
                            </div>
                        </div>
                    @else
                        <span class="text-muted">{{ __('No PDFs uploaded yet.') }}</span>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection
