@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <h4 class="mb-4">Select Ad Account & Facebook Page</h4>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <p class="text-muted mb-4">We found multiple ad accounts or pages. Please select which ones to use for your ads.</p>

            <form action="{{ route('marketplace.vendor.meta-ads.connection.select-account') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label class="form-label fw-bold">Ad Account <span class="text-danger">*</span></label>
                    @if(empty($adAccounts))
                        <div class="alert alert-warning">No ad accounts found. Make sure your Facebook account has access to at least one ad account.</div>
                    @else
                        @foreach($adAccounts as $account)
                            <div class="form-check mb-2 border rounded p-3">
                                <input class="form-check-input" type="radio" name="ad_account_id"
                                       id="account_{{ $account['id'] }}" value="{{ $account['id'] }}"
                                       {{ $loop->first ? 'checked' : '' }}>
                                <label class="form-check-label" for="account_{{ $account['id'] }}">
                                    <strong>{{ $account['name'] }}</strong>
                                    <span class="text-muted ms-2">{{ $account['id'] }}</span>
                                    @if(!empty($account['currency']))
                                        <span class="badge bg-secondary ms-1">{{ $account['currency'] }}</span>
                                    @endif
                                </label>
                            </div>
                        @endforeach
                    @endif
                    @error('ad_account_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                @if(!empty($pages))
                    <div class="mb-4">
                        <label class="form-label fw-bold">Facebook Page (for ad creatives)</label>
                        <div class="form-check mb-2 border rounded p-3">
                            <input class="form-check-input" type="radio" name="fb_page_id" id="page_none" value="">
                            <label class="form-check-label" for="page_none">
                                <span class="text-muted">No page (ads without page ads)</span>
                            </label>
                        </div>
                        @foreach($pages as $page)
                            <div class="form-check mb-2 border rounded p-3">
                                <input class="form-check-input" type="radio" name="fb_page_id"
                                       id="page_{{ $page['id'] }}" value="{{ $page['id'] }}"
                                       {{ $loop->first ? 'checked' : '' }}>
                                <label class="form-check-label" for="page_{{ $page['id'] }}">
                                    <strong>{{ $page['name'] }}</strong>
                                    <span class="text-muted ms-2">{{ $page['id'] }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                @endif

                <button type="submit" class="btn btn-primary" {{ empty($adAccounts) ? 'disabled' : '' }}>
                    <i class="ti ti-check"></i> Confirm Selection
                </button>
                <a href="{{ route('marketplace.vendor.meta-ads.connection') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
            </form>
        </div>
    </div>
@endsection
