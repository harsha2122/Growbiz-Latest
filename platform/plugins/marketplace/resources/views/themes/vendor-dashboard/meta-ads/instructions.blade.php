@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <h4 class="mb-3">{{ __('Ad Instructions') }}</h4>
    @include('plugins/marketplace::themes.vendor-dashboard.meta-ads.partials.phone-guide', ['guideOpen' => true])
@endsection
