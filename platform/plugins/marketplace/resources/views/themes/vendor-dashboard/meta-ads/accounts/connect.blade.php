@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">{{ __('Connect Facebook Account') }}</h4>
        <a href="{{ route('marketplace.vendor.meta-ads.accounts.index') }}" class="btn btn-secondary btn-sm">
            {{ __('Back') }}
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="alert alert-info">
                <x-core::icon name="ti ti-info-circle" />
                {{ __('To run Meta Ads, you need to connect your Facebook Ad Account via Facebook Login. Configure your Facebook App credentials in the admin settings first.') }}
            </div>

            <h6 class="mb-3">{{ __('Steps to connect:') }}</h6>
            <ol class="mb-4">
                <li>{{ __('Click the button below to authorize with Facebook.') }}</li>
                <li>{{ __('Grant the required permissions for Ads management.') }}</li>
                <li>{{ __('Select the Ad Account you want to use.') }}</li>
            </ol>

            @if (MarketplaceHelper::getMetaAppId())
                <a href="{{ route('marketplace.vendor.meta-ads.oauth.redirect') }}" class="btn btn-primary" id="fb-connect-btn">
                    <x-core::icon name="ti ti-brand-facebook" /> {{ __('Login with Facebook') }}
                </a>
            @else
                <div class="alert alert-warning mt-3">
                    <x-core::icon name="ti ti-alert-triangle" />
                    {{ __('Meta Ads is not configured. Please ask the administrator to set up the Facebook App ID and Secret in settings.') }}
                </div>
            @endif
        </div>
    </div>
@endsection
