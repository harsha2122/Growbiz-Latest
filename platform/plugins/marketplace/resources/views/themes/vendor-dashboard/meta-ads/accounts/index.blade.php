@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">{{ __('Meta Ad Account') }}</h4>
    </div>

    @if ($account && $account->is_connected)
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <span class="badge bg-success me-2">{{ __('Connected') }}</span>
                    <strong>{{ $account->ad_account_name ?? $account->fb_user_name }}</strong>
                </div>
                <ul class="list-unstyled mb-3">
                    <li><strong>{{ __('Facebook User') }}:</strong> {{ $account->fb_user_name }}</li>
                    <li><strong>{{ __('Ad Account') }}:</strong> {{ $account->ad_account_name }} ({{ $account->ad_account_id }})</li>
                    <li><strong>{{ __('Connected At') }}:</strong> {{ $account->connected_at?->format('d M Y H:i') ?? '-' }}</li>
                    <li>
                        <strong>{{ __('Token Expires') }}:</strong>
                        @if ($account->token_expires_at)
                            {{ $account->token_expires_at->format('d M Y') }}
                            @if ($account->token_expires_at->isPast())
                                <span class="badge bg-danger ms-1">{{ __('Expired') }}</span>
                            @endif
                        @else
                            {{ __('Never') }}
                        @endif
                    </li>
                </ul>
                <form action="{{ route('marketplace.vendor.meta-ads.accounts.disconnect', $account->id) }}" method="POST"
                    onsubmit="return confirm('{{ __('Disconnect this Facebook account?') }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <x-core::icon name="ti ti-unlink" /> {{ __('Disconnect') }}
                    </button>
                </form>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <x-core::icon name="ti ti-brand-facebook" class="fs-1 text-primary mb-3" />
                <h5>{{ __('No Facebook Ad Account Connected') }}</h5>
                <p class="text-muted">{{ __('Connect your Facebook Ad Account to start running Meta Ads.') }}</p>
                <a href="{{ route('marketplace.vendor.meta-ads.accounts.connect') }}" class="btn btn-primary">
                    <x-core::icon name="ti ti-link" /> {{ __('Connect Facebook Account') }}
                </a>
            </div>
        </div>
    @endif
@endsection
