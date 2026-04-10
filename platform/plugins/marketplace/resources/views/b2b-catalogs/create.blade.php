@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <form id="b2b-catalog-form" action="{{ route('marketplace.b2b-catalogs.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">{{ __('Create B2B Catalog') }}</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="title" class="form-label">{{ __('Title') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">{{ __('Description') }}</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="discount_percentage" class="form-label">{{ __('Discount %') }}</label>
                    <input type="number" class="form-control @error('discount_percentage') is-invalid @enderror" id="discount_percentage" name="discount_percentage" value="{{ old('discount_percentage') }}" min="0" max="100" step="0.01" placeholder="e.g. 15">
                    <small class="text-muted">{{ __('Leave empty for no discount tag.') }}</small>
                    @error('discount_percentage')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="contact_number" class="form-label">{{ __('Contact Number') }}</label>
                        <input type="text" class="form-control @error('contact_number') is-invalid @enderror" id="contact_number" name="contact_number" value="{{ old('contact_number') }}" placeholder="+91XXXXXXXXXX">
                        <small class="text-muted">{{ __('Leave empty to use the default number from settings.') }}</small>
                        @error('contact_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="whatsapp_number" class="form-label">{{ __('WhatsApp Number') }}</label>
                        <input type="text" class="form-control @error('whatsapp_number') is-invalid @enderror" id="whatsapp_number" name="whatsapp_number" value="{{ old('whatsapp_number') }}" placeholder="+91XXXXXXXXXX">
                        <small class="text-muted">{{ __('Leave empty to use the default number from settings.') }}</small>
                        @error('whatsapp_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="pdf_file" class="form-label">{{ __('PDF File') }} <span class="text-danger">*</span></label>
                    <input type="file" class="form-control @error('pdf_file') is-invalid @enderror" id="pdf_file" name="pdf_file" accept=".pdf" required>
                    @error('pdf_file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary" id="b2b-submit-btn">{{ __('Save') }}</button>
                <a href="{{ route('marketplace.b2b-catalogs.index') }}" class="btn btn-secondary ms-1">{{ __('Cancel') }}</a>
            </div>
        </div>
    </form>

    @include('plugins/marketplace::b2b-catalogs.partials.upload-progress', ['redirectUrl' => route('marketplace.b2b-catalogs.index'), 'actionUrl' => route('marketplace.b2b-catalogs.store')])
@endsection
