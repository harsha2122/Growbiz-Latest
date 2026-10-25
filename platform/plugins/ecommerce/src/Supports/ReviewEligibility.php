<?php

namespace Botble\Ecommerce\Supports;

use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Review;

class ReviewEligibility
{
    // A null submission type evaluates both options for rendering the form.
    public function check(int|string|null $customerId, int|string $productId, ?bool $writtenReview = null): array
    {
        if (! $customerId) {
            return $this->denied('login_required', __('Please login to submit a review or rating.'));
        }

        if (Review::hasUserReviewed($customerId, $productId)) {
            return $this->denied('already_reviewed', __('You have reviewed this product already!'));
        }

        $reviewRequiresPurchase = EcommerceHelper::onlyAllowCustomersPurchasedToReview();
        $ratingRequiresPurchase = EcommerceHelper::onlyAllowCustomersPurchasedToRate();
        $purchased = false;

        if ($reviewRequiresPurchase || $ratingRequiresPurchase) {
            $purchased = Order::query()
                ->where('ec_orders.user_id', $customerId)
                ->where('ec_orders.status', OrderStatusEnum::COMPLETED)
                ->join('ec_order_product', 'ec_order_product.order_id', '=', 'ec_orders.id')
                ->leftJoin('ec_product_variations', 'ec_product_variations.product_id', '=', 'ec_order_product.product_id')
                ->where(function ($query) use ($productId): void {
                    $query->where('ec_order_product.product_id', $productId)
                        ->orWhere('ec_product_variations.configurable_product_id', $productId);
                })
                ->exists();
        }

        $canReview = ! $reviewRequiresPurchase || $purchased;
        $canRate = ! $ratingRequiresPurchase || $purchased;
        $allowed = $writtenReview === null ? ($canReview || $canRate) : ($writtenReview ? $canReview : $canRate);

        return [
            'error' => ! $allowed,
            'type' => $allowed ? null : 'purchase_required',
            'message' => $allowed ? '' : ($writtenReview === false
                ? __('Please purchase the product to submit a rating.')
                : __('Please purchase the product for a review!')),
            'can_review' => $canReview,
            'can_rate' => $canRate,
        ];
    }

    protected function denied(string $type, string $message): array
    {
        return ['error' => true, 'type' => $type, 'message' => $message, 'can_review' => false, 'can_rate' => false];
    }
}
