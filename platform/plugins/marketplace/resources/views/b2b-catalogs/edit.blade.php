@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <form id="b2b-catalog-form" action="{{ route('marketplace.b2b-catalogs.update', $catalog->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">{{ __('Edit B2B Catalog: :title', ['title' => $catalog->title]) }}</h4>
            </div>
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
                    <label for="discount_percentage" class="form-label">{{ __('Discount %') }}</label>
                    <input type="number" class="form-control @error('discount_percentage') is-invalid @enderror" id="discount_percentage" name="discount_percentage" value="{{ old('discount_percentage', $catalog->discount_percentage) }}" min="0" max="100" step="0.01" placeholder="e.g. 15">
                    <small class="text-muted">{{ __('Leave empty for no discount tag.') }}</small>
                    @error('discount_percentage')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="pdf_file" class="form-label">{{ __('PDF File') }}</label>
                    @if ($catalog->pdf_path)
                        <div class="mb-2">
                            <a href="{{ route('marketplace.b2b-catalogs.view-pdf', $catalog->id) }}" class="btn btn-sm btn-outline-primary">
                                <x-core::icon name="ti ti-eye" /> {{ __('View Current PDF') }}: {{ basename($catalog->pdf_path) }}
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
                <a href="{{ route('marketplace.b2b-catalogs.index') }}" class="btn btn-secondary ms-1">{{ __('Cancel') }}</a>
            </div>
        </div>
    </form>

    @include('plugins/marketplace::b2b-catalogs.partials.upload-progress', ['redirectUrl' => route('marketplace.b2b-catalogs.index'), 'actionUrl' => route('marketplace.b2b-catalogs.update', $catalog->id)])
@endsection
