<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Models\Store;
use Botble\Marketplace\Services\ProfileReviewService;
use Illuminate\Http\Request;

class ProfileReviewController extends BaseController
{
    public function start(int $store, Request $request)
    {
        $vendor = Store::query()->wherePublished()->where('is_minimal_profile', true)->findOrFail($store);
        $data = $request->validate(['star' => ['required', 'integer', 'min:1', 'max:5']]);
        $request->session()->put('profile_rating_drafts.'.$vendor->id, (int) $data['star']);
        $target = $vendor->url.'#profile-reviews';
        if (auth('customer')->check()) {
            return redirect()->to($target);
        }
        $request->session()->put('profile_rating_return', $target);
        $request->session()->put('url.intended', $target);

        return redirect()->route('customer.login');
    }

    public function store(int $store, Request $request, ProfileReviewService $service)
    {
        $data = $request->validate([
            'star' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:5000'],
        ]);
        $vendor = Store::query()->wherePublished()->where('is_minimal_profile', true)->findOrFail($store);
        $review = $service->submit($vendor, auth('customer')->user(), (int) $data['star'], $data['comment'] ?? null);

        $request->session()->forget('profile_rating_drafts.'.$vendor->id);

        return back()->with('profile_review_success', $review->status === 'pending'
            ? __('Your review was saved and is awaiting approval.')
            : __('Your rating has been saved.'));
    }
}
