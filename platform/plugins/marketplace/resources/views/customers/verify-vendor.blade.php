@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="row row-cards">
        <div class="col-md-3">
            <x-core::card>
                <x-core::card.header>
                    <x-core::card.title>
                        {{ trans('plugins/marketplace::store.information') }}
                    </x-core::card.title>
                </x-core::card.header>

                <x-core::card.body class="p-0">
                    <div class="p-3 text-center">
                        <div class="mb-2">
                            <img
                                src="{{ RvMedia::getImageUrl($vendor->store->logo, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                alt="{{ $vendor->store->name }}"
                                class="avatar avatar-rounded avatar-xl"
                            />
                        </div>

                        @if ($vendor->store?->id)
                            <a href="{{ route('marketplace.store.edit', $vendor->store?->id) }}" target="_blank">
                                {{ $vendor->store->name }}
                                <x-core::icon name="ti ti-external-link" />
                            </a>
                        @endif
                    </div>

                    @if($vendor->store->phone)
                        <div class="hr my-3"></div>

                        <dl class="row p-3 pt-0">
                            <dt class="col">{{ trans('plugins/marketplace::store.store_phone') }}</dt>
                            <dd class="col-auto">{{ $vendor->store->phone }}</dd>
                        </dl>
                    @endif
                </x-core::card.body>
            </x-core::card>
        </div>
        <div class="col-md-9">
            <x-core::card>
                <x-core::card.header>
                    {{ trans('plugins/marketplace::store.vendor_information') }}
                </x-core::card.header>

                <x-core::card.body>
                    <x-core::datagrid>
                        <x-core::datagrid.item>
                            <x-slot:title>
                                {{ trans('plugins/marketplace::store.vendor_name') }}
                            </x-slot:title>

                            <a href="{{ route('customers.edit', $vendor->id) }}" target="_blank">
                                {{ $vendor->name }}
                                <x-core::icon name="ti ti-external-link" />
                            </a>
                        </x-core::datagrid.item>

                        <x-core::datagrid.item>
                            <x-slot:title>
                                {{ trans('plugins/marketplace::unverified-vendor.forms.email') }}
                            </x-slot:title>
                            {{ $vendor->email }}
                        </x-core::datagrid.item>

                        @if ($vendor->phone)
                            <x-core::datagrid.item>
                                <x-slot:title>
                                    {{ trans('plugins/marketplace::unverified-vendor.forms.vendor_phone') }}
                                </x-slot:title>
                                {{ $vendor->phone }}
                            </x-core::datagrid.item>
                        @endif

                        <x-core::datagrid.item>
                            <x-slot:title>
                                {{ trans('plugins/marketplace::unverified-vendor.forms.registered_at') }}
                            </x-slot:title>
                            {{ BaseHelper::formatDateTime($vendor->created_at) }}
                        </x-core::datagrid.item>

                        @if($vendor->store->pan_card_file && Storage::disk('local')->exists($vendor->store->pan_card_file))
                            <x-core::datagrid.item>
                                <x-slot:title>
                                    PAN Card
                                </x-slot:title>
                                <a href="{{ route('marketplace.unverified-vendors.download-document', [$vendor, 'pan_card']) }}" target="_blank">
                                    View PAN Card
                                </a>
                            </x-core::datagrid.item>
                        @endif

                        @if($vendor->store->aadhar_card_file && Storage::disk('local')->exists($vendor->store->aadhar_card_file))
                            <x-core::datagrid.item>
                                <x-slot:title>
                                    Aadhar Card
                                </x-slot:title>
                                <a href="{{ route('marketplace.unverified-vendors.download-document', [$vendor, 'aadhar_card']) }}" target="_blank">
                                    View Aadhar Card
                                </a>
                            </x-core::datagrid.item>
                        @endif

                        @if($vendor->store->gst_certificate_file && Storage::disk('local')->exists($vendor->store->gst_certificate_file))
                            <x-core::datagrid.item>
                                <x-slot:title>
                                    GST Certificate
                                </x-slot:title>
                                <a href="{{ route('marketplace.unverified-vendors.download-document', [$vendor, 'gst_certificate']) }}" target="_blank">
                                    View GST Certificate
                                </a>
                            </x-core::datagrid.item>
                        @endif

                        @if($vendor->store->udyam_aadhar_file && Storage::disk('local')->exists($vendor->store->udyam_aadhar_file))
                            <x-core::datagrid.item>
                                <x-slot:title>
                                    Udyam Aadhar
                                </x-slot:title>
                                <a href="{{ route('marketplace.unverified-vendors.download-document', [$vendor, 'udyam_aadhar']) }}" target="_blank">
                                    View Udyam Aadhar
                                </a>
                            </x-core::datagrid.item>
                        @endif
                    </x-core::datagrid>
                </x-core::card.body>

                <x-core::card.footer class="text-end">
                    <x-core::button
                        type="button"
                        data-bs-toggle="modal"
                        data-bs-target="#reject-vendor-modal"
                        icon="ti ti-x"
                    >
                        {{ trans('plugins/marketplace::unverified-vendor.reject') }}
                    </x-core::button>
                    <x-core::button
                        data-bs-toggle="modal"
                        data-bs-target="#approve-vendor-for-selling-modal"
                        type="button"
                        color="primary"
                        icon="ti ti-check"
                    >
                        {{ trans('plugins/marketplace::unverified-vendor.approve') }}
                    </x-core::button>
                </x-core::card.footer>
            </x-core::card>
        </div>
    </div>
@endsection

@push('footer')
    <x-core::modal.action
        id="approve-vendor-for-selling-modal"
        type="warning"
        :form-action="route('marketplace.unverified-vendors.approve-vendor', $vendor)"
        :title="trans('plugins/marketplace::unverified-vendor.approve_vendor_confirmation')"
        :description="trans('plugins/marketplace::unverified-vendor.approve_vendor_confirmation_description', [
            'vendor' => $vendor->name,
        ])"
        :submit-button-attrs="['id' => 'confirm-vendor-button']"
        :submit-button-label="trans('plugins/marketplace::unverified-vendor.approve')"
    />

    <x-core::modal.action
        id="reject-vendor-modal"
        type="danger"
        :form-action="route('marketplace.unverified-vendors.reject-vendor', $vendor)"
        :title="trans('plugins/marketplace::unverified-vendor.reject_vendor_confirmation')"
        :description="trans('plugins/marketplace::unverified-vendor.reject_vendor_confirmation_description', [
            'vendor' => $vendor->name,
        ])"
        :submit-button-attrs="['id' => 'confirm-vendor-button']"
        :submit-button-label="trans('plugins/marketplace::unverified-vendor.reject')"
    />
@endpush
