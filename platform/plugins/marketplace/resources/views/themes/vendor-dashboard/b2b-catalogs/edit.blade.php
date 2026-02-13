@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">{{ __('Edit B2B Catalog') }}</h4>
        <a href="{{ route('marketplace.vendor.b2b-catalogs.index') }}" class="btn btn-secondary">
            {{ __('Back') }}
        </a>
    </div>

    <form id="b2b-catalog-form" action="{{ route('marketplace.vendor.b2b-catalogs.update', $catalog->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label for="title" class="form-label">{{ __('Title') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $catalog->title) }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">{{ __('Description') }}</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $catalog->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="pdf_file" class="form-label">{{ __('PDF File') }}</label>
                    @if ($catalog->pdf_path)
                        <div class="mb-2">
                            <a href="{{ Storage::disk('public')->url($catalog->pdf_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <x-core::icon name="ti ti-download" /> {{ __('View Current PDF') }}
                            </a>
                        </div>
                    @endif
                    <input type="file" class="form-control @error('pdf_file') is-invalid @enderror" id="pdf_file" name="pdf_file" accept=".pdf">
                    <small class="text-muted">{{ __('Upload a new file to replace the current one.') }}</small>
                    @error('pdf_file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary" id="b2b-submit-btn">{{ __('Update') }}</button>
            </div>
        </div>
    </form>

    @include(MarketplaceHelper::viewPath('vendor-dashboard.b2b-catalogs.partials.upload-progress'))
@endsection
