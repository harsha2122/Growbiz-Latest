@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">{{ __('Edit Service') }}</h4>
        <a href="{{ route('marketplace.vendor.services.index') }}" class="btn btn-secondary">
            {{ __('Back') }}
        </a>
    </div>

    <form action="{{ route('marketplace.vendor.services.update', $service->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">{{ __('Service Name') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $service->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">{{ __('Description') }}</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $service->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="price" class="form-label">{{ __('Price') }}</label>
                        <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $service->price) }}" min="0" step="0.01">
                        <small class="text-muted">{{ __('Leave empty to show "Contact for price".') }}</small>
                        @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="order" class="form-label">{{ __('Display Order') }}</label>
                        <input type="number" class="form-control @error('order') is-invalid @enderror" id="order" name="order" value="{{ old('order', $service->order) }}" min="0">
                        @error('order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">{{ __('Status') }}</label>
                    <select class="form-control @error('status') is-invalid @enderror" id="status" name="status">
                        @foreach (\Botble\Base\Enums\BaseStatusEnum::labels() as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $service->status->getValue()) == $value)>{{ __($label) }}</option>
                        @endforeach
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="image_input" class="form-label">{{ __('Image') }}</label>
                    @if ($service->image)
                        <div class="mb-2">
                            <img src="{{ RvMedia::getImageUrl($service->image) }}" alt="{{ $service->name }}" style="max-width: 150px; border-radius: 6px;">
                        </div>
                    @endif
                    <input type="file" class="form-control @error('image_input') is-invalid @enderror" id="image_input" name="image_input" accept="image/*">
                    @error('image_input')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
            </div>
        </div>
    </form>
@endsection
