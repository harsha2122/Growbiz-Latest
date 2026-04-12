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
                        <strong>{{ __('Aadhaar (Front/PDF)') }}: </strong>
                        <a href="{{ route('marketplace.vendor.become-vendor.download', ['file' => 'aadhar_1']) }}" target="_blank" class="text-primary">{{ __('View Document') }}</a>
                    </li>
                @endif
                @if ($store->aadhar_file_2 && Storage::disk('local')->exists($store->aadhar_file_2))
                    <li class="list-group-item">
                        <strong>{{ __('Aadhaar (Back)') }}: </strong>
                        <a href="{{ route('marketplace.vendor.become-vendor.download', ['file' => 'aadhar_2']) }}" target="_blank" class="text-primary">{{ __('View Document') }}</a>
                    </li>
                @endif
                @if ($store->business_doc_file && Storage::disk('local')->exists($store->business_doc_file))
                    @php
                        $docTypeLabels = ['gst_certificate' => 'GST Certificate', 'shop_act' => 'Shop Act', 'udyam_aadhar' => 'Udyam Aadhaar'];
                        $docLabel = $store->business_doc_type ? ($docTypeLabels[$store->business_doc_type] ?? $store->business_doc_type) : 'Business Document';
                    @endphp
                    <li class="list-group-item">
                        <strong>{{ __($docLabel) }}: </strong>
                        <a href="{{ route('marketplace.vendor.become-vendor.download', ['file' => 'business_doc']) }}" target="_blank" class="text-primary">{{ __('View Document') }}</a>
                    </li>
                @endif
            @endif
        </ul>
    </div>
@endsection
