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
                @if ($store->aadhar_file_1 && Storage::disk('local')->exists($store->aadhar_file_1))
                    <li class="list-group-item">
                        <strong>{{ __('Aadhaar Card') }}: </strong>
                        <a href="{{ route('marketplace.vendor.become-vendor.download', ['file' => 'aadhar_1']) }}" target="_blank" class="text-primary">{{ __('View Side 1') }}</a>
                        @if ($store->aadhar_file_2 && Storage::disk('local')->exists($store->aadhar_file_2))
                            &nbsp;|&nbsp;
                            <a href="{{ route('marketplace.vendor.become-vendor.download', ['file' => 'aadhar_2']) }}" target="_blank" class="text-primary">{{ __('View Side 2') }}</a>
                        @endif
                    </li>
                @endif
                @if ($store->business_doc_file && Storage::disk('local')->exists($store->business_doc_file))
                    <li class="list-group-item">
                        <strong>{{ __('Business Document') }}
                            @if($store->business_doc_type)
                                ({{ match($store->business_doc_type) { 'gst_certificate' => 'GST Certificate', 'shop_act' => 'Shop Act', 'udyam_aadhar' => 'Udyam Aadhaar', default => $store->business_doc_type } }})
                            @endif
                        : </strong>
                        <a href="{{ route('marketplace.vendor.become-vendor.download', ['file' => 'business_doc']) }}" target="_blank" class="text-primary">{{ __('View Document') }}</a>
                    </li>
                @endif
            @endif
        </ul>
    </div>
@endsection
