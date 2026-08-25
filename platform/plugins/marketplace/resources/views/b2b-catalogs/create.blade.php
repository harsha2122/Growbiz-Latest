@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <form id="b2b-catalog-form" action="{{ route('marketplace.b2b-catalogs.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">{{ __('Create B2B Catalog') }}</h4>
            </div>
            <div class="card-body">

                {{-- Title --}}
                <div class="mb-3">
                    <label for="title" class="form-label">{{ __('Title') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Store --}}
                <div class="mb-3">
                    <label for="store_id" class="form-label">{{ __('Assign to Store') }}</label>
                    <select class="form-select @error('store_id') is-invalid @enderror" id="store_id" name="store_id">
                        <option value="">— {{ __('No store (global catalogue)') }} —</option>
                        @foreach ($stores as $store)
                            <option value="{{ $store->id }}" {{ old('store_id') == $store->id ? 'selected' : '' }}>
                                {{ $store->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('store_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Description --}}
                <div class="mb-3">
                    <label for="description" class="form-label">{{ __('Description') }}</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Discount --}}
                <div class="mb-3">
                    <label for="discount_percentage" class="form-label">{{ __('Discount %') }}</label>
                    <input type="number" class="form-control @error('discount_percentage') is-invalid @enderror" id="discount_percentage" name="discount_percentage" value="{{ old('discount_percentage') }}" min="0" max="100" step="0.01" placeholder="e.g. 15">
                    <small class="text-muted">{{ __('Leave empty for no discount tag.') }}</small>
                    @error('discount_percentage') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Contact numbers --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="contact_number" class="form-label">{{ __('Contact Number') }}</label>
                        <input type="text" class="form-control @error('contact_number') is-invalid @enderror" id="contact_number" name="contact_number" value="{{ old('contact_number') }}" placeholder="+91XXXXXXXXXX">
                        @error('contact_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="whatsapp_number" class="form-label">{{ __('WhatsApp Number') }}</label>
                        <input type="text" class="form-control @error('whatsapp_number') is-invalid @enderror" id="whatsapp_number" name="whatsapp_number" value="{{ old('whatsapp_number') }}" placeholder="+91XXXXXXXXXX">
                        @error('whatsapp_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Catalog Type --}}
                <div class="mb-3">
                    <label class="form-label">{{ __('Catalog Type') }} <span class="text-danger">*</span></label>
                    <div class="d-flex gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="type" id="type_pdf" value="pdf" checked>
                            <label class="form-check-label" for="type_pdf">{{ __('PDF Catalog') }}</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="type" id="type_google_sheet" value="google_sheet">
                            <label class="form-check-label" for="type_google_sheet">{{ __('Google Sheet Link') }}</label>
                        </div>
                    </div>
                    @error('type') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                {{-- PDF Files --}}
                <div class="mb-3" id="pdf-type-section">
                    <label class="form-label">{{ __('PDF Files') }} <span class="text-danger">*</span></label>
                    <div id="pdf-rows">
                        <div class="pdf-row border rounded p-3 mb-2 bg-light">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label small mb-1">{{ __('PDF Title') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="pdf_titles[]" placeholder="{{ __('e.g. Product Catalogue 2026') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small mb-1">{{ __('PDF File') }} <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" name="pdf_files[]" accept=".pdf">
                                </div>
                                <div class="col-md-1 text-end">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-pdf-row" style="display:none;" title="{{ __('Remove') }}">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-1" id="add-pdf-row">
                        <i class="ti ti-plus"></i> {{ __('Add Another PDF') }}
                    </button>
                </div>

                {{-- Google Sheet URL --}}
                <div class="mb-3" id="google-sheet-type-section" style="display:none;">
                    <label for="google_sheet_url" class="form-label">{{ __('Google Sheet Link') }} <span class="text-danger">*</span></label>
                    <input type="url" class="form-control @error('google_sheet_url') is-invalid @enderror" id="google_sheet_url" name="google_sheet_url" value="{{ old('google_sheet_url') }}" placeholder="https://docs.google.com/spreadsheets/d/...">
                    <small class="text-muted">{{ __('Make sure the sheet\'s share setting is "Anyone with the link can view" so every visitor can open it.') }}</small>
                    @error('google_sheet_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn btn-primary" id="b2b-submit-btn">{{ __('Save') }}</button>
                <a href="{{ route('marketplace.b2b-catalogs.index') }}" class="btn btn-secondary ms-1">{{ __('Cancel') }}</a>
            </div>
        </div>
    </form>

    @include('plugins/marketplace::b2b-catalogs.partials.upload-progress', [
        'redirectUrl' => route('marketplace.b2b-catalogs.index'),
        'actionUrl'   => route('marketplace.b2b-catalogs.store'),
    ])

    @push('footer')
        <script>
            (function () {
                const pdfSection  = document.getElementById('pdf-type-section');
                const sheetSection = document.getElementById('google-sheet-type-section');
                const sheetInput  = document.getElementById('google_sheet_url');
                const typeRadios  = document.querySelectorAll('input[name="type"]');

                function toggleType() {
                    const isPdf = document.getElementById('type_pdf').checked;
                    pdfSection.style.display = isPdf ? '' : 'none';
                    sheetSection.style.display = isPdf ? 'none' : '';
                    // Disabled fields are excluded from the submitted form entirely (unlike
                    // merely hidden ones), so switching to Google Sheet mode can't leak an
                    // empty pdf_titles[]/pdf_files[] entry that fails "required" server-side.
                    pdfSection.querySelectorAll('input[name="pdf_titles[]"], input[name="pdf_files[]"]').forEach(function (el) {
                        el.required = isPdf;
                        el.disabled = ! isPdf;
                    });
                    sheetInput.required = ! isPdf;
                    sheetInput.disabled = isPdf;
                }

                typeRadios.forEach(function (radio) {
                    radio.addEventListener('change', toggleType);
                });
                toggleType();

                const container = document.getElementById('pdf-rows');
                const addBtn    = document.getElementById('add-pdf-row');

                function updateRemoveButtons() {
                    const rows = container.querySelectorAll('.pdf-row');
                    rows.forEach(function (row) {
                        row.querySelector('.remove-pdf-row').style.display = rows.length > 1 ? 'inline-block' : 'none';
                    });
                }

                addBtn.addEventListener('click', function () {
                    const first = container.querySelector('.pdf-row');
                    const clone = first.cloneNode(true);
                    clone.querySelector('input[type=text]').value = '';
                    clone.querySelector('input[type=file]').value = '';
                    container.appendChild(clone);
                    updateRemoveButtons();
                });

                container.addEventListener('click', function (e) {
                    if (e.target.closest('.remove-pdf-row')) {
                        e.target.closest('.pdf-row').remove();
                        updateRemoveButtons();
                    }
                });
            })();
        </script>
    @endpush
@endsection
