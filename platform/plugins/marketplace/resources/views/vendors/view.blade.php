@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="row row-cards">
        <div class="col-md-3">
            <x-core::card>
                <x-core::card.header>
                    <x-core::card.title>
                        {{ trans('plugins/marketplace::marketplace.vendor_information') }}
                    </x-core::card.title>
                </x-core::card.header>

                <x-core::card.body class="p-0">
                    <div class="text-center p-3">
                        <div class="mb-2">
                            <img
                                src="{{ $vendor->avatar_url ?: RvMedia::getDefaultImage() }}"
                                alt="{{ $vendor->name }}"
                                class="avatar avatar-rounded avatar-xl"
                            />
                        </div>

                        <h3 class="m-0">{{ $vendor->name }}</h3>
                        <p class="text-muted">{{ $vendor->email }}</p>

                        @if($vendor->phone)
                            <p class="text-muted mb-1">
                                <x-core::icon name="ti ti-phone" />
                                {{ $vendor->phone }}
                            </p>
                        @endif

                        <div class="mt-2">
                            @if($vendor->confirmed_at)
                                <span class="badge bg-green text-green-fg">
                                    <x-core::icon name="ti ti-check" />
                                    {{ trans('plugins/ecommerce::customer.email_verified') }}
                                </span>
                            @else
                                <span class="badge bg-yellow text-yellow-fg">
                                    <x-core::icon name="ti ti-alert-circle" />
                                    {{ trans('plugins/ecommerce::customer.email_not_verified') }}
                                </span>
                            @endif

                            @if($vendor->vendor_verified_at)
                                <span class="badge bg-green text-green-fg">
                                    <x-core::icon name="ti ti-shield-check" />
                                    {{ trans('plugins/marketplace::marketplace.vendor_verified') }}
                                </span>
                            @else
                                <span class="badge bg-cyan text-cyan-fg">
                                    <x-core::icon name="ti ti-shield-x" />
                                    {{ trans('plugins/marketplace::marketplace.vendor_not_verified') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="hr my-2"></div>

                    <div class="p-3">
                        <dl class="row">
                            <dt class="col">{{ trans('plugins/ecommerce::customer.status') }}</dt>
                            <dd class="col-auto">
                                {!! BaseHelper::clean($vendor->status->toHtml()) !!}
                            </dd>
                        </dl>

                        @if($vendor->dob)
                            <dl class="row">
                                <dt class="col">{{ trans('plugins/ecommerce::customer.dob') }}</dt>
                                <dd class="col-auto">{{ BaseHelper::formatDate($vendor->dob, 'Y-m-d') }}</dd>
                            </dl>
                        @endif

                        @if($vendor->created_at)
                            <dl class="row">
                                <dt class="col">{{ trans('core/base::tables.created_at') }}</dt>
                                <dd class="col-auto">{{ BaseHelper::formatDate($vendor->created_at, 'Y-m-d H:i') }}</dd>
                            </dl>
                        @endif

                        @if($vendor->vendor_verified_at)
                            <dl class="row">
                                <dt class="col">{{ trans('plugins/marketplace::marketplace.vendor_verified_at') }}</dt>
                                <dd class="col-auto">{{ BaseHelper::formatDate($vendor->vendor_verified_at, 'Y-m-d H:i') }}</dd>
                            </dl>
                        @endif

                        <dl class="row">
                            <dt class="col">{{ trans('plugins/ecommerce::customer.total_orders') }}</dt>
                            <dd class="col-auto">{{ number_format($totalOrders) }}</dd>
                        </dl>

                        <dl class="row">
                            <dt class="col">{{ trans('plugins/ecommerce::customer.total_spent') }}</dt>
                            <dd class="col-auto">{{ format_price($totalSpent) }}</dd>
                        </dl>
                    </div>

                    <div class="hr my-2"></div>

                    <div class="p-3">
                        <a href="{{ route('customers.edit', $vendor->id) }}" class="btn btn-primary w-100">
                            <x-core::icon name="ti ti-edit" />
                            {{ trans('plugins/ecommerce::customer.edit_action') }}
                        </a>
                    </div>
                </x-core::card.body>
            </x-core::card>

            @if($store)
                <x-core::card class="mt-3">
                    <x-core::card.header>
                        <x-core::card.title>
                            {{ trans('plugins/marketplace::store.information') }}
                        </x-core::card.title>
                    </x-core::card.header>

                    <x-core::card.body class="p-0">
                        <div class="p-3 text-center">
                            <div class="mb-2">
                                <img
                                    src="{{ RvMedia::getImageUrl($store->logo, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                    alt="{{ $store->name }}"
                                    class="avatar avatar-rounded avatar-xl"
                                />
                            </div>

                            <h3 class="m-0">
                                <a href="{{ route('marketplace.store.edit', $store->id) }}" target="_blank">
                                    {{ $store->name }}
                                    <x-core::icon name="ti ti-external-link" />
                                </a>
                            </h3>

                            @if($store->email)
                                <p class="text-muted mb-1">{{ $store->email }}</p>
                            @endif

                            @if($store->phone)
                                <p class="text-muted mb-1">
                                    <x-core::icon name="ti ti-phone" />
                                    {{ $store->phone }}
                                </p>
                            @endif

                            @if($store->is_verified)
                                <span class="badge bg-green text-green-fg">
                                    <x-core::icon name="ti ti-shield-check" />
                                    {{ trans('plugins/marketplace::store.verified') }}
                                </span>
                            @endif
                        </div>

                        @if($store->address || $store->city || $store->state || $store->country)
                            <div class="hr my-2"></div>
                            <div class="p-3">
                                <strong>{{ trans('plugins/marketplace::store.forms.address') }}</strong>
                                <p class="text-muted mb-0">
                                    {{ $store->address }}
                                    @if($store->city), {{ $store->city }}@endif
                                    @if($store->state), {{ $store->state }}@endif
                                    @if($store->country_name), {{ $store->country_name }}@endif
                                    @if($store->zip_code) {{ $store->zip_code }}@endif
                                </p>
                            </div>
                        @endif

                        <div class="hr my-2"></div>
                        <div class="p-3">
                            <strong>{{ trans('plugins/marketplace::marketplace.documents') }}</strong>
                            <div class="mt-2">
                                @php
                                    $hasAnyDocument = $store->aadhar_file_1 || $store->business_doc_file;
                                    $docTypeLabels = ['gst_certificate' => 'GST Certificate', 'shop_act' => 'Shop Act', 'udyam_aadhar' => 'Udyam Aadhaar'];
                                @endphp

                                @if(!$hasAnyDocument)
                                    <p class="text-muted small">No documents uploaded</p>
                                @else
                                    @if($store->aadhar_file_1)
                                        @if(Storage::disk('local')->exists($store->aadhar_file_1))
                                            <a href="{{ route('marketplace.vendors.download-document', [$vendor->id, 'aadhar_1']) }}"
                                               class="btn btn-sm btn-outline-primary w-100 mb-2"
                                               target="_blank">
                                                <x-core::icon name="ti ti-id" />
                                                View Aadhaar (Front/PDF)
                                            </a>
                                        @else
                                            <div class="alert alert-warning small mb-2 p-2">
                                                <x-core::icon name="ti ti-alert-triangle" />
                                                Aadhaar Front/PDF: File missing on disk
                                            </div>
                                        @endif
                                    @endif

                                    @if($store->aadhar_file_2)
                                        @if(Storage::disk('local')->exists($store->aadhar_file_2))
                                            <a href="{{ route('marketplace.vendors.download-document', [$vendor->id, 'aadhar_2']) }}"
                                               class="btn btn-sm btn-outline-primary w-100 mb-2"
                                               target="_blank">
                                                <x-core::icon name="ti ti-id" />
                                                View Aadhaar (Back)
                                            </a>
                                        @else
                                            <div class="alert alert-warning small mb-2 p-2">
                                                <x-core::icon name="ti ti-alert-triangle" />
                                                Aadhaar Back: File missing on disk
                                            </div>
                                        @endif
                                    @endif

                                    @if($store->business_doc_file)
                                        @if(Storage::disk('local')->exists($store->business_doc_file))
                                            <a href="{{ route('marketplace.vendors.download-document', [$vendor->id, 'business_doc']) }}"
                                               class="btn btn-sm btn-outline-primary w-100 mb-2"
                                               target="_blank">
                                                <x-core::icon name="ti ti-file-certificate" />
                                                View Business Doc
                                                @if($store->business_doc_type)
                                                    ({{ $docTypeLabels[$store->business_doc_type] ?? $store->business_doc_type }})
                                                @endif
                                            </a>
                                        @else
                                            <div class="alert alert-warning small mb-2 p-2">
                                                <x-core::icon name="ti ti-alert-triangle" />
                                                Business Document: File missing on disk
                                            </div>
                                        @endif
                                    @endif
                                @endif
                            </div>
                        </div>

                        @if($store->tax_id)
                            <div class="hr my-2"></div>
                            <div class="p-3">
                                <dl class="row mb-0">
                                    <dt class="col">{{ trans('plugins/marketplace::store.tax_id') }}</dt>
                                    <dd class="col-auto">{{ $store->tax_id }}</dd>
                                </dl>
                            </div>
                        @endif
                    </x-core::card.body>
                </x-core::card>

                @if($subscriptionStatus)
                    <x-core::card class="mt-3">
                        <x-core::card.header>
                            <x-core::card.title>
                                {{ __('Subscription Plan') }}
                            </x-core::card.title>
                        </x-core::card.header>

                        <x-core::card.body class="p-0">
                            <div class="p-3">
                                @if($subscriptionStatus['has_subscription'])
                                    <dl class="row mb-2">
                                        <dt class="col">{{ __('Current Plan') }}</dt>
                                        <dd class="col-auto">
                                            <span class="badge bg-primary text-primary-fg">{{ $subscriptionStatus['plan_name'] }}</span>
                                        </dd>
                                    </dl>

                                    <dl class="row mb-2">
                                        <dt class="col">{{ __('Products') }}</dt>
                                        <dd class="col-auto">
                                            <strong>{{ $subscriptionStatus['products_used'] }}</strong>
                                            /
                                            @if($subscriptionStatus['products_limit'] == 0)
                                                <span class="text-success">{{ __('Unlimited') }}</span>
                                            @else
                                                {{ $subscriptionStatus['products_limit'] }}
                                            @endif
                                        </dd>
                                    </dl>

                                    <dl class="row mb-2">
                                        <dt class="col">{{ __('Valid Till') }}</dt>
                                        <dd class="col-auto">
                                            @if($subscriptionStatus['expires_at'])
                                                @if($subscriptionStatus['is_expired'])
                                                    <span class="text-danger">
                                                        {{ $subscriptionStatus['expires_at']->format('Y-m-d') }}
                                                        <br><small>({{ __('Expired') }})</small>
                                                    </span>
                                                @elseif($subscriptionStatus['days_remaining'] <= 7)
                                                    <span class="text-warning">
                                                        {{ $subscriptionStatus['expires_at']->format('Y-m-d') }}
                                                        <br><small>({{ $subscriptionStatus['days_remaining'] }} {{ __('days left') }})</small>
                                                    </span>
                                                @else
                                                    <span class="text-success">
                                                        {{ $subscriptionStatus['expires_at']->format('Y-m-d') }}
                                                        <br><small>({{ $subscriptionStatus['days_remaining'] }} {{ __('days left') }})</small>
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-success">{{ __('Never expires') }}</span>
                                            @endif
                                        </dd>
                                    </dl>

                                    @if($subscriptionStatus['products_limit'] > 0)
                                        <div class="progress mt-2" style="height: 6px;">
                                            @php
                                                $percentage = min(100, ($subscriptionStatus['products_used'] / $subscriptionStatus['products_limit']) * 100);
                                                $progressClass = $percentage >= 90 ? 'bg-danger' : ($percentage >= 70 ? 'bg-warning' : 'bg-success');
                                            @endphp
                                            <div class="progress-bar {{ $progressClass }}" role="progressbar" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <small class="text-muted">
                                            {{ $subscriptionStatus['products_remaining'] }} {{ __('products remaining') }}
                                        </small>
                                    @endif
                                @else
                                    <div class="alert alert-warning mb-0">
                                        <x-core::icon name="ti ti-alert-circle" />
                                        {{ __('No active subscription plan') }}
                                    </div>
                                @endif
                            </div>
                        </x-core::card.body>
                    </x-core::card>
                @endif
            @endif

            @if($vendor->addresses->count() > 0)
                <x-core::card class="mt-3">
                    <x-core::card.header>
                        <x-core::card.title>
                            {{ trans('plugins/ecommerce::customer.addresses') }}
                        </x-core::card.title>
                    </x-core::card.header>

                    <x-core::card.body class="p-0">
                        <div class="list-group list-group-flush">
                            @foreach($vendor->addresses as $address)
                                <div class="list-group-item">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-truncate">
                                                <strong>{{ $address->name }}</strong>
                                                @if($address->is_default)
                                                    <span class="badge bg-blue text-blue-fg ms-1">{{ trans('plugins/ecommerce::customer.default') }}</span>
                                                @endif
                                            </div>
                                            <div class="text-muted text-truncate mt-1">
                                                {{ $address->full_address }}
                                            </div>
                                            @if($address->phone)
                                                <div class="text-muted mt-1">
                                                    <x-core::icon name="ti ti-phone" />
                                                    {{ $address->phone }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-core::card.body>
                </x-core::card>
            @endif
        </div>

        <div class="col-md-9">
            <!-- Vendor Statistics -->
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-3">
                    <x-core::card>
                        <x-core::card.body>
                            <div class="d-flex align-items-center">
                                <div class="subheader">{{ trans('plugins/marketplace::marketplace.store_products') }}</div>
                            </div>
                            <div class="h1 mb-0">{{ number_format($storeProducts) }}</div>
                        </x-core::card.body>
                    </x-core::card>
                </div>

                <div class="col-md-3 col-sm-6 mb-3">
                    <x-core::card>
                        <x-core::card.body>
                            <div class="d-flex align-items-center">
                                <div class="subheader">{{ trans('plugins/marketplace::marketplace.store_orders') }}</div>
                            </div>
                            <div class="h1 mb-0">{{ number_format($storeOrders) }}</div>
                        </x-core::card.body>
                    </x-core::card>
                </div>

                <div class="col-md-3 col-sm-6 mb-3">
                    <x-core::card>
                        <x-core::card.body>
                            <div class="d-flex align-items-center">
                                <div class="subheader">{{ trans('plugins/marketplace::marketplace.total_revenue') }}</div>
                            </div>
                            <div class="h1 mb-0">{{ format_price($totalRevenue) }}</div>
                        </x-core::card.body>
                    </x-core::card>
                </div>

                <div class="col-md-3 col-sm-6 mb-3">
                    <x-core::card>
                        <x-core::card.body>
                            <div class="d-flex align-items-center">
                                <div class="subheader">{{ trans('plugins/marketplace::marketplace.total_earnings') }}</div>
                            </div>
                            <div class="h1 mb-0">{{ format_price($totalEarnings) }}</div>
                        </x-core::card.body>
                    </x-core::card>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 col-sm-6 mb-3">
                    <x-core::card>
                        <x-core::card.body>
                            <div class="d-flex align-items-center">
                                <div class="subheader">{{ trans('plugins/marketplace::marketplace.withdrawals') }}</div>
                            </div>
                            <div class="h1 mb-0">{{ format_price($totalWithdrawals) }}</div>
                        </x-core::card.body>
                    </x-core::card>
                </div>

                <div class="col-md-3 col-sm-6 mb-3">
                    <x-core::card>
                        <x-core::card.body>
                            <div class="d-flex align-items-center">
                                <div class="subheader">{{ trans('plugins/marketplace::marketplace.pending_withdrawals') }}</div>
                            </div>
                            <div class="h1 mb-0">{{ format_price($pendingWithdrawals) }}</div>
                        </x-core::card.body>
                    </x-core::card>
                </div>

                <div class="col-md-3 col-sm-6 mb-3">
                    <x-core::card>
                        <x-core::card.body>
                            <div class="d-flex align-items-center">
                                <div class="subheader">{{ trans('plugins/marketplace::marketplace.balance') }}</div>
                            </div>
                            <div class="h1 mb-0">{{ format_price($balance) }}</div>
                        </x-core::card.body>
                    </x-core::card>
                </div>

                <div class="col-md-3 col-sm-6 mb-3">
                    <x-core::card>
                        <x-core::card.body>
                            <div class="d-flex align-items-center">
                                <div class="subheader">{{ trans('plugins/ecommerce::customer.completed_orders') }}</div>
                            </div>
                            <div class="h1 mb-0">{{ number_format($completedOrders) }}</div>
                        </x-core::card.body>
                    </x-core::card>
                </div>
            </div>

            @if($store && $store->products()->count() > 0)
                <x-core::card>
                    <x-core::card.header>
                        <x-core::card.title>
                            {{ trans('plugins/marketplace::marketplace.recent_products') }}
                        </x-core::card.title>
                    </x-core::card.header>

                    <x-core::table>
                        <x-core::table.header>
                            <x-core::table.header.cell>
                                {{ trans('plugins/ecommerce::products.form.product') }}
                            </x-core::table.header.cell>
                            <x-core::table.header.cell>
                                {{ trans('plugins/ecommerce::products.form.price') }}
                            </x-core::table.header.cell>
                            <x-core::table.header.cell>
                                {{ trans('plugins/ecommerce::products.form.quantity') }}
                            </x-core::table.header.cell>
                            <x-core::table.header.cell>
                                {{ trans('core/base::tables.status') }}
                            </x-core::table.header.cell>
                            <x-core::table.header.cell>
                                {{ trans('core/base::tables.created_at') }}
                            </x-core::table.header.cell>
                            <x-core::table.header.cell></x-core::table.header.cell>
                        </x-core::table.header>
                        <x-core::table.body>
                            @foreach($store->products()->latest()->limit(10)->get() as $product)
                                <x-core::table.body.row>
                                    <x-core::table.body.cell>
                                        <div class="d-flex align-items-center">
                                            <img
                                                src="{{ RvMedia::getImageUrl($product->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                alt="{{ $product->name }}"
                                                class="me-2 rounded"
                                                style="width: 40px; height: 40px; object-fit: cover;"
                                            />
                                            <div>
                                                <a href="{{ route('products.edit', $product->id) }}" target="_blank">
                                                    {{ $product->name }}
                                                </a>
                                            </div>
                                        </div>
                                    </x-core::table.body.cell>
                                    <x-core::table.body.cell>
                                        {{ format_price($product->price) }}
                                    </x-core::table.body.cell>
                                    <x-core::table.body.cell>
                                        {{ $product->quantity ?: '—' }}
                                    </x-core::table.body.cell>
                                    <x-core::table.body.cell>
                                        {!! BaseHelper::clean($product->status->toHtml()) !!}
                                    </x-core::table.body.cell>
                                    <x-core::table.body.cell>
                                        {{ $product->created_at ? BaseHelper::formatDate($product->created_at, 'Y-m-d') : '—' }}
                                    </x-core::table.body.cell>
                                    <x-core::table.body.cell>
                                        <a href="{{ route('products.edit', $product->id) }}" class="text-decoration-none" target="_blank">
                                            {{ trans('core/base::tables.view') }}
                                        </a>
                                    </x-core::table.body.cell>
                                </x-core::table.body.row>
                            @endforeach
                        </x-core::table.body>
                    </x-core::table>
                </x-core::card>
            @endif

            @if($totalOrders > 0)
                <x-core::card class="mt-3">
                    <x-core::card.header>
                        <x-core::card.title>
                            {{ trans('plugins/ecommerce::customer.recent_orders') }}
                        </x-core::card.title>
                    </x-core::card.header>

                    <x-core::table>
                        <x-core::table.header>
                            <x-core::table.header.cell>
                                {{ trans('plugins/ecommerce::order.order_id') }}
                            </x-core::table.header.cell>
                            <x-core::table.header.cell>
                                {{ trans('plugins/ecommerce::order.created_at') }}
                            </x-core::table.header.cell>
                            <x-core::table.header.cell>
                                {{ trans('plugins/ecommerce::order.amount') }}
                            </x-core::table.header.cell>
                            <x-core::table.header.cell>
                                {{ trans('plugins/ecommerce::order.payment_method') }}
                            </x-core::table.header.cell>
                            <x-core::table.header.cell>
                                {{ trans('plugins/ecommerce::order.status') }}
                            </x-core::table.header.cell>
                            <x-core::table.header.cell></x-core::table.header.cell>
                        </x-core::table.header>
                        <x-core::table.body>
                            @foreach($vendor->orders()->latest()->limit(10)->get() as $order)
                                <x-core::table.body.row>
                                    <x-core::table.body.cell>
                                        <a href="{{ route('orders.edit', $order->id) }}">
                                            {{ $order->code }}
                                        </a>
                                    </x-core::table.body.cell>
                                    <x-core::table.body.cell>
                                        {{ $order->created_at ? BaseHelper::formatDate($order->created_at, 'Y-m-d H:i') : '—' }}
                                    </x-core::table.body.cell>
                                    <x-core::table.body.cell>
                                        {{ format_price($order->amount) }}
                                    </x-core::table.body.cell>
                                    <x-core::table.body.cell>
                                        {{ $order->payment->payment_channel->label() ?? '—' }}
                                    </x-core::table.body.cell>
                                    <x-core::table.body.cell>
                                        {!! BaseHelper::clean($order->status->toHtml()) !!}
                                    </x-core::table.body.cell>
                                    <x-core::table.body.cell>
                                        <a href="{{ route('orders.edit', $order->id) }}" class="text-decoration-none">
                                            {{ trans('core/base::tables.view') }}
                                        </a>
                                    </x-core::table.body.cell>
                                </x-core::table.body.row>
                            @endforeach
                        </x-core::table.body>
                    </x-core::table>
                </x-core::card>
            @endif

            @if($vendor->reviews->count() > 0)
                <x-core::card class="mt-3">
                    <x-core::card.header>
                        <x-core::card.title>
                            {{ trans('plugins/ecommerce::customer.recent_reviews') }} ({{ $vendor->reviews->count() }})
                        </x-core::card.title>
                    </x-core::card.header>

                    <x-core::card.body>
                        @foreach($vendor->reviews()->latest()->limit(5)->get() as $review)
                            <div class="mb-3 pb-3 border-bottom">
                                <div class="d-flex align-items-start">
                                    <img
                                        src="{{ RvMedia::getImageUrl($review->product->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                        alt="{{ $review->product->name }}"
                                        class="me-3 rounded"
                                        style="width: 50px; height: 50px; object-fit: cover;"
                                    />
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <a href="{{ route('products.edit', $review->product_id) }}" target="_blank" class="text-decoration-none">
                                                    <strong>{{ $review->product->name }}</strong>
                                                </a>
                                                <div class="text-warning">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        @if($i <= $review->star)
                                                            <x-core::icon name="ti ti-star-filled" />
                                                        @else
                                                            <x-core::icon name="ti ti-star" />
                                                        @endif
                                                    @endfor
                                                    <span class="text-muted ms-1">({{ $review->star }}/5)</span>
                                                </div>
                                            </div>
                                            <div class="text-muted small">
                                                {{ $review->created_at ? $review->created_at->diffForHumans() : '' }}
                                            </div>
                                        </div>
                                        @if($review->comment)
                                            <p class="mb-0 mt-2">{{ $review->comment }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </x-core::card.body>
                </x-core::card>
            @endif
        </div>

        {{-- Referrals --}}
        @if ($store)
            @php $storeReferrals = $store->referrals()->with('referee:id,name,email')->latest('joined_at')->get(); @endphp
            <div class="col-12 mt-4">
                <x-core::card>
                    <x-core::card.header>
                        <x-core::card.title>
                            {{ __('Referrals') }}
                            <span class="badge bg-primary ms-2">{{ $storeReferrals->count() }}</span>
                        </x-core::card.title>
                    </x-core::card.header>
                    <x-core::card.body>
                        <p class="mb-2"><strong>{{ __('Referral Code') }}:</strong>
                            <code>{{ $store->referral_code ?? '—' }}</code>
                        </p>
                        @if ($storeReferrals->isEmpty())
                            <p class="text-muted mb-0">{{ __('No referrals yet.') }}</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Email') }}</th>
                                            <th>{{ __('Joined') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($storeReferrals as $i => $referral)
                                            <tr>
                                                <td>{{ $i + 1 }}</td>
                                                <td>{{ $referral->referee->name ?? '—' }}</td>
                                                <td>{{ $referral->referee->email ?? '—' }}</td>
                                                <td>{{ $referral->joined_at->format('d M Y') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </x-core::card.body>
                </x-core::card>
            </div>
        @endif
    </div>
@endsection
