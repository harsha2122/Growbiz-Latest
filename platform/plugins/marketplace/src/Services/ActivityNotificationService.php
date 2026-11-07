<?php

namespace Botble\Marketplace\Services;

use Botble\Ecommerce\Models\Order;
use Botble\Marketplace\Models\ActivityNotification;
use Botble\Marketplace\Models\Enquiry;
use Botble\Marketplace\Models\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ActivityNotificationService
{
    public function send(int $customerId, ?int $storeId, string $audience, string $kind, int $subjectId, string $title, string $message, ?string $key = null): void
    {
        // Keep order/enquiry creation working during deployment before the migration runs.
        if (! $customerId || ! Schema::hasTable('mp_activity_notifications')) {
            return;
        }
        ActivityNotification::query()->firstOrCreate([
            'customer_id' => $customerId, 'audience' => $audience, 'event_key' => $key ?? (string) Str::uuid(),
        ], [
            'store_id' => $storeId, 'kind' => $kind, 'subject_id' => $subjectId,
            'title' => $title, 'message' => $message,
        ]);
    }

    public function seller(int $storeId, string $kind, int $subjectId, string $title, string $message, ?string $key = null): void
    {
        if (! Schema::hasTable('mp_activity_notifications')) {
            return;
        }
        $owner = Store::query()->whereKey($storeId)->value('customer_id');
        if ($owner) {
            $this->send((int) $owner, $storeId, 'seller', $kind, $subjectId, $title, $message, $key);
        }
    }

    public function order(Order $order, bool $new = false, ?string $message = null): void
    {
        $message ??= $new ? __('A new order has been placed.') : __('Order status: :status', ['status' => (string) $order->status]);
        $title = $new ? __('New order #:id', ['id' => $order->id]) : __('Order #:id updated', ['id' => $order->id]);
        $key = $new ? 'order:new:'.$order->id : null;
        if ($order->store_id) {
            $this->seller((int) $order->store_id, 'order', $order->id, $title, $message, $key);
        }
        if ($order->user_id) {
            $this->send((int) $order->user_id, null, 'customer', 'order', $order->id, $title, $message, $key);
        }
    }

    public function viewEnquiries(int $storeId, int $throughId): int
    {
        return DB::transaction(function () use ($storeId, $throughId): int {
            $count = Enquiry::query()->where('store_id', $storeId)->where('id', '<=', $throughId)
                ->whereNull('seller_viewed_at')->update(['seller_viewed_at' => now()]);
            ActivityNotification::query()->where('store_id', $storeId)->where('audience', 'seller')
                ->where('kind', 'enquiry')->where('subject_id', '<=', $throughId)->whereNull('read_at')->update(['read_at' => now()]);

            return $count;
        });
    }
}
