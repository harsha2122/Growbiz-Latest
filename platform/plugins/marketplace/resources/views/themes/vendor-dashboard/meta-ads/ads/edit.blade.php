@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">{{ __('Edit Ad') }}</h4>
        <a href="{{ route('marketplace.vendor.meta-ads.ads.index') }}" class="btn btn-secondary btn-sm">{{ __('Back') }}</a>
    </div>
    <form action="{{ route('marketplace.vendor.meta-ads.ads.update', $ad->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('Ad Name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $ad->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('Ad Set') }} <span class="text-danger">*</span></label>
                        <select name="ad_set_id" class="form-select" required>
                            @foreach ($adSets as $adSet)
                                <option value="{{ $adSet->id }}" {{ old('ad_set_id', $ad->ad_set_id) == $adSet->id ? 'selected' : '' }}>{{ $adSet->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('Format') }}</label>
                        <select name="format" class="form-select">
                            @foreach (['SINGLE_IMAGE' => __('Single Image'), 'CAROUSEL' => __('Carousel'), 'VIDEO' => __('Video'), 'COLLECTION' => __('Collection')] as $val => $label)
                                <option value="{{ $val }}" {{ old('format', $ad->format) == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('CTA Button') }}</label>
                        <select name="cta_button" class="form-select">
                            @foreach (['LEARN_MORE' => __('Learn More'), 'SHOP_NOW' => __('Shop Now'), 'SIGN_UP' => __('Sign Up'), 'CONTACT_US' => __('Contact Us'), 'GET_OFFER' => __('Get Offer'), 'BOOK_NOW' => __('Book Now'), 'SUBSCRIBE' => __('Subscribe')] as $val => $label)
                                <option value="{{ $val }}" {{ old('cta_button', $ad->cta_button) == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ __('Primary Text') }}</label>
                        <textarea name="primary_text" class="form-control" rows="3" maxlength="2000">{{ old('primary_text', $ad->primary_text) }}</textarea>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('Headline') }}</label>
                        <input type="text" name="headline" class="form-control" value="{{ old('headline', $ad->headline) }}" maxlength="255">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('Destination URL') }}</label>
                        <input type="url" name="destination_url" class="form-control" value="{{ old('destination_url', $ad->destination_url) }}">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('Image URL') }}</label>
                        <input type="text" name="image_url" class="form-control" value="{{ old('image_url', $ad->image_url) }}">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('Link to Product') }}</label>
                        <select name="product_id" class="form-select">
                            <option value="">{{ __('None') }}</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" {{ old('product_id', $ad->product_id) == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('Status') }}</label>
                        <select name="status" class="form-select">
                            @foreach (['IN_REVIEW' => __('In Review'), 'ACTIVE' => __('Active'), 'PAUSED' => __('Paused')] as $val => $label)
                                <option value="{{ $val }}" {{ old('status', $ad->status) == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-top">
                    <h6 class="text-muted">{{ __('Performance') }}</h6>
                    <div class="row g-3">
                        <div class="col-3 text-center"><div class="fs-5 fw-bold">{{ number_format($ad->impressions) }}</div><small class="text-muted">{{ __('Impressions') }}</small></div>
                        <div class="col-3 text-center"><div class="fs-5 fw-bold">{{ number_format($ad->clicks) }}</div><small class="text-muted">{{ __('Clicks') }}</small></div>
                        <div class="col-3 text-center"><div class="fs-5 fw-bold">{{ number_format($ad->ctr, 2) }}%</div><small class="text-muted">{{ __('CTR') }}</small></div>
                        <div class="col-3 text-center"><div class="fs-5 fw-bold">${{ number_format($ad->spend, 2) }}</div><small class="text-muted">{{ __('Spend') }}</small></div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">{{ __('Update Ad') }}</button>
            </div>
        </div>
    </form>
@endsection
