<div class="container">
    <div class="row justify-content-center py-5">
        <div class="col-xl-5 col-lg-6">
            <div class="card">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <h3 class="fs-4 mb-2">{{ __('Verify OTP') }}</h3>
                        <p class="text-muted">{{ __('Enter the 6-digit code sent to your email address.') }}</p>
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
                        <div class="mb-3">
                            <label for="otp" class="form-label">{{ __('OTP Code') }}</label>
                            <input
                                type="text"
                                class="form-control form-control-lg text-center @error('otp') is-invalid @enderror"
                                id="otp"
                                name="otp"
                                maxlength="6"
                                pattern="[0-9]{6}"
                                inputmode="numeric"
                                autocomplete="one-time-code"
                                placeholder="000000"
                                required
                                autofocus
                            >
                            @error('otp')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            {{ __('Verify & Login') }}
                        </button>
                    </form>

                    <div class="text-center">
                        <form method="POST" action="{{ route('customer.login.otp.resend') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-link p-0 text-decoration-underline">
                                {{ __('Resend OTP') }}
                            </button>
                        </form>
                        <span class="mx-2">|</span>
                        <a href="{{ route('customer.login') }}" class="text-decoration-underline">
                            {{ __('Back to Login') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
