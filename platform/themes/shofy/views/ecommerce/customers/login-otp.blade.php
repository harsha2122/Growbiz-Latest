@php
   Theme::set('breadcrumbHeight', 120);
   Theme::set('breadcrumbClasses', 'pb-30');
   Theme::set('pageTitle', __('Verify OTP'));
@endphp

<section class="tp-login-area pb-60 pt-60">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-5 col-lg-6 col-md-8">
                <div class="tp-login-wrapper">
                    <div class="tp-login-top text-center mb-30">
                        <h3 class="tp-login-title">{{ __('Verify OTP') }}</h3>
                        <p>{{ __('Enter the 6-digit code sent to your email address.') }}</p>
                    </div>

                    @if (session()->has('auth_error_message'))
                        <div role="alert" class="alert alert-danger">
                            {{ session('auth_error_message') }}
                        </div>
                    @endif

                    @if (session()->has('auth_success_message'))
                        <div role="alert" class="alert alert-success">
                            {{ session('auth_success_message') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('customer.login.otp.verify') }}">
                        @csrf
                        <div class="tp-login-input-wrapper">
                            <div class="tp-login-input-box">
                                <div class="tp-login-input">
                                    <input
                                        type="text"
                                        name="otp"
                                        maxlength="6"
                                        pattern="[0-9]{6}"
                                        inputmode="numeric"
                                        autocomplete="one-time-code"
                                        placeholder="{{ __('Enter 6-digit OTP') }}"
                                        class="text-center"
                                        style="font-size: 1.5rem; letter-spacing: 0.5rem;"
                                        required
                                        autofocus
                                    >
                                </div>
                                @error('otp')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="tp-login-bottom mt-25">
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-auth-submit">
                                    {{ __('Verify & Login') }}
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="tp-login-bottom mt-15">
                        <div class="d-flex justify-content-between align-items-center">
                            <form method="POST" action="{{ route('customer.login.otp.resend') }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary btn-sm">
                                    {{ __('Resend OTP') }}
                                </button>
                            </form>
                            <a href="{{ route('customer.login') }}" class="btn btn-outline-secondary btn-sm">
                                {{ __('Back to Login') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
