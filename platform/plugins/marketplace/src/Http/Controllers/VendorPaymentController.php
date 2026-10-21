<?php

namespace Botble\Marketplace\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Enums\VendorPaymentStatusEnum;
use Botble\Marketplace\Http\Requests\VendorPaymentReviewRequest;
use Botble\Marketplace\Models\VendorPayment;
use Botble\Marketplace\Tables\VendorPaymentTable;

class VendorPaymentController extends BaseController
{
    public function index(VendorPaymentTable $table)
    {
        $this->pageTitle('Vendor Payments');

        return $table->renderTable();
    }

    public function review(int|string $id, VendorPaymentReviewRequest $request)
    {
        $payment = VendorPayment::query()->findOrFail($id);

        abort_if(! $payment->isPending(), 400, __('This payment request has already been reviewed.'));

        $status = $request->input('status');

        $payment->status = $status;
        $payment->remarks = $request->input('remarks');
        $payment->reviewed_by = auth()->id();
        $payment->reviewed_at = now();
        $payment->save();

        $message = $status === VendorPaymentStatusEnum::APPROVED
            ? __('Payment approved and added to the vendor\'s record.')
            : __('Payment declined.');

        return $this->httpResponse()->setMessage($message);
    }
}
