@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <form id="b2b-catalog-form" action="{{ route('marketplace.b2b-catalogs.update', $catalog->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">{{ __('Edit B2B Catalog: :title', ['title' => $catalog->title]) }}</h4>
            </div>
            <div class="card-body">

                {{-- Title --}}
                <div class="mb-3">
                    <label for="title" class="form-label">{{ __('Title') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $catalog->title) }}" required>
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Store --}}
                <div class="mb-3">
                    <label for="store_id" class="form-label">{{ __('Assign to Store') }}</label>
                    <select class="form-select @error('store_id') is-invalid @enderror" id="store_id" name="store_id">
                        <option value="">— {{ __('No store (global catalogue)') }} —</option>
                        @foreach ($stores as $store)
                            <option value="{{ $store->id }}" {{ old('store_id', $catalog->store_id) == $store->id ? 'selected' : '' }}>
                                {{ $store->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('store_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Description --}}
                <div class="mb-3">
                    <label for="description" class="form-label">{{ __('Description') }}</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $catalog->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Discount --}}
                <div class="mb-3">
                    <label for="discount_percentage" class="form-label">{{ __('Discount %') }}</label>
                    <input type="number" class="form-control @error('discount_percentage') is-invalid @enderror" id="discount_percentage" name="discount_percentage" value="{{ old('discount_percentage', $catalog->discount_percentage) }}" min="0" max="100" step="0.01" placeholder="e.g. 15">
                    <small class="text-muted">{{ __('Leave empty for no discount tag.') }}</small>
                    @error('discount_percentage') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Contact numbers --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="contact_number" class="form-label">{{ __('Contact Number') }}</label>
                        <input type="text" class="form-control @error('contact_number') is-invalid @enderror" id="contact_number" name="contact_number" value="{{ old('contact_number', $catalog->contact_number) }}" placeholder="+91XXXXXXXXXX">
                        @error('contact_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="whatsapp_number" class="form-label">{{ __('WhatsApp Number') }}</label>
                        <input type="text" class="form-control @error('whatsapp_number') is-invalid @enderror" id="whatsapp_number" name="whatsapp_number" value="{{ old('whatsapp_number', $catalog->whatsapp_number) }}" placeholder="+91XXXXXXXXXX">
                        @error('whatsapp_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Existing PDFs --}}
                @if ($catalog->pdfs->isNotEmpty())
                    <div class="mb-4">
                        <label class="form-label">{{ __('Current PDFs') }}</label>
                        <div id="existing-pdfs">
                            @foreach ($catalog->pdfs as $pdf)
                                <div class="existing-pdf-row d-flex align-items-center gap-3 border rounded p-2 mb-2 bg-light" id="pdf-row-{{ $pdf->id }}">
                                    <i class="ti ti-file-type-pdf text-danger fs-4"></i>
                                    <div class="flex-grow-1">
                                        <strong>{{ $pdf->title }}</strong>
                                        <br>
                                        <a href="{{ route('marketplace.b2b-catalogs.pdfs.view', [$catalog->id, $pdf->id]) }}" target="_blank" class="small text-primary">
                                            <i class="ti ti-eye"></i> {{ __('View PDF') }}
                                        </a>
                                    </div>
                                    <button type="button"
                                        class="btn btn-sm btn-outline-danger delete-pdf-btn"
                                        data-url="{{ route('marketplace.b2b-catalogs.pdfs.destroy', [$catalog->id, $pdf->id]) }}"
                                        data-row="pdf-row-{{ $pdf->id }}"
                                        title="{{ __('Delete this PDF') }}">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Legacy single pdf_path (backward compat) --}}
                @if ($catalog->pdf_path && $catalog->pdfs->isEmpty())
                    <div class="mb-3">
                        <label class="form-label">{{ __('Current PDF (legacy)') }}</label>
                        <div>
                            <a href="{{ route('marketplace.b2b-catalogs.view-pdf', $catalog->id) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                <i class="ti ti-eye"></i> {{ __('View') }}: {{ basename($catalog->pdf_path) }}
                            </a>
                        </div>
                    </div>
                @endif

                {{-- Add new PDFs --}}
                <div class="mb-3">
                    <label class="form-label">{{ __('Add New PDFs') }}</label>
                    <div id="new-pdf-rows"></div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-1" id="add-new-pdf-row">
                        <i class="ti ti-plus"></i> {{ __('Add PDF') }}
                    </button>
                </div>

                <button type="submit" class="btn btn-primary" id="b2b-submit-btn">{{ __('Update') }}</button>
                <a href="{{ route('marketplace.b2b-catalogs.index') }}" class="btn btn-secondary ms-1">{{ __('Cancel') }}</a>
            </div>
        </div>
    </form>

    @include('plugins/marketplace::b2b-catalogs.partials.upload-progress', [
        'redirectUrl' => route('marketplace.b2b-catalogs.index'),
        'actionUrl'   => route('marketplace.b2b-catalogs.update', $catalog->id),
    ])

    @push('footer')
        <script>
            (function () {
                // Add new PDF rows
                const newContainer = document.getElementById('new-pdf-rows');
                const addNewBtn    = document.getElementById('add-new-pdf-row');
                let rowIndex = 0;

                function buildNewRow(idx) {
                    const div = document.createElement('div');
                    div.className = 'pdf-row border rounded p-3 mb-2 bg-light';
                    div.innerHTML = `
                        <div class="row g-2 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label small mb-1">{{ __('PDF Title') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="new_pdf_titles[${idx}]" placeholder="{{ __('PDF Title') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small mb-1">{{ __('PDF File') }} <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" name="pdf_files[${idx}]" accept=".pdf" required>
                            </div>
                            <div class="col-md-1 text-end">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-new-row" title="{{ __('Remove') }}">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </div>
                        </div>`;
                    return div;
                }

                addNewBtn.addEventListener('click', function () {
                    newContainer.appendChild(buildNewRow(rowIndex++));
                });

                newContainer.addEventListener('click', function (e) {
                    if (e.target.closest('.remove-new-row')) {
                        e.target.closest('.pdf-row').remove();
                    }
                });

                // Delete existing PDF via AJAX
                document.querySelectorAll('.delete-pdf-btn').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        if (! confirm('{{ __("Delete this PDF?") }}')) return;
                        const url = btn.dataset.url;
                        const rowId = btn.dataset.row;
                        fetch(url, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                                'Accept': 'application/json',
                            },
                        })
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            document.getElementById(rowId).remove();
                        })
                        .catch(function () {
                            alert('{{ __("Failed to delete PDF.") }}');
                        });
                    });
                });
            })();
        </script>
    @endpush
@endsection
