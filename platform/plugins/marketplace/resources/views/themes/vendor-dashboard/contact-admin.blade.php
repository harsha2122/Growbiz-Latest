@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="row">
        <div class="col-12">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-start gap-3" role="alert">
                    <x-core::icon name="ti ti-check" class="text-success flex-shrink-0 mt-1" />
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <x-core::card>
                <x-core::card.header>
                    <x-core::card.title>
                        <x-core::icon name="ti ti-message-circle" />
                        {{ __('Contact Admin') }}
                    </x-core::card.title>
                    <x-core::card.subtitle>
                        {{ __('Have a question or need assistance? Send us a message and our team will get back to you as soon as possible.') }}
                    </x-core::card.subtitle>
                </x-core::card.header>
                <x-core::card.body>
                    <form action="{{ route('marketplace.vendor.contact-admin.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">{{ __('Name') }}</label>
                                <input type="text" class="form-control" value="{{ auth('customer')->user()->name }}" readonly disabled>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">{{ __('Email') }}</label>
                                <input type="email" class="form-control" value="{{ auth('customer')->user()->email }}" readonly disabled>
                            </div>

                            @if (auth('customer')->user()->store)
                                <div class="col-12">
                                    <label class="form-label fw-semibold">{{ __('Store') }}</label>
                                    <input type="text" class="form-control" value="{{ auth('customer')->user()->store->name }}" readonly disabled>
                                </div>
                            @endif

                            <div class="col-12">
                                <label class="form-label fw-semibold" for="subject">{{ __('Subject') }} <span class="text-danger">*</span></label>
                                <select name="subject" id="subject" class="form-select @error('subject') is-invalid @enderror" required>
                                    <option value="">{{ __('Select a subject') }}</option>
                                    <option value="General Inquiry" {{ old('subject') == 'General Inquiry' ? 'selected' : '' }}>{{ __('General Inquiry') }}</option>
                                    <option value="Store Issue" {{ old('subject') == 'Store Issue' ? 'selected' : '' }}>{{ __('Store Issue') }}</option>
                                    <option value="Product Issue" {{ old('subject') == 'Product Issue' ? 'selected' : '' }}>{{ __('Product Issue') }}</option>
                                    <option value="Order Issue" {{ old('subject') == 'Order Issue' ? 'selected' : '' }}>{{ __('Order Issue') }}</option>
                                    <option value="Payment Issue" {{ old('subject') == 'Payment Issue' ? 'selected' : '' }}>{{ __('Payment Issue') }}</option>
                                    <option value="Withdrawal Issue" {{ old('subject') == 'Withdrawal Issue' ? 'selected' : '' }}>{{ __('Withdrawal Issue') }}</option>
                                    <option value="Technical Support" {{ old('subject') == 'Technical Support' ? 'selected' : '' }}>{{ __('Technical Support') }}</option>
                                    <option value="Feedback" {{ old('subject') == 'Feedback' ? 'selected' : '' }}>{{ __('Feedback') }}</option>
                                    <option value="Other" {{ old('subject') == 'Other' ? 'selected' : '' }}>{{ __('Other') }}</option>
                                </select>
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold" for="content">{{ __('Message') }} <span class="text-danger">*</span></label>
                                <textarea
                                    name="content"
                                    id="content"
                                    class="form-control @error('content') is-invalid @enderror"
                                    rows="5"
                                    placeholder="{{ __('Describe your issue or question in detail...') }}"
                                    required
                                >{{ old('content') }}</textarea>
                                @error('content')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <x-core::icon name="ti ti-send" class="me-1" />
                                    {{ __('Send Message') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </x-core::card.body>
            </x-core::card>
        </div>
    </div>
@endsection
