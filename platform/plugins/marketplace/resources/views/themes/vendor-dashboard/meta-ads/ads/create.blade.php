@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">{{ __('Create Ad') }}</h4>
        <a href="{{ route('marketplace.vendor.meta-ads.ads.index') }}" class="btn btn-secondary btn-sm">{{ __('Back') }}</a>
    </div>
    <form action="{{ route('marketplace.vendor.meta-ads.ads.store') }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('Ad Name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('Ad Set') }} <span class="text-danger">*</span></label>
                        <select name="ad_set_id" class="form-select @error('ad_set_id') is-invalid @enderror" required>
                            <option value="">{{ __('Select ad set') }}</option>
                            @foreach ($adSets as $adSet)
                                <option value="{{ $adSet->id }}" {{ old('ad_set_id') == $adSet->id ? 'selected' : '' }}>{{ $adSet->name }}</option>
                            @endforeach
                        </select>
                        @error('ad_set_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('Format') }} <span class="text-danger">*</span></label>
                        <select name="format" class="form-select" required>
                            @foreach (['SINGLE_IMAGE' => __('Single Image'), 'CAROUSEL' => __('Carousel'), 'VIDEO' => __('Video'), 'COLLECTION' => __('Collection')] as $val => $label)
                                <option value="{{ $val }}" {{ old('format', 'SINGLE_IMAGE') == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('CTA Button') }}</label>
                        <select name="cta_button" class="form-select">
                            @foreach (['LEARN_MORE' => __('Learn More'), 'SHOP_NOW' => __('Shop Now'), 'SIGN_UP' => __('Sign Up'), 'CONTACT_US' => __('Contact Us'), 'GET_OFFER' => __('Get Offer'), 'BOOK_NOW' => __('Book Now'), 'SUBSCRIBE' => __('Subscribe')] as $val => $label)
                                <option value="{{ $val }}" {{ old('cta_button', 'LEARN_MORE') == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ __('Primary Text') }}</label>
                        <textarea name="primary_text" class="form-control" rows="3" maxlength="2000">{{ old('primary_text') }}</textarea>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('Headline') }}</label>
                        <input type="text" name="headline" class="form-control" value="{{ old('headline') }}" maxlength="255">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('Destination URL') }}</label>
                        <input type="url" name="destination_url" class="form-control" value="{{ old('destination_url') }}">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('Image URL') }}</label>
                        <input type="text" name="image_url" class="form-control" value="{{ old('image_url') }}">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('Link to Product') }}</label>
                        <select name="product_id" class="form-select">
                            <option value="">{{ __('None') }}</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ __('Status') }}</label>
                        <select name="status" class="form-select">
                            <option value="IN_REVIEW">{{ __('Submit for Review') }}</option>
                            <option value="PAUSED">{{ __('Paused (Draft)') }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">{{ __('Create Ad') }}</button>
            </div>
        </div>
    </form>
@endsection
