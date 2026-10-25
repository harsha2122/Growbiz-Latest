<?php

namespace Botble\Ecommerce\Traits;

use Botble\Ecommerce\Supports\ReviewEligibility;

trait CheckReviewConditionTrait
{
    protected function checkReviewCondition(int|string $productId, ?bool $writtenReview = null): array
    {
        return app(ReviewEligibility::class)->check(auth('customer')->id(), $productId, $writtenReview);
    }
}
