@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <h4 class="mb-4">Select Your Ad Account</h4>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <p class="text-muted mb-4">We found the following ad accounts linked to your Facebook profile. Select the one you want to use for running ads.</p>

            <form action="{{ route('marketplace.vendor.meta-ads.connection.select-account') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label class="form-label fw-bold">Ad Account <span class="text-danger">*</span></label>

                    @if(empty($adAccounts))
                        <div class="alert alert-warning">
                            No ad accounts found. Make sure your Facebook account has access to at least one ad account in
                            <a href="https://business.facebook.com" target="_blank">Meta Business Manager</a>.
                        </div>
                    @else
                        @foreach($adAccounts as $account)
                            <div class="form-check mb-2 border rounded p-3 {{ $loop->first ? 'border-primary' : '' }}">
                                <input class="form-check-input" type="radio" name="ad_account_id"
                                       id="account_{{ $loop->index }}" value="{{ $account['id'] }}"
                                       {{ $loop->first ? 'checked' : '' }}>
                                <label class="form-check-label w-100" for="account_{{ $loop->index }}">
                                    <strong>{{ $account['name'] }}</strong>
                                    <span class="text-muted ms-2 small">{{ $account['id'] }}</span>
                                    @if(!empty($account['currency']))
                                        <span class="badge bg-light text-dark ms-1">{{ $account['currency'] }}</span>
                                    @endif
                                </label>
                            </div>
                        @endforeach
                    @endif

                    @error('ad_account_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary" {{ empty($adAccounts) ? 'disabled' : '' }}>
                    <i class="ti ti-check"></i> Connect This Account
                </button>
                <a href="{{ route('marketplace.vendor.meta-ads.connection') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
            </form>
        </div>
    </div>
@endsection
