<?php

namespace Botble\Ecommerce\Http\Controllers\Customers;

use Botble\ACL\Traits\AuthenticatesUsers;
use Botble\ACL\Traits\LogoutGuardTrait;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Enums\CustomerStatusEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Forms\Fronts\Auth\LoginForm;
use Botble\Ecommerce\Http\Requests\LoginRequest;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Notifications\LoginOtpNotification;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends BaseController
{
    use AuthenticatesUsers, LogoutGuardTrait {
        AuthenticatesUsers::attemptLogin as baseAttemptLogin;
    }

    public string $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('customer.guest', ['except' => ['logout', 'showOtpForm', 'verifyOtp', 'resendOtp']]);
    }

    public function showLoginForm()
    {
        SeoHelper::setTitle(__('Login'));

        Theme::breadcrumb()->add(__('Login'), route('customer.login'));

        if (! in_array(url()->previous(), [route('customer.login'), route('customer.register')])) {
            session(['url.intended' => url()->previous()]);
        }

        return Theme::scope(
            'ecommerce.customers.login',
            ['form' => LoginForm::create()],
            'plugins/ecommerce::themes.customers.login'
        )->render();
    }

    protected function guard()
    {
        return auth('customer');
    }

    public function login(LoginRequest $request)
    {
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            $this->sendLockoutResponse($request);
        }

        if ($this->guard()->validate($this->credentials($request))) {
            $customer = $this->guard()->getLastAttempted();

            if (EcommerceHelper::isEnableEmailVerification() && empty($customer->confirmed_at)) {
                throw ValidationException::withMessages([
                    'confirmation' => [
                        __(
                            'The given email address has not been confirmed. <a href=":resend_link">Resend confirmation link.</a>',
                            [
                                'resend_link' => route('customer.resend_confirmation', ['email' => $customer->email]),
                            ]
                        ),
                    ],
                ]);
            }

            if ($customer->status->getValue() !== CustomerStatusEnum::ACTIVATED) {
                throw ValidationException::withMessages([
                    'email' => [
                        __('Your account has been locked, please contact the administrator.'),
                    ],
                ]);
            }

            // Check if OTP login is enabled
            if (is_plugin_active('marketplace') && MarketplaceHelper::isLoginOtpEnabled() && $customer->email) {
                return $this->sendOtp($customer, $request);
            }

            // Normal login (no OTP)
            if ($this->baseAttemptLogin($request)) {
                return $this->sendLoginResponse($request);
            }
        }

        $this->incrementLoginAttempts($request);

        $this->sendFailedLoginResponse();
    }

    protected function sendOtp(Customer $customer, Request $request)
    {
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $customer->update([
            'login_otp' => bcrypt($otp),
            'login_otp_expires_at' => Carbon::now()->addMinutes(5),
        ]);

        $customer->notify(new LoginOtpNotification($otp));

        session([
            'otp_customer_id' => $customer->id,
            'otp_remember' => $request->filled('remember'),
        ]);

        return redirect()->route('customer.login.otp')
            ->with('auth_success_message', __('A verification code has been sent to your email address.'));
    }

    public function showOtpForm()
    {
        if (! session('otp_customer_id')) {
            return redirect()->route('customer.login');
        }

        SeoHelper::setTitle(__('Verify OTP'));
        Theme::breadcrumb()->add(__('Verify OTP'), route('customer.login.otp'));

        return Theme::scope(
            'ecommerce.customers.login-otp',
            [],
            'plugins/ecommerce::themes.customers.login-otp'
        )->render();
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $customerId = session('otp_customer_id');

        if (! $customerId) {
            return redirect()->route('customer.login')
                ->with('auth_error_message', __('Your OTP session has expired. Please login again.'));
        }

        $customer = Customer::find($customerId);

        if (! $customer || ! $customer->login_otp || ! $customer->login_otp_expires_at) {
            session()->forget(['otp_customer_id', 'otp_remember']);

            return redirect()->route('customer.login')
                ->with('auth_error_message', __('Invalid OTP session. Please login again.'));
        }

        if (Carbon::now()->gt($customer->login_otp_expires_at)) {
            $customer->update([
                'login_otp' => null,
                'login_otp_expires_at' => null,
            ]);
            session()->forget(['otp_customer_id', 'otp_remember']);

            return redirect()->route('customer.login')
                ->with('auth_error_message', __('OTP has expired. Please login again.'));
        }

        if (! password_verify($request->input('otp'), $customer->login_otp)) {
            return back()->with('auth_error_message', __('Invalid OTP code. Please try again.'));
        }

        // OTP verified - clear OTP and log in
        $customer->update([
            'login_otp' => null,
            'login_otp_expires_at' => null,
        ]);

        $remember = session('otp_remember', false);
        session()->forget(['otp_customer_id', 'otp_remember']);

        $this->guard()->login($customer, $remember);
        $request->session()->regenerate();

        return redirect()->intended($this->redirectPath());
    }

    public function resendOtp()
    {
        $customerId = session('otp_customer_id');

        if (! $customerId) {
            return redirect()->route('customer.login');
        }

        $customer = Customer::find($customerId);

        if (! $customer) {
            session()->forget(['otp_customer_id', 'otp_remember']);

            return redirect()->route('customer.login');
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $customer->update([
            'login_otp' => bcrypt($otp),
            'login_otp_expires_at' => Carbon::now()->addMinutes(5),
        ]);

        $customer->notify(new LoginOtpNotification($otp));

        return back()->with('auth_success_message', __('A new OTP has been sent to your email address.'));
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();

        $this->loggedOut($request);

        return redirect()->to(BaseHelper::getHomepageUrl());
    }

    protected function attemptLogin(LoginRequest $request)
    {
        if ($this->guard()->validate($this->credentials($request))) {
            $customer = $this->guard()->getLastAttempted();

            if (EcommerceHelper::isEnableEmailVerification() && empty($customer->confirmed_at)) {
                throw ValidationException::withMessages([
                    'confirmation' => [
                        __(
                            'The given email address has not been confirmed. <a href=":resend_link">Resend confirmation link.</a>',
                            [
                                'resend_link' => route('customer.resend_confirmation', ['email' => $customer->email]),
                            ]
                        ),
                    ],
                ]);
            }

            if ($customer->status->getValue() !== CustomerStatusEnum::ACTIVATED) {
                throw ValidationException::withMessages([
                    'email' => [
                        __('Your account has been locked, please contact the administrator.'),
                    ],
                ]);
            }

            return $this->baseAttemptLogin($request);
        }

        return false;
    }

    public function credentials(LoginRequest $request): array
    {
        $usernameKey = match (EcommerceHelper::getLoginOption()) {
            'phone' => 'phone',
            'email_or_phone' => $request->isEmail($request->input($this->username())) ? 'email' : 'phone',
            default => 'email',
        };

        return [
            $usernameKey => $request->input($this->username()),
            'password' => $request->input('password'),
        ];
    }
}
