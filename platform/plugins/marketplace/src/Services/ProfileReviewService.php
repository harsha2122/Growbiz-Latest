<?php

namespace Botble\Marketplace\Services;

use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Botble\Marketplace\Models\ProfileReview;
use Botble\Marketplace\Models\ServiceCompletion;
use Botble\Marketplace\Models\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProfileReviewService
{
    public function canWrite(int $customerId, int $storeId): bool
    {
        return Order::query()->where('user_id', $customerId)->where('store_id', $storeId)->where('status', 'completed')->exists()
            || ServiceCompletion::query()->where('customer_id', $customerId)->where('store_id', $storeId)
                ->whereDate('completed_at', '<=', today())->exists();
    }

    public function submit(Store $store, Customer $customer, int $star, ?string $comment): ProfileReview
    {
        return DB::transaction(function () use ($store, $customer, $star, $comment) {
            // Serializes simultaneous submissions by the same customer.
            Customer::query()->whereKey($customer->id)->lockForUpdate()->firstOrFail();
            $store = Store::query()->whereKey($store->id)->wherePublished()->where('is_minimal_profile', true)->firstOrFail();
            abort_if($store->customer_id == $customer->id, 403, __('You cannot rate your own store.'));
            $comment = trim((string) $comment);
            if ($comment !== '' && ! $this->canWrite($customer->id, $store->id)) {
                throw ValidationException::withMessages(['comment' => __('A completed purchase or admin-confirmed service from this vendor is required for a written review. You can submit stars without a purchase.')]);
            }
            $review = ProfileReview::query()->firstOrNew(['store_id' => $store->id, 'customer_id' => $customer->id]);
            $review->star = $star;
            // A stars-only update must not erase an earlier written review.
            if ($comment !== '' || ! $review->exists) {
                $review->comment = $comment ?: null;
            }
            $review->status = $comment !== '' && get_ecommerce_setting('review_need_to_be_approved', false) ? 'pending' : ($review->status ?: 'published');
            $review->save();

            return $review;
        }, 3);
    }
}
