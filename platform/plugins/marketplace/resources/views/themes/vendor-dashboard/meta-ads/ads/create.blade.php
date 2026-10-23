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

    @if(!$hasPage)
        <div class="alert alert-warning d-flex gap-2">
            <i class="ti ti-alert-triangle fs-5 mt-1 flex-shrink-0"></i>
            <div>
                <strong>No Facebook Page linked to your ad account.</strong>
                The ad will be saved locally but will <strong>not</strong> be pushed to Meta Ads Manager.
                <a href="{{ route('marketplace.vendor.meta-ads.connection') }}" class="alert-link ms-1">
                    Go to Connection → Disconnect & reconnect to select a Facebook Page →
                </a>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('marketplace.vendor.meta-ads.ad-sets.ads.store', $adSet->id) }}" method="POST" enctype="multipart/form-data">
                @csrf

                @if($products->isNotEmpty())
                    <div class="mb-3">
                        <label class="form-label">Link to Product <span class="text-muted">(optional)</span></label>
                        <select name="product_id" class="form-select">
                            <option value="">— Select a product —</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
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

                <input type="hidden" name="format" value="SINGLE_IMAGE">

                {{-- Creative Upload --}}
                <div class="mb-3">
                    <label class="form-label">
                        Upload Creative <span class="text-danger">*</span>
                        <span class="text-muted small ms-1">(JPG, PNG, GIF, WebP — max 100MB)</span>
                    </label>
                    <input type="file" name="creative_file" id="creativeFile" class="form-control"
                           accept="image/jpeg,image/png,image/gif,image/webp">
                    <div class="mt-2 d-none" id="previewBox">
                        <img id="imagePreview" src="" alt="Preview" class="img-thumbnail" style="max-height:200px;">
                        <div class="text-muted small mt-1" id="fileName"></div>
                    </div>
                    <div class="form-text">Images only: JPG, PNG, GIF, WebP. Carousel and Video ads aren't supported yet.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Primary Text <span class="text-danger">*</span></label>
                    <textarea name="primary_text" class="form-control" rows="3" maxlength="500" required>{{ old('primary_text') }}</textarea>
                    <div class="form-text">Main ad copy shown above the image (max 500 chars)</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Headline <span class="text-danger">*</span></label>
                    <input type="text" name="headline" class="form-control" maxlength="100" value="{{ old('headline') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" class="form-control" maxlength="500" value="{{ old('description') }}"
                           placeholder="Additional text shown below headline">
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
                    <input type="url" name="destination_url" class="form-control" value="{{ old('destination_url') }}"
                           placeholder="https://yourstore.com/product" required>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-upload"></i> Upload & Create Ad
                    </button>
                    <a href="{{ route('marketplace.vendor.meta-ads.ad-sets.show', $adSet->id) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

<script>
    const creativeFile  = document.getElementById('creativeFile');
    const previewBox    = document.getElementById('previewBox');
    const imagePreview  = document.getElementById('imagePreview');
    const fileNameEl    = document.getElementById('fileName');

    creativeFile.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) { clearPreview(); return; }

        previewBox.classList.remove('d-none');
        fileNameEl.textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
        imagePreview.src = URL.createObjectURL(file);
    });

    function clearPreview() {
        previewBox.classList.add('d-none');
        imagePreview.src = '';
        fileNameEl.textContent = '';
    }
</script>
@endsection
