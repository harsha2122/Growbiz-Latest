<?php

namespace Botble\Ecommerce\Traits;

use Botble\Ecommerce\Supports\ReviewEligibility;
use Illuminate\Http\Request;

trait CheckReviewConditionForApiTrait
{
    protected function checkReviewCondition(int|string $productId, Request $request, ?bool $writtenReview = null): array
    {
        return app(ReviewEligibility::class)->check($request->user()?->id, $productId, $writtenReview);
    }
}
