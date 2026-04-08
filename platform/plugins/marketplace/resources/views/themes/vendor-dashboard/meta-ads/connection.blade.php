@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <h4 class="mb-4">Facebook Connection</h4>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            @if($adAccount && $adAccount->is_connected)
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                        <i class="ti ti-check text-white fs-4"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Connected</h5>
                        <div class="text-muted">{{ $adAccount->fb_user_name }}</div>
                    </div>
                </div>
                @if($adAccount->ad_account_name)
                    <p class="mb-1">Ad Account: <strong>{{ $adAccount->ad_account_name }}</strong>
                        <span class="text-muted">({{ $adAccount->ad_account_id }})</span></p>
                @endif
                @if($adAccount->fb_page_name)
                    <p class="mb-1">Facebook Page: <strong>{{ $adAccount->fb_page_name }}</strong>
                        <span class="text-muted">({{ $adAccount->fb_page_id }})</span></p>
                @else
                    <div class="alert alert-danger d-flex gap-2 mt-3">
                        <i class="ti ti-alert-triangle mt-1 flex-shrink-0"></i>
                        <div>
                            <strong>No Facebook Page linked — ads cannot be created.</strong>
                            Meta requires a Facebook Page to publish ads (your personal profile is not enough).
                            <div class="mt-2">
                                Don't have a Page?
                                <a href="https://www.facebook.com/pages/create" target="_blank" class="alert-link fw-semibold">
                                    Create a free Facebook Page →
                                </a>
                                (takes 2 minutes)
                            </div>
                            <div class="mt-1">
                                Already have a Page? Disconnect and reconnect below to select it.
                            </div>
                        </div>
                    </div>
                @endif
                @if($adAccount->token_expires_at)
                    <p class="mb-1 text-muted small">Token expires: {{ $adAccount->token_expires_at->format('M d, Y') }}</p>
                @endif
                @if($adAccount->connected_at)
                    <p class="text-muted small">Connected {{ $adAccount->connected_at->diffForHumans() }}</p>
                @endif
                <form action="{{ route('marketplace.vendor.meta-ads.connection.disconnect') }}" method="POST"
                      onsubmit="return confirm('Disconnect your Facebook account?')">
                    @csrf
                    <button class="btn btn-outline-danger">Disconnect</button>
                </form>
            @else
                <p class="text-muted">Connect your Facebook account to start creating and managing ads.</p>
                @if($oauthUrl)
                    <a href="{{ $oauthUrl }}" class="btn btn-primary btn-lg">
                        <i class="ti ti-brand-facebook"></i> Connect with Facebook
                    </a>
                @else
                    <div class="alert alert-warning">
                        Facebook App credentials are not configured. Please contact the admin.
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection
