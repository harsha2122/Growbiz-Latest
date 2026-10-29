<?php

namespace Botble\Marketplace\Http\Controllers;

use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Product;
use Botble\Marketplace\Models\ProfileReview;
use Botble\Marketplace\Models\ServiceCompletion;
use Botble\Marketplace\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProfileReviewController extends BaseController
{
    public function index()
    {
        $this->pageTitle(__('Profile Reviews & Services'));
        $reviews = ProfileReview::query()->with(['store', 'customer'])->latest()->paginate(20);
        $services = ServiceCompletion::query()->with(['store', 'customer'])->latest()->paginate(10, ['*'], 'services');
        $stores = Store::query()->orderBy('name')->pluck('name', 'id');
        $products = Product::query()->where('product_type', 'service')->where('is_variation', 0)->with('store')->orderBy('name')->get(['id', 'store_id', 'name']);

        return view('plugins/marketplace::profile-reviews.admin', compact('reviews', 'services', 'stores', 'products'));
    }

    public function moderate(int $review, Request $request)
    {
        $data = $request->validate(['status' => ['required', Rule::in(['published', 'pending', 'rejected'])]]);
        ProfileReview::findOrFail($review)->update($data);

        return back()->with('success_msg', __('Review status updated.'));
    }

    public function confirmService(Request $request)
    {
        $data = $request->validate([
            'store_id' => ['required', 'integer', 'exists:mp_stores,id'],
            'customer_email' => ['required', 'email', 'exists:ec_customers,email'],
            'product_id' => ['nullable', 'integer', 'exists:ec_products,id'],
            'completed_at' => ['required', 'date', 'before_or_equal:today'],
            'reference' => ['required', 'string', 'max:191'],
        ]);
        $store = Store::findOrFail($data['store_id']);
        $customer = Customer::query()->where('email', $data['customer_email'])->firstOrFail();
        if ($store->customer_id == $customer->id) {
            throw ValidationException::withMessages(['customer_email' => __('A seller cannot verify their own service experience.')]);
        }
        if (! empty($data['product_id'])) {
            $product = Product::query()->where('store_id', $store->id)->where('product_type', 'service')->find($data['product_id']);
            if (! $product) {
                throw ValidationException::withMessages(['product_id' => __('Choose a service belonging to this vendor.')]);
            }
        }
        ServiceCompletion::create([
            'store_id' => $store->id, 'customer_id' => $customer->id,
            'product_id' => $data['product_id'] ?? null, 'completed_at' => $data['completed_at'],
            'reference' => $data['reference'], 'verified_by' => auth()->id(),
        ]);

        return back()->with('success_msg', __('Completed service confirmed. This customer can now write a review for the profile and the selected service.'));
    }

    public function revokeService(int $service)
    {
        ServiceCompletion::findOrFail($service)->delete();

        return back()->with('success_msg', __('Service confirmation removed.'));
    }
}
