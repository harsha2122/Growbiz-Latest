@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    @include(MarketplaceHelper::viewPath('vendor-dashboard.meta-ads.partials.breadcrumb'), ['items' => [
        ['label' => 'Campaigns', 'url' => route('marketplace.vendor.meta-ads.campaigns.index')],
        ['label' => $adSet->campaign->name, 'url' => route('marketplace.vendor.meta-ads.campaigns.show', $adSet->campaign_id)],
        ['label' => $adSet->name, 'url' => route('marketplace.vendor.meta-ads.ad-sets.show', $adSet->id)],
        ['label' => 'Create Ad'],
    ]])

    <h4 class="mb-3">{{ __('Create Ad') }}</h4>

    <form action="{{ route('marketplace.vendor.meta-ads.ad-sets.ads.store', $adSet->id) }}" method="POST">
        @csrf

        <div class="row">
            <div class="col-lg-7">
                {{-- Ad Details --}}
                <div class="card mb-3">
                    <div class="card-header"><h6 class="mb-0">{{ __('Ad Details') }}</h6></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Ad Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required placeholder="e.g. Product Showcase - Summer">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Select Product (optional)') }}</label>
                            <select name="product_id" class="form-select" id="productSelect">
                                <option value="">{{ __('-- No product --') }}</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}"
                                        data-image="{{ RvMedia::getImageUrl($product->image, 'thumb') }}"
                                        data-name="{{ $product->name }}"
                                        data-price="{{ $product->price }}"
                                        {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }} — ₹{{ number_format($product->price, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">{{ __('Selecting a product auto-fills image, headline, and URL') }}</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Ad Format') }} <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                @foreach (['SINGLE_IMAGE' => 'Single Image', 'CAROUSEL' => 'Carousel', 'VIDEO' => 'Video'] as $val => $label)
                                    <div class="form-check">
                                        <input type="radio" name="format" value="{{ $val }}" class="form-check-input" id="format_{{ $val }}" {{ old('format', 'SINGLE_IMAGE') === $val ? 'checked' : '' }}>
                                        <label class="form-check-label" for="format_{{ $val }}">{{ $label }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Image URL') }}</label>
                            <input type="text" name="image_url" id="imageUrl" class="form-control" value="{{ old('image_url') }}" placeholder="https://...">
                        </div>
                    </div>
                </div>

                {{-- Ad Copy --}}
                <div class="card mb-3">
                    <div class="card-header"><h6 class="mb-0">{{ __('Ad Copy') }}</h6></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Primary Text') }} <span class="text-danger">*</span></label>
                            <textarea name="primary_text" class="form-control @error('primary_text') is-invalid @enderror" rows="3" maxlength="500" required placeholder="Main text that appears above your ad image">{{ old('primary_text') }}</textarea>
                            @error('primary_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Headline') }} <span class="text-danger">*</span></label>
                            <input type="text" name="headline" id="headline" class="form-control @error('headline') is-invalid @enderror" value="{{ old('headline') }}" maxlength="100" required placeholder="Bold text below the image">
                            @error('headline') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Description') }}</label>
                            <textarea name="description" class="form-control" rows="2" maxlength="500" placeholder="Additional text shown in some placements">{{ old('description') }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('CTA Button') }} <span class="text-danger">*</span></label>
                                <select name="cta_button" class="form-select">
                                    @foreach (['LEARN_MORE' => 'Learn More', 'SHOP_NOW' => 'Shop Now', 'SIGN_UP' => 'Sign Up', 'BOOK_NOW' => 'Book Now', 'CONTACT_US' => 'Contact Us', 'DOWNLOAD' => 'Download', 'GET_OFFER' => 'Get Offer'] as $val => $label)
                                        <option value="{{ $val }}" {{ old('cta_button', 'SHOP_NOW') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Destination URL') }} <span class="text-danger">*</span></label>
                                <input type="url" name="destination_url" id="destinationUrl" class="form-control @error('destination_url') is-invalid @enderror" value="{{ old('destination_url') }}" required placeholder="https://yourstore.com/product">
                                @error('destination_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><x-core::icon name="ti ti-check" /> {{ __('Create Ad') }}</button>
                    <a href="{{ route('marketplace.vendor.meta-ads.ad-sets.show', $adSet->id) }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                </div>
            </div>

            {{-- Live Preview --}}
            <div class="col-lg-5">
                <div class="card sticky-top" style="top: 20px;">
                    <div class="card-header"><h6 class="mb-0"><x-core::icon name="ti ti-eye" class="me-1" /> {{ __('Ad Preview — Facebook Feed') }}</h6></div>
                    <div class="card-body p-0">
                        <div class="p-3" style="background: #f0f2f5;">
                            <div class="bg-white rounded shadow-sm overflow-hidden">
                                {{-- Header --}}
                                <div class="d-flex align-items-center p-3 pb-2">
                                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white" style="width: 36px; height: 36px; font-size: 14px;">S</div>
                                    <div class="ms-2">
                                        <strong style="font-size: 13px;">{{ __('Your Store') }}</strong>
                                        <div style="font-size: 11px; color: #65676B;">Sponsored · <x-core::icon name="ti ti-world" style="font-size: 11px;" /></div>
                                    </div>
                                </div>
                                {{-- Text --}}
                                <div class="px-3 pb-2" style="font-size: 14px;" id="previewText">{{ __('Your ad text will appear here...') }}</div>
                                {{-- Image --}}
                                <div style="background: #e4e6eb; min-height: 200px; display: flex; align-items: center; justify-content: center;" id="previewImage">
                                    <x-core::icon name="ti ti-photo" style="font-size: 48px; color: #bcc0c4;" />
                                </div>
                                {{-- Bottom --}}
                                <div class="p-3 d-flex justify-content-between align-items-center" style="background: #f0f2f5;">
                                    <div>
                                        <div style="font-size: 12px; color: #65676B;" id="previewDomain">yourstore.com</div>
                                        <strong style="font-size: 14px;" id="previewHeadline">{{ __('Your headline here') }}</strong>
                                    </div>
                                    <button class="btn btn-sm btn-outline-secondary" id="previewCta">{{ __('Shop Now') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop

@push('footer')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var productSelect = document.getElementById('productSelect');
            var imageUrl = document.getElementById('imageUrl');
            var headline = document.getElementById('headline');
            var destinationUrl = document.getElementById('destinationUrl');

            // Product auto-fill
            productSelect.addEventListener('change', function () {
                var option = this.options[this.selectedIndex];
                if (option.value) {
                    imageUrl.value = option.dataset.image || '';
                    headline.value = option.dataset.name || '';
                    destinationUrl.value = window.location.origin + '/products/' + encodeURIComponent(option.dataset.name.toLowerCase().replace(/\s+/g, '-'));
                    updatePreview();
                }
            });

            // Live preview updates
            var primaryText = document.querySelector('[name="primary_text"]');
            var ctaSelect = document.querySelector('[name="cta_button"]');

            [primaryText, headline, imageUrl, destinationUrl, ctaSelect].forEach(function (el) {
                if (el) el.addEventListener('input', updatePreview);
                if (el) el.addEventListener('change', updatePreview);
            });

            function updatePreview() {
                document.getElementById('previewText').textContent = primaryText.value || 'Your ad text will appear here...';
                document.getElementById('previewHeadline').textContent = headline.value || 'Your headline here';

                var ctaLabels = {LEARN_MORE:'Learn More',SHOP_NOW:'Shop Now',SIGN_UP:'Sign Up',BOOK_NOW:'Book Now',CONTACT_US:'Contact Us',DOWNLOAD:'Download',GET_OFFER:'Get Offer'};
                document.getElementById('previewCta').textContent = ctaLabels[ctaSelect.value] || 'Shop Now';

                if (destinationUrl.value) {
                    try { document.getElementById('previewDomain').textContent = new URL(destinationUrl.value).hostname; } catch(e) {}
                }

                var imgEl = document.getElementById('previewImage');
                if (imageUrl.value) {
                    imgEl.innerHTML = '<img src="' + imageUrl.value + '" style="width:100%; display:block;" onerror="this.style.display=\'none\'">';
                }
            }
        });
    </script>
@endpush
