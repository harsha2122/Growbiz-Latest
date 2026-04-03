@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    @include(MarketplaceHelper::viewPath('vendor-dashboard.meta-ads.partials.breadcrumb'), ['items' => [['label' => 'Connection']]])

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header" style="background: linear-gradient(135deg, #1877F2, #0D47A1); color: white;">
            <div class="d-flex align-items-center">
                <x-core::icon name="ti ti-brand-meta" class="me-2" style="font-size: 24px;" />
                <div>
                    <h5 class="mb-0 text-white">{{ __('Facebook & Instagram Ads') }}</h5>
                    <small class="opacity-75">{{ __('Connect your Facebook account to manage ads') }}</small>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if ($adAccount && $adAccount->is_connected)
                {{-- Connected State --}}
                <div class="d-flex align-items-center mb-4 p-3 rounded" style="background: #f0f9f0; border: 1px solid #c3e6c3;">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px; background: #28a745; color: white;">
                        <x-core::icon name="ti ti-check" style="font-size: 24px;" />
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-0">{{ __('Connected as :name', ['name' => $adAccount->fb_user_name]) }}</h6>
                        <small class="text-muted">
                            {{ __('Facebook User ID: :id', ['id' => $adAccount->fb_user_id]) }}
                            &bull; {{ __('Connected :date', ['date' => $adAccount->connected_at?->diffForHumans()]) }}
                        </small>
                        @if ($adAccount->token_expires_at)
                            <br><small class="text-muted">{{ __('Token expires: :date', ['date' => $adAccount->token_expires_at->format('d M Y')]) }}</small>
                        @endif
                    </div>
                    <form action="{{ route('marketplace.vendor.meta-ads.connection.disconnect') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('{{ __('Are you sure you want to disconnect?') }}')">
                            <x-core::icon name="ti ti-unlink" /> {{ __('Disconnect') }}
                        </button>
                    </form>
                </div>

                {{-- Ad Account Info --}}
                <div class="card mb-0">
                    <div class="card-body">
                        <h6><x-core::icon name="ti ti-ad" class="me-1" /> {{ __('Ad Account') }}</h6>
                        @if ($adAccount->ad_account_id)
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <strong>{{ $adAccount->ad_account_name ?? $adAccount->ad_account_id }}</strong>
                                    <br><small class="text-muted">{{ $adAccount->ad_account_id }}</small>
                                </div>
                                <a href="{{ route('marketplace.vendor.meta-ads.campaigns.index') }}" class="btn btn-primary">
                                    <x-core::icon name="ti ti-arrow-right" /> {{ __('Go to Campaigns') }}
                                </a>
                            </div>
                        @else
                            <p class="text-muted mb-0">{{ __('No ad account linked. Please check your Facebook Business Manager settings.') }}</p>
                        @endif
                    </div>
                </div>
            @else
                {{-- Disconnected State --}}
                <div class="text-center py-5">
                    <div class="mb-4">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background: #E7F3FF;">
                            <x-core::icon name="ti ti-brand-meta" style="font-size: 40px; color: #1877F2;" />
                        </div>
                    </div>
                    <h5>{{ __('Connect your Facebook account') }}</h5>
                    <p class="text-muted mb-4" style="max-width: 500px; margin: 0 auto;">
                        {{ __('Link your Facebook account to create and manage ad campaigns for your products on Facebook and Instagram, directly from your vendor dashboard.') }}
                    </p>

                    @if ($appId)
                        <a href="{{ route('marketplace.vendor.meta-ads.connection.redirect') }}" class="btn btn-lg text-white" style="background: #1877F2; border-color: #1877F2;">
                            <x-core::icon name="ti ti-brand-facebook" class="me-1" /> {{ __('Connect with Facebook') }}
                        </a>
                        <div class="mt-3">
                            <small class="text-muted">{{ __('We\'ll request access to manage your ad accounts and read page information.') }}</small>
                        </div>
                    @else
                        <div class="alert alert-warning d-inline-block">
                            {{ __('Facebook integration is not configured. Please contact the administrator.') }}
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
@stop
