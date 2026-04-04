@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('marketplace.vendor.meta-ads.ad-sets.show', $adSet->id) }}" class="text-muted small">← {{ $adSet->name }}</a>
            <h4 class="mb-0">Create Ad</h4>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('marketplace.vendor.meta-ads.ad-sets.ads.store', $adSet->id) }}" method="POST">
                @csrf
                @if($products->isNotEmpty())
                    <div class="mb-3">
                        <label class="form-label">Link to Product (optional)</label>
                        <select name="product_id" class="form-select" id="productSelect">
                            <option value="">— Select a product —</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}"
                                    data-url="{{ route('public.product', $product->slug ?? $product->id) }}"
                                    {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="mb-3">
                    <label class="form-label">Ad Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Format <span class="text-danger">*</span></label>
                    <select name="format" class="form-select" required>
                        <option value="SINGLE_IMAGE">Single Image</option>
                        <option value="CAROUSEL">Carousel</option>
                        <option value="VIDEO">Video</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Primary Text <span class="text-danger">*</span></label>
                    <textarea name="primary_text" class="form-control" rows="3" maxlength="500" required>{{ old('primary_text') }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Headline <span class="text-danger">*</span></label>
                    <input type="text" name="headline" class="form-control" maxlength="100" value="{{ old('headline') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" class="form-control" maxlength="500" value="{{ old('description') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">CTA Button <span class="text-danger">*</span></label>
                    <select name="cta_button" class="form-select" required>
                        @foreach(['LEARN_MORE' => 'Learn More', 'SHOP_NOW' => 'Shop Now', 'SIGN_UP' => 'Sign Up', 'BOOK_NOW' => 'Book Now', 'CONTACT_US' => 'Contact Us', 'DOWNLOAD' => 'Download', 'GET_OFFER' => 'Get Offer'] as $val => $label)
                            <option value="{{ $val }}" {{ old('cta_button') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Destination URL <span class="text-danger">*</span></label>
                    <input type="url" name="destination_url" id="destinationUrl" class="form-control" value="{{ old('destination_url') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Image URL</label>
                    <input type="text" name="image_url" class="form-control" value="{{ old('image_url') }}" placeholder="https://...">
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Create Ad</button>
                    <a href="{{ route('marketplace.vendor.meta-ads.ad-sets.show', $adSet->id) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    @if($products->isNotEmpty())
    <script>
        document.getElementById('productSelect')?.addEventListener('change', function() {
            const url = this.options[this.selectedIndex].dataset.url;
            if (url) document.getElementById('destinationUrl').value = url;
        });
    </script>
    @endif
@endsection
