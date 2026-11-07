<?php

namespace Botble\Marketplace\Services;

use Botble\Marketplace\Models\ActivityNotification;
use Botble\Marketplace\Models\ActivityPushSubscription;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class ActivityPushService
{
    public static function allowedEndpoint(string $endpoint): bool
    {
        $parts = parse_url($endpoint);
        if (! $parts || ($parts['scheme'] ?? '') !== 'https' || isset($parts['user']) || isset($parts['pass']) || ($parts['port'] ?? 443) !== 443) {
            return false;
        }
        $host = strtolower($parts['host'] ?? '');

        return in_array($host, ['fcm.googleapis.com', 'web.push.apple.com', 'push.services.mozilla.com'], true)
            || str_ends_with($host, '.push.services.mozilla.com') || str_ends_with($host, '.notify.windows.com');
    }

    public function sendPending(): void
    {
        $public = setting('activity_push_public_key');
        $private = setting('activity_push_private_key');
        $enabledAt = setting('activity_push_enabled_at');
        if (! $public || ! $private || ! $enabledAt) {
            return;
        }
        $sender = $this->sender(['VAPID' => [
            'subject' => rtrim(config('app.url'), '/'), 'publicKey' => $public, 'privateKey' => Crypt::decryptString($private),
        ]]);
        $notifications = ActivityNotification::query()->whereNull('read_at')->whereNull('push_sent_at')->where('push_attempts', '<', 3)
            ->where('created_at', '>=', \Carbon\Carbon::parse($enabledAt))->where('created_at', '>=', now()->subHour())->oldest('id')->limit(30)->get();
        $started = microtime(true);
        foreach ($notifications as $notification) {
            if (microtime(true) - $started > 45) {
                break;
            }
            $subscriptions = ActivityPushSubscription::query()->where('customer_id', $notification->customer_id)
                ->where('audience', $notification->audience)->where('store_id', $notification->store_id)->get();
            $failed = false;
            foreach ($subscriptions as $subscription) {
                if (microtime(true) - $started > 45) {
                    $failed = true;
                    break;
                }
                try {
                    $data = $subscription->subscription;
                    $data['contentEncoding'] = 'aes128gcm';
                    if (! self::allowedEndpoint($data['endpoint'] ?? '')) {
                        $subscription->delete();

                        continue;
                    }
                    // Never show private order/contact details on the lock screen.
                    $payload = json_encode(['title' => __('Growbiz notification'), 'body' => match ($notification->kind) {
                        'enquiry' => __('You have an enquiry update. Open Growbiz to view it.'),
                        'order' => __('An order was placed or updated. Open Growbiz to view it.'),
                        default => __('You have a new update. Open Growbiz to view it.'),
                    }, 'tag' => 'growbiz-'.$notification->audience.'-'.$notification->id,
                        'url' => $notification->audience === 'seller' ? route('marketplace.vendor.dashboard') : route('customer.overview')]);
                    $report = $sender->sendOneNotification(Subscription::create($data), $payload);
                    if ($report->isSubscriptionExpired()) {
                        $subscription->delete();
                    } elseif (! $report->isSuccess()) {
                        $failed = true;
                    }
                } catch (\Throwable $exception) {
                    $failed = true;
                    // Avoid logging private subscription URLs or authentication keys.
                    Log::warning('Activity push delivery failed', ['notification_id' => $notification->id, 'exception_type' => $exception::class]);
                }
            }
            $notification->update(['push_attempts' => $notification->push_attempts + 1, 'push_sent_at' => $failed ? null : now()]);
        }
    }

    protected function sender(array $auth): WebPush
    {
        return new WebPush($auth, ['TTL' => 3600], 5, ['allow_redirects' => false]);
    }
}
