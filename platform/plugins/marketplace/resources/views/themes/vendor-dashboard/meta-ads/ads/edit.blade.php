@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('marketplace.vendor.meta-ads.ad-sets.show', $ad->ad_set_id) }}" class="text-muted small">← Ad Set</a>
            <h4 class="mb-0">Edit Ad</h4>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('marketplace.vendor.meta-ads.ads.update', $ad->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Ad Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $ad->name) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Format <span class="text-danger">*</span></label>
                    <select name="format" class="form-select" required>
                        @foreach(['SINGLE_IMAGE' => 'Single Image', 'CAROUSEL' => 'Carousel', 'VIDEO' => 'Video'] as $val => $label)
                            <option value="{{ $val }}" {{ old('format', $ad->format) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Primary Text <span class="text-danger">*</span></label>
                    <textarea name="primary_text" class="form-control" rows="3" required>{{ old('primary_text', $ad->primary_text) }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Headline <span class="text-danger">*</span></label>
                    <input type="text" name="headline" class="form-control" value="{{ old('headline', $ad->headline) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" class="form-control" value="{{ old('description', $ad->description) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">CTA Button <span class="text-danger">*</span></label>
                    <select name="cta_button" class="form-select" required>
                        @foreach(['LEARN_MORE' => 'Learn More', 'SHOP_NOW' => 'Shop Now', 'SIGN_UP' => 'Sign Up', 'BOOK_NOW' => 'Book Now', 'CONTACT_US' => 'Contact Us', 'DOWNLOAD' => 'Download', 'GET_OFFER' => 'Get Offer'] as $val => $label)
                            <option value="{{ $val }}" {{ old('cta_button', $ad->cta_button) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Destination URL <span class="text-danger">*</span></label>
                    <input type="url" name="destination_url" class="form-control" value="{{ old('destination_url', $ad->destination_url) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Image URL</label>
                    <input type="text" name="image_url" class="form-control" value="{{ old('image_url', $ad->image_url) }}">
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="{{ route('marketplace.vendor.meta-ads.ad-sets.show', $ad->ad_set_id) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
