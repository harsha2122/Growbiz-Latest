@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <h4 class="mb-4">Connect Facebook Account</h4>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('marketplace.vendor.meta-ads.connection.select-account') }}" method="POST">
                @csrf

                {{-- Ad Account Selection --}}
                <h5 class="mb-3">Step 1: Select Ad Account <span class="text-danger">*</span></h5>
                <p class="text-muted small mb-3">Choose the ad account you want to use for running ads.</p>

                @if(empty($adAccounts))
                    <div class="alert alert-warning">
                        No ad accounts found. Make sure your Facebook account has access to at least one ad account in
                        <a href="https://business.facebook.com" target="_blank">Meta Business Manager</a>.
                    </div>
                @else
                    <div class="mb-4">
                        @foreach($adAccounts as $account)
                            <div class="form-check mb-2 border rounded p-3 {{ $loop->first ? 'border-primary bg-light' : '' }}">
                                <input class="form-check-input" type="radio" name="ad_account_id"
                                       id="account_{{ $loop->index }}" value="{{ $account['id'] }}"
                                       {{ $loop->first ? 'checked' : '' }} required>
                                <label class="form-check-label w-100" for="account_{{ $loop->index }}">
                                    <strong>{{ $account['name'] }}</strong>
                                    <span class="text-muted ms-2 small">{{ $account['id'] }}</span>
                                    @if(!empty($account['currency']))
                                        <span class="badge bg-secondary ms-1">{{ $account['currency'] }}</span>
                                    @endif
                                    @if(!empty($account['account_status']) && $account['account_status'] == 1)
                                        <span class="badge bg-success ms-1">Active</span>
                                    @endif
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('ad_account_id')
                        <div class="text-danger small mb-3">{{ $message }}</div>
                    @enderror
                @endif

                {{-- Facebook Page Selection --}}
                <h5 class="mb-3 mt-4">Step 2: Select Facebook Page <span class="text-warning small fw-normal">(required for creating ads)</span></h5>
                <p class="text-muted small mb-3">
                    Meta requires a Facebook Page to publish ads. Select the Page for your store.
                    If you don't have one, <a href="https://www.facebook.com/pages/create" target="_blank">create a Page on Facebook →</a>
                </p>

                @if(empty($pages))
                    <div class="alert alert-warning d-flex gap-2">
                        <i class="ti ti-alert-triangle mt-1"></i>
                        <div>
                            <strong>No Facebook Pages found</strong> on your account.
                            You can still connect now, but you won't be able to create ads until you
                            <a href="https://www.facebook.com/pages/create" target="_blank">create a Facebook Page</a>
                            and reconnect.
                        </div>
                    </div>
                @else
                    <div class="mb-4">
                        <div class="form-check mb-2 border rounded p-3">
                            <input class="form-check-input" type="radio" name="fb_page_id"
                                   id="page_none" value="">
                            <label class="form-check-label text-muted" for="page_none">
                                — Skip (ads won't be pushed to Meta) —
                            </label>
                        </div>
                        @foreach($pages as $page)
                            <div class="form-check mb-2 border rounded p-3 {{ $loop->first ? 'border-primary bg-light' : '' }}">
                                <input class="form-check-input" type="radio" name="fb_page_id"
                                       id="page_{{ $loop->index }}" value="{{ $page['id'] }}"
                                       {{ $loop->first ? 'checked' : '' }}>
                                <label class="form-check-label w-100" for="page_{{ $loop->index }}">
                                    <strong>{{ $page['name'] }}</strong>
                                    <span class="text-muted ms-2 small">ID: {{ $page['id'] }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('fb_page_id')
                        <div class="text-danger small mb-3">{{ $message }}</div>
                    @enderror
                @endif

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary" {{ empty($adAccounts) ? 'disabled' : '' }}>
                        <i class="ti ti-check"></i> Connect Account
                    </button>
                    <a href="{{ route('marketplace.vendor.meta-ads.connection') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
