@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    @include(MarketplaceHelper::viewPath('vendor-dashboard.meta-ads.partials.breadcrumb'), ['items' => [
        ['label' => 'Campaigns', 'url' => route('marketplace.vendor.meta-ads.campaigns.index')],
        ['label' => $ad->adSet->campaign->name, 'url' => route('marketplace.vendor.meta-ads.campaigns.show', $ad->adSet->campaign_id)],
        ['label' => $ad->adSet->name, 'url' => route('marketplace.vendor.meta-ads.ad-sets.show', $ad->ad_set_id)],
        ['label' => 'Edit: ' . $ad->name],
    ]])

    <h4 class="mb-3">{{ __('Edit Ad') }}</h4>

    <form action="{{ route('marketplace.vendor.meta-ads.ads.update', $ad->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">{{ __('Ad Details') }}</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">{{ __('Ad Name') }} <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $ad->name) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('Select Product') }}</label>
                    <select name="product_id" class="form-select">
                        <option value="">{{ __('-- No product --') }}</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" {{ old('product_id', $ad->product_id) == $product->id ? 'selected' : '' }}>
                                {{ $product->name }} — ₹{{ number_format($product->price, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('Ad Format') }}</label>
                    <div class="d-flex gap-3">
                        @foreach (['SINGLE_IMAGE' => 'Single Image', 'CAROUSEL' => 'Carousel', 'VIDEO' => 'Video'] as $val => $label)
                            <div class="form-check">
                                <input type="radio" name="format" value="{{ $val }}" class="form-check-input" id="format_{{ $val }}" {{ old('format', $ad->format) === $val ? 'checked' : '' }}>
                                <label class="form-check-label" for="format_{{ $val }}">{{ $label }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('Image URL') }}</label>
                    <input type="text" name="image_url" class="form-control" value="{{ old('image_url', $ad->image_url) }}">
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">{{ __('Ad Copy') }}</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">{{ __('Primary Text') }} <span class="text-danger">*</span></label>
                    <textarea name="primary_text" class="form-control" rows="3" required>{{ old('primary_text', $ad->primary_text) }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Headline') }} <span class="text-danger">*</span></label>
                    <input type="text" name="headline" class="form-control" value="{{ old('headline', $ad->headline) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Description') }}</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description', $ad->description) }}</textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('CTA Button') }}</label>
                        <select name="cta_button" class="form-select">
                            @foreach (['LEARN_MORE' => 'Learn More', 'SHOP_NOW' => 'Shop Now', 'SIGN_UP' => 'Sign Up', 'BOOK_NOW' => 'Book Now', 'CONTACT_US' => 'Contact Us', 'DOWNLOAD' => 'Download', 'GET_OFFER' => 'Get Offer'] as $val => $label)
                                <option value="{{ $val }}" {{ old('cta_button', $ad->cta_button) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('Destination URL') }} <span class="text-danger">*</span></label>
                        <input type="url" name="destination_url" class="form-control" value="{{ old('destination_url', $ad->destination_url) }}" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><x-core::icon name="ti ti-check" /> {{ __('Update Ad') }}</button>
            <a href="{{ route('marketplace.vendor.meta-ads.ad-sets.show', $ad->ad_set_id) }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
@stop
