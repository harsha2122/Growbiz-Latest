<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Facades\Assets;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Customer;
use Botble\Marketplace\Models\ServiceCompletion;
use Botble\Marketplace\Tables\ReviewTable;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReviewController extends BaseController
{
    public function index(ReviewTable $table)
    {
        $this->pageTitle(__('Reviews'));

        $store = auth('customer')->user()->store;
        abort_unless($store?->exists, 404);
        if ($store->is_minimal_profile) {
            $reviews = $store->profileReviews()->with('customer:id,name')->latest()->paginate(20);
            $summary = $store->publicRatingSummary();

            $services = ServiceCompletion::where('store_id', $store->id)->with('customer:id,name,email')->latest()->paginate(10, ['*'], 'services');

            return \Botble\Marketplace\Facades\MarketplaceHelper::view('vendor-dashboard.profile-reviews', compact('reviews', 'summary', 'services'));
        }

        abort_unless(EcommerceHelper::isReviewEnabled(), 404);
        Assets::addStylesDirectly('vendor/core/plugins/ecommerce/css/review.css');

        return $table->renderTable();
    }

    public function confirmService(Request $request)
    {
        $store = auth('customer')->user()->store;
        abort_unless($store?->exists && $store->is_minimal_profile, 403);
        $data = $request->validate([
            'customer_email' => ['required', 'email', 'max:191'],
            'completed_at' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'reference' => ['required', 'string', 'max:191'],
        ]);
        $customer = Customer::query()->where('email', $data['customer_email'])->whereNotNull('confirmed_at')->first();
        if (! $customer || $customer->id == auth('customer')->id()) {
            throw ValidationException::withMessages(['customer_email' => __('Enter the verified account email of the customer who received your service. You cannot confirm your own service.')]);
        }
        \Illuminate\Support\Facades\DB::transaction(function () use ($store, $customer, $data): void {
            \Botble\Marketplace\Models\Store::query()->whereKey($store->id)->lockForUpdate()->firstOrFail();
            ServiceCompletion::firstOrCreate([
                'store_id' => $store->id, 'customer_id' => $customer->id, 'reference' => $data['reference'], 'product_id' => null,
            ], [
                'completed_at' => $data['completed_at'], 'verified_by' => 0, 'verified_by_seller' => auth('customer')->id(),
            ]);
        });

        return back()->with('success_msg', __('Service confirmed. This customer can now write a review on your profile.'));
    }
}
