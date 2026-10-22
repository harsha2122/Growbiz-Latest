@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="mb-0">{{ __('Gallery') }}</h4>
    </div>

    <div class="alert alert-info">
        {{ __(':count / :limit images used. The admin controls your maximum gallery size.', ['count' => $images->count(), 'limit' => $limit]) }}
    </div>

    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <strong>{{ __('Add an Image') }}</strong>
                </div>
                <div class="card-body">
                    @if ($images->count() >= $limit)
                        <p class="text-muted mb-0">{{ __('You have reached your gallery image limit. Remove an image to add a new one.') }}</p>
                    @else
                        <form action="{{ route('marketplace.vendor.gallery.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">{{ __('Image') }}</label>
                                    <input type="file" class="form-control @error('image_input') is-invalid @enderror" name="image_input" accept="image/*" required>
                                    @error('image_input')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary px-4">{{ __('Add Image') }}</button>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-7 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <strong>{{ __('Your Gallery') }}</strong>
                </div>
                <div class="card-body">
                    @if ($images->isEmpty())
                        <p class="text-muted mb-0">{{ __('No images added yet. They will appear in the gallery section on your store page.') }}</p>
                    @else
                        <div class="d-flex flex-wrap gap-3">
                            @foreach ($images as $image)
                                <div class="border rounded p-2 text-center" style="width: 120px;">
                                    <img src="{{ RvMedia::getImageUrl($image->image) }}" alt="" class="img-fluid mb-2" style="height: 80px; width: 100%; object-fit: cover;">
                                    <form action="{{ route('marketplace.vendor.gallery.destroy', $image->id) }}" method="POST" onsubmit="return confirm('{{ __('Delete this image?') }}');">
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
