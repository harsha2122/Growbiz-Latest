@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', __('Contact Admin'))

@section('content')
    @php
        $customer = auth('customer')->user();
    @endphp

    <!-- Header Section -->
    <div class="bb-customer-profile-wrapper mb-4">
        <div class="bb-customer-profile">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="bg-primary bg-opacity-20 rounded-circle p-3 d-inline-flex">
                        <x-core::icon name="ti ti-message-circle" class="text-primary" style="font-size: 2rem;" />
                    </div>
                </div>
                <div class="col">
                    <div class="bb-customer-profile-info text-start">
                        <h2 class="h4 mb-2">{{ __('Contact Admin') }}</h2>
                        <p class="text-muted mb-0">
                            {{ __('Have a question or need assistance? Send us a message and our team will get back to you as soon as possible.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-3 mb-4" role="alert">
            <x-core::icon name="ti ti-check" class="text-success" />
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Contact Form -->
    <div class="bb-customer-profile-wrapper">
        <div class="bb-customer-profile">
            <form action="{{ route('customer.contact-admin.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ __('Name') }}</label>
                        <input type="text" class="form-control" value="{{ $customer->name }}" readonly disabled>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ __('Email') }}</label>
                        <input type="email" class="form-control" value="{{ $customer->email }}" readonly disabled>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold" for="subject">{{ __('Subject') }} <span class="text-danger">*</span></label>
                        <select name="subject" id="subject" class="form-select @error('subject') is-invalid @enderror" required>
                            <option value="">{{ __('Select a subject') }}</option>
                            <option value="General Inquiry" {{ old('subject') == 'General Inquiry' ? 'selected' : '' }}>{{ __('General Inquiry') }}</option>
                            <option value="Order Issue" {{ old('subject') == 'Order Issue' ? 'selected' : '' }}>{{ __('Order Issue') }}</option>
                            <option value="Payment Issue" {{ old('subject') == 'Payment Issue' ? 'selected' : '' }}>{{ __('Payment Issue') }}</option>
                            <option value="Product Question" {{ old('subject') == 'Product Question' ? 'selected' : '' }}>{{ __('Product Question') }}</option>
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
        </div>
    </div>
@endsection
