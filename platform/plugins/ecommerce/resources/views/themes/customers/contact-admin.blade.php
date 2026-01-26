@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', __('Contact Admin'))

@section('content')
    <div class="bb-contact-admin-wrapper">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="mb-4">
                    <p class="text-muted">{{ __('Have a question or need assistance? Send us a message and our team will get back to you as soon as possible.') }}</p>
                </div>

                <form action="{{ route('customer.contact-admin.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Name') }}</label>
                            <input type="text" class="form-control bg-light" value="{{ auth('customer')->user()->name }}" readonly disabled>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ __('Email') }}</label>
                            <input type="email" class="form-control bg-light" value="{{ auth('customer')->user()->email }}" readonly disabled>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="subject">{{ __('Subject') }} <span class="text-danger">*</span></label>
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
                            <label class="form-label" for="content">{{ __('Message') }} <span class="text-danger">*</span></label>
                            <textarea
                                name="content"
                                id="content"
                                class="form-control @error('content') is-invalid @enderror"
                                rows="6"
                                placeholder="{{ __('Describe your issue or question in detail...') }}"
                                required
                            >{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <x-core::icon name="ti ti-send" />
                                {{ __('Send Message') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
