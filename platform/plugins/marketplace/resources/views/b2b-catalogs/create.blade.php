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

                {{-- PDF Files --}}
                <div class="mb-3">
                    <label class="form-label">{{ __('PDF Files') }} <span class="text-danger">*</span></label>
                    <div id="pdf-rows">
                        <div class="pdf-row border rounded p-3 mb-2 bg-light">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label small mb-1">{{ __('PDF Title') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="pdf_titles[]" placeholder="{{ __('e.g. Product Catalogue 2026') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small mb-1">{{ __('PDF File') }} <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" name="pdf_files[]" accept=".pdf" required>
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
