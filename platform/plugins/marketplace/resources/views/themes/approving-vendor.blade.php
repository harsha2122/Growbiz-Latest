@extends(EcommerceHelper::viewPath('customers.master'))

@section('content')
    <h3 class="alert-heading">{{ SeoHelper::getTitle() }}</h3>

    <div class="alert alert-warning mb-0 mt-3" role="alert">
        <p class="mb-0">{{ __('Request under process. Once approved you can see the dashboard.') }}</p>
    </div>

    <div class="mt-4">
        <h5 class="mb-3">{{ __('Store Information') }}</h5>
        <ul class="list-group">
            <li class="list-group-item"><strong>{{ __('Store Name') }}:</strong> {{ $store->name }}</li>
            <li class="list-group-item"><strong>{{ __('Owner') }}:</strong> {{ $store->customer->name }}</li>
            <li class="list-group-item"><strong>{{ __('Phone') }}:</strong> {{ $store->phone }}</li>
            @if (MarketplaceHelper::getSetting('requires_vendor_documentations_verification', true))
                @if ($store->pan_card_file && Storage::disk('local')->exists($store->pan_card_file))
                    <li class="list-group-item">
                        <strong>{{ __('PAN Card') }}: </strong>
                        <a href="{{ route('marketplace.vendor.become-vendor.download', ['file' => 'pan_card']) }}" target="_blank" class="text-primary">{{ __('View Document') }}</a>
                    </li>
                @endif
                @if ($store->aadhar_card_file && Storage::disk('local')->exists($store->aadhar_card_file))
                    <li class="list-group-item">
                        <strong>{{ __('Aadhar Card') }}: </strong>
                        <a href="{{ route('marketplace.vendor.become-vendor.download', ['file' => 'aadhar_card']) }}" target="_blank" class="text-primary">{{ __('View Document') }}</a>
                    </li>
                @endif
                @if ($store->gst_certificate_file && Storage::disk('local')->exists($store->gst_certificate_file))
                    <li class="list-group-item">
                        <strong>{{ __('GST Certificate') }}: </strong>
                        <a href="{{ route('marketplace.vendor.become-vendor.download', ['file' => 'gst_certificate']) }}" target="_blank" class="text-primary">{{ __('View Document') }}</a>
                    </li>
                @endif
                @if ($store->udyam_aadhar_file && Storage::disk('local')->exists($store->udyam_aadhar_file))
                    <li class="list-group-item">
                        <strong>{{ __('Udyam Aadhar') }}: </strong>
                        <a href="{{ route('marketplace.vendor.become-vendor.download', ['file' => 'udyam_aadhar']) }}" target="_blank" class="text-primary">{{ __('View Document') }}</a>
                    </li>
                @endif
            @endif
        </ul>
    </div>
@endsection
