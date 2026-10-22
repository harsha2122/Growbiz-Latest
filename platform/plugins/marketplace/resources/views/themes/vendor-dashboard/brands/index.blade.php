@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="mb-0">{{ __('Brands') }}</h4>
    </div>

    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <strong>{{ __('Add a Brand') }}</strong>
                </div>
                <div class="card-body">
                    <form action="{{ route('marketplace.vendor.brands.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">{{ __('Brand Name') }}</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">{{ __('Brand Logo') }}</label>
                                <input type="file" class="form-control @error('logo_input') is-invalid @enderror" name="logo_input" accept="image/*" required>
                                @error('logo_input')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary px-4">{{ __('Add Brand') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <strong>{{ __('Your Brands') }}</strong>
                </div>
                <div class="card-body">
                    @if ($brands->isEmpty())
                        <p class="text-muted mb-0">{{ __('No brands added yet. They will appear as a logo row on your store page.') }}</p>
                    @else
                        <div class="d-flex flex-wrap gap-3">
                            @foreach ($brands as $brand)
                                <div class="border rounded p-2 text-center" style="width: 120px;">
                                    <img src="{{ RvMedia::getImageUrl($brand->logo) }}" alt="{{ $brand->name }}" class="img-fluid mb-2" style="height: 60px; object-fit: contain;">
                                    <div class="small text-truncate mb-2">{{ $brand->name }}</div>
                                    <form action="{{ route('marketplace.vendor.brands.destroy', $brand->id) }}" method="POST" onsubmit="return confirm('{{ __('Delete this brand?') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger w-100">{{ __('Delete') }}</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
