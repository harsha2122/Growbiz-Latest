@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">{{ __('Add B2B Catalog') }}</h4>
        <a href="{{ route('marketplace.vendor.b2b-catalogs.index') }}" class="btn btn-secondary">
            {{ __('Back') }}
        </a>
    </div>

    <form id="b2b-catalog-form" action="{{ route('marketplace.vendor.b2b-catalogs.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card">
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
                    <label for="pdf_file" class="form-label">{{ __('PDF File') }} <span class="text-danger">*</span></label>
                    <input type="file" class="form-control @error('pdf_file') is-invalid @enderror" id="pdf_file" name="pdf_file" accept=".pdf" required>
                    @error('pdf_file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary" id="b2b-submit-btn">{{ __('Save') }}</button>
            </div>
        </div>
    </form>

    @include(MarketplaceHelper::viewPath('vendor-dashboard.b2b-catalogs.partials.upload-progress'))
@endsection
