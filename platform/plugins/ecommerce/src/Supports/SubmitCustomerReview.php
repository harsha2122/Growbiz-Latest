<?php

namespace Botble\Ecommerce\Supports;

use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Review;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubmitCustomerReview
{
    public function save(int $customerId, int $productId, array $attributes): Review
    {
        return DB::transaction(function () use ($customerId, $productId, $attributes) {
            Customer::query()->whereKey($customerId)->lockForUpdate()->firstOrFail();
            $attributes['comment'] = trim((string) ($attributes['comment'] ?? ''));
            $written = $attributes['comment'] !== '' || ! empty($attributes['images']);
            $eligibility = app(ReviewEligibility::class)->check($customerId, $productId, $written);
            if ($eligibility['error']) {
                throw ValidationException::withMessages(['product_id' => $eligibility['message']]);
            }
            $review = Review::query()->firstOrNew(['customer_id' => $customerId, 'product_id' => $productId]);
            $review->fill($attributes);
            $review->customer_id = $customerId;
            $review->product_id = $productId;
            $review->save();

            return $review;
        }, 3);
    }
}
