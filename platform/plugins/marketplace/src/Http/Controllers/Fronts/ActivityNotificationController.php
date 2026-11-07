<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Models\ActivityNotification;
use Botble\Marketplace\Models\ActivityPushSubscription;
use Botble\Marketplace\Models\Enquiry;
use Botble\Marketplace\Services\ActivityPushService;
use Illuminate\Http\Request;

class ActivityNotificationController extends BaseController
{
    private function seller(Request $request): bool
    {
        return str_starts_with($request->route()->getName(), 'marketplace.vendor.');
    }

    private function query(Request $request)
    {
        $seller = $this->seller($request);
        $query = ActivityNotification::query()->where('customer_id', auth('customer')->id())
            ->where('audience', $seller ? 'seller' : 'customer');
        if ($seller) {
            $store = auth('customer')->user()->store;
            abort_unless($store?->exists, 404);
            $query->where('store_id', $store->id);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $query = $this->query($request);
        // Bound reads to the same snapshot so arrivals during this request remain new.
        $through = (int) (clone $query)->max('id');
        $query->where('id', '<=', $through);
        $unread = (clone $query)->whereNull('read_at')->count();
        $items = (clone $query)->latest('id')->limit(30)->get();
        $storeId = $this->seller($request) ? auth('customer')->user()->store->id : null;
        $enquiries = $storeId ? Enquiry::query()->where('store_id', $storeId) : null;

        return response()->json([
            'through' => $through, 'unread' => $unread, 'push_key' => setting('activity_push_public_key'),
            'enquiries' => $enquiries ? ['total' => (clone $enquiries)->count(), 'new' => (clone $enquiries)->whereNull('seller_viewed_at')->count()] : null,
            'items' => $items->map(function ($item) use ($request) {
                $seller = $this->seller($request);
                $url = $seller ? route('marketplace.vendor.dashboard') : route('customer.overview');
                if ($item->kind === 'enquiry' && $seller) {
                    $url = route('marketplace.vendor.enquiries.index');
                } elseif ($item->kind === 'order') {
                    // The destination performs its own order ownership checks.
                    $url = $seller ? route('marketplace.vendor.orders.edit', $item->subject_id) : route('customer.orders.view', $item->subject_id);
                } elseif ($item->kind === 'referral') {
                    $url = route('public.referral.account');
                }

                return ['id' => $item->id, 'title' => $item->title, 'message' => $item->message, 'read' => (bool) $item->read_at, 'time' => $item->created_at->toIso8601String(), 'url' => $url];
            }),
        ])->header('Cache-Control', 'private, no-store');
    }

    public function read(Request $request)
    {
        $data = $request->validate(['id' => ['nullable', 'integer', 'min:1'], 'through' => ['required_without:id', 'nullable', 'integer', 'min:0']]);
        $query = $this->query($request)->whereNull('read_at');
        if (! empty($data['id'])) {
            $query->whereKey($data['id']);
        } else {
            $query->where('id', '<=', $data['through']);
        }
        $query->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function subscribe(Request $request)
    {
        abort_unless(setting('activity_push_public_key'), 503);
        $this->query($request); // Applies seller/store ownership checks too.
        $data = $request->validate([
            'endpoint' => ['required', 'string', 'max:2048'],
            'keys.p256dh' => ['required', 'string', 'regex:/^[A-Za-z0-9_-]{87}$/'],
            'keys.auth' => ['required', 'string', 'regex:/^[A-Za-z0-9_-]{22}$/'],
            'contentEncoding' => ['nullable', 'in:aes128gcm'],
        ]);
        abort_unless(ActivityPushService::allowedEndpoint($data['endpoint']), 422);
        $seller = $this->seller($request);
        $hash = hash('sha256', $data['endpoint']);
        ActivityPushSubscription::query()->where('endpoint_hash', $hash)->where('customer_id', '!=', auth('customer')->id())->delete();
        ActivityPushSubscription::query()->updateOrCreate(['endpoint_hash' => $hash, 'audience' => $seller ? 'seller' : 'customer'], [
            'customer_id' => auth('customer')->id(), 'audience' => $seller ? 'seller' : 'customer',
            'store_id' => $seller ? auth('customer')->user()->store->id : null, 'subscription' => $data,
        ]);

        $staleIds = ActivityPushSubscription::query()->where('customer_id', auth('customer')->id())->where('audience', $seller ? 'seller' : 'customer')->latest('id')->skip(10)->take(100)->pluck('id');
        ActivityPushSubscription::query()->whereIn('id', $staleIds)->delete();

        return response()->json(['success' => true]);
    }

    public function unsubscribe(Request $request)
    {
        $data = $request->validate(['endpoint' => ['required', 'string', 'max:2048']]);
        ActivityPushSubscription::query()->where('customer_id', auth('customer')->id())
            ->where('endpoint_hash', hash('sha256', $data['endpoint']))->delete();

        return response()->json(['success' => true]);
    }
}
