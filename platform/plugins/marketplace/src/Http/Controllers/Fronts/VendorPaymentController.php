<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Enums\VendorPaymentStatusEnum;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Http\Requests\VendorPaymentRequest;
use Botble\Marketplace\Models\VendorPayment;

class VendorPaymentController extends BaseController
{
    public function index()
    {
        $this->pageTitle('Payments');

        $store = auth('customer')->user()?->store;

        $payments = $store
            ? VendorPayment::query()->where('store_id', $store->id)->latest()->paginate(20)
            : null;

        $qrCode = MarketplaceHelper::paymentQrCode();

        return MarketplaceHelper::view('vendor-dashboard.payments.index', compact('payments', 'qrCode'));
    }

    public function store(VendorPaymentRequest $request)
    {
        $store = auth('customer')->user()?->store;

        abort_if(! $store, 404);

        VendorPayment::query()->create([
            ...$request->validated(),
            'store_id' => $store->id,
            'status' => VendorPaymentStatusEnum::PENDING,
        ]);

        return $this
            ->httpResponse()
            ->setNextUrl(route('marketplace.vendor.payments.index'))
            ->setMessage(__('Your payment has been submitted and is pending admin approval.'));
    }
}
