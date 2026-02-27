@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <h4>{{ __('Meta Ads – Platform Summary') }}</h4>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <div class="fs-2 fw-bold text-primary">{{ number_format($connectedAccounts) }}</div>
                        <small class="text-muted">{{ __('Connected Accounts') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <div class="fs-2 fw-bold">{{ number_format($totalCampaigns) }}</div>
                        <small class="text-muted">{{ __('Total Campaigns') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <div class="fs-2 fw-bold text-success">{{ number_format($activeCampaigns) }}</div>
                        <small class="text-muted">{{ __('Active Campaigns') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <div class="fs-2 fw-bold">{{ number_format($totalImpressions) }}</div>
                        <small class="text-muted">{{ __('Total Impressions') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <div class="fs-2 fw-bold">{{ number_format($totalClicks) }}</div>
                        <small class="text-muted">{{ __('Total Clicks') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <div class="fs-2 fw-bold text-danger">${{ number_format($totalSpend, 2) }}</div>
                        <small class="text-muted">{{ __('Total Spend') }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('marketplace.meta-ads.index') }}" class="btn btn-outline-primary btn-sm">
                {{ __('View All Campaigns') }}
            </a>
            <a href="{{ route('marketplace.meta-ads.accounts') }}" class="btn btn-outline-secondary btn-sm">
                {{ __('View Ad Accounts') }}
            </a>
        </div>
    </div>
@endsection
