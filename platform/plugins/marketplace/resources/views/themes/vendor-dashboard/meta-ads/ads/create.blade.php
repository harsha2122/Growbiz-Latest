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

                <div class="mb-3">
                    <label class="form-label">Format <span class="text-danger">*</span></label>
                    <select name="format" class="form-select" required id="formatSelect">
                        <option value="SINGLE_IMAGE" {{ old('format', 'SINGLE_IMAGE') === 'SINGLE_IMAGE' ? 'selected' : '' }}>Single Image</option>
                        <option value="CAROUSEL" {{ old('format') === 'CAROUSEL' ? 'selected' : '' }}>Carousel</option>
                        <option value="VIDEO" {{ old('format') === 'VIDEO' ? 'selected' : '' }}>Video</option>
                    </select>
                </div>

                {{-- Creative Upload --}}
                <div class="mb-3">
                    <label class="form-label">
                        Upload Creative <span class="text-danger">*</span>
                        <span class="text-muted small ms-1" id="uploadHint">(JPG, PNG, GIF, WebP — max 100MB)</span>
                    </label>
                    <input type="file" name="creative_file" id="creativeFile" class="form-control"
                           accept="image/jpeg,image/png,image/gif,image/webp,video/mp4,video/quicktime,video/avi">
                    <div class="mt-2 d-none" id="previewBox">
                        <img id="imagePreview" src="" alt="Preview" class="img-thumbnail" style="max-height:200px; display:none;">
                        <video id="videoPreview" controls class="img-thumbnail" style="max-height:200px; display:none;"></video>
                        <div class="text-muted small mt-1" id="fileName"></div>
                    </div>
                    <div class="form-text">Images: JPG, PNG, GIF, WebP &nbsp;|&nbsp; Videos: MP4, MOV, AVI</div>
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
    const formatSelect  = document.getElementById('formatSelect');
    const creativeFile  = document.getElementById('creativeFile');
    const uploadHint    = document.getElementById('uploadHint');
    const previewBox    = document.getElementById('previewBox');
    const imagePreview  = document.getElementById('imagePreview');
    const videoPreview  = document.getElementById('videoPreview');
    const fileNameEl    = document.getElementById('fileName');

    formatSelect.addEventListener('change', function () {
        if (this.value === 'VIDEO') {
            creativeFile.accept = 'video/mp4,video/quicktime,video/avi';
            uploadHint.textContent = '(MP4, MOV, AVI — max 100MB)';
        } else {
            creativeFile.accept = 'image/jpeg,image/png,image/gif,image/webp,video/mp4,video/quicktime,video/avi';
            uploadHint.textContent = '(JPG, PNG, GIF, WebP — max 100MB)';
        }
        clearPreview();
    });

    creativeFile.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) { clearPreview(); return; }

        previewBox.classList.remove('d-none');
        fileNameEl.textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';

        const url = URL.createObjectURL(file);
        if (file.type.startsWith('video/')) {
            imagePreview.style.display = 'none';
            videoPreview.src = url;
            videoPreview.style.display = 'block';
        } else {
            videoPreview.style.display = 'none';
            imagePreview.src = url;
            imagePreview.style.display = 'block';
        }
    });

    function clearPreview() {
        previewBox.classList.add('d-none');
        imagePreview.src = '';
        videoPreview.src = '';
        fileNameEl.textContent = '';
    }
</script>
@endsection
