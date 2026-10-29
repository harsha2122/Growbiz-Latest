<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Marketplace\Models\Store;
use Botble\Marketplace\Services\ProfileReviewService;
use Illuminate\Http\Request;

class ProfileReviewController extends BaseController
{
    public function store(int $store, Request $request, ProfileReviewService $service)
    {
        abort_unless(EcommerceHelper::isReviewEnabled(), 404);
        $data = $request->validate([
            'star' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:5000'],
        ]);
        $vendor = Store::query()->wherePublished()->where('is_minimal_profile', true)->findOrFail($store);
        $review = $service->submit($vendor, auth('customer')->user(), (int) $data['star'], $data['comment'] ?? null);

        return back()->with('profile_review_success', $review->status === 'pending'
            ? __('Your review was saved and is awaiting approval.')
            : __('Your rating has been saved.'));
    }
}
