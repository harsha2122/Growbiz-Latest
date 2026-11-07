<?php

namespace Tests\Feature;

use Botble\Marketplace\Models\ActivityNotification;
use Botble\Marketplace\Models\Enquiry;
use Botble\Marketplace\Services\ActivityNotificationService;
use Composer\Autoload\ClassLoader;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ActivityNotificationTest extends TestCase
{
    private ClassLoader $loader;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'activity_test', 'database.connections.activity_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        $this->loader = new ClassLoader;
        $this->loader->addPsr4('Botble\\Ecommerce\\', base_path('platform/plugins/ecommerce/src'));
        $this->loader->addPsr4('Botble\\Marketplace\\', base_path('platform/plugins/marketplace/src'));
        $this->loader->register();
        Schema::create('mp_enquiries', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('store_id');
            $t->timestamps();
        });
        Schema::create('mp_stores', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('customer_id');
        });
        (require base_path('platform/plugins/marketplace/database/migrations/2026_11_07_000001_create_customer_activity_notifications.php'))->up();
        (require base_path('platform/plugins/marketplace/database/migrations/2026_11_07_000002_add_activity_push_subscriptions.php'))->up();
    }

    protected function tearDown(): void
    {
        DB::purge('activity_test');
        $this->loader->unregister();
        parent::tearDown();
    }

    public function test_enquiry_page_snapshot_does_not_swallow_later_arrivals_or_other_stores(): void
    {
        $service = app(ActivityNotificationService::class);
        $ids = [];
        foreach ([1, 1, 2, 1] as $store) {
            $id = DB::table('mp_enquiries')->insertGetId(['store_id' => $store, 'created_at' => now(), 'updated_at' => now()]);
            $ids[] = $id;
            $service->send($store, $store, 'seller', 'enquiry', $id, 'New enquiry', 'Private details stay on the enquiry page.', 'enquiry:'.$id);
        }
        $this->assertSame(2, $service->viewEnquiries(1, $ids[1]));
        $this->assertSame(1, Enquiry::where('store_id', 1)->whereNull('seller_viewed_at')->count());
        $this->assertSame(1, Enquiry::where('store_id', 2)->whereNull('seller_viewed_at')->count());
        $this->assertSame(2, ActivityNotification::whereNotNull('read_at')->count());
        $this->assertSame(0, $service->viewEnquiries(1, $ids[1]));
    }

    public function test_seller_notification_uses_store_owner_and_deduplicates_new_events(): void
    {
        DB::table('mp_stores')->insert(['id' => 5, 'customer_id' => 20]);
        $service = app(ActivityNotificationService::class);
        $service->seller(5, 'order', 40, 'New order', 'A new order arrived.', 'order:new:40');
        $service->seller(5, 'order', 40, 'New order', 'A new order arrived.', 'order:new:40');
        $service->send(30, null, 'customer', 'order', 40, 'Order placed', 'Your order was placed.', 'order:new:40');
        $this->assertSame(2, ActivityNotification::count());
        $this->assertSame(20, (int) ActivityNotification::where('audience', 'seller')->first()->customer_id);
        $this->assertNull(ActivityNotification::where('audience', 'customer')->first()->store_id);
    }

    public function test_order_alerts_go_to_both_parties_once_and_status_changes_create_new_alerts(): void
    {
        DB::table('mp_stores')->insert(['id' => 5, 'customer_id' => 20]);
        $order = new \Botble\Ecommerce\Models\Order;
        $order->forceFill(['id' => 41, 'store_id' => 5, 'user_id' => 30, 'status' => 'pending']);
        $service = app(ActivityNotificationService::class);
        $service->order($order, true);
        $service->order($order, true);
        $this->assertSame(2, ActivityNotification::count());
        $order->status = 'completed';
        $service->order($order);
        $this->assertSame(4, ActivityNotification::count());
        $this->assertSame(2, ActivityNotification::where('audience', 'customer')->where('customer_id', 30)->count());
        $this->assertSame(2, ActivityNotification::where('audience', 'seller')->where('customer_id', 20)->count());
    }

    public function test_push_only_accepts_known_https_browser_gateways(): void
    {
        $guard = \Botble\Marketplace\Services\ActivityPushService::class;
        foreach (['https://fcm.googleapis.com/fcm/send/token', 'https://updates.push.services.mozilla.com/wpush/v2/token', 'https://web.push.apple.com/token'] as $url) {
            $this->assertTrue($guard::allowedEndpoint($url));
        }
        foreach (['http://fcm.googleapis.com/token', 'https://127.0.0.1/token', 'https://fcm.googleapis.com.attacker.test/token', 'https://user@fcm.googleapis.com/token', 'https://fcm.googleapis.com:8443/token'] as $url) {
            $this->assertFalse($guard::allowedEndpoint($url));
        }
    }

    public function test_encrypted_background_delivery_prunes_expired_devices_and_is_not_resent(): void
    {
        $keys = \Minishlink\WebPush\VAPID::createVapidKeys();
        $values = ['activity_push_public_key' => $keys['publicKey'],
            'activity_push_private_key' => \Illuminate\Support\Facades\Crypt::encryptString($keys['privateKey']),
            'activity_push_enabled_at' => now()->subMinute()->toIso8601String()];
        $settings = \Mockery::mock(\Botble\Setting\Supports\SettingStore::class);
        $settings->shouldReceive('get')->andReturnUsing(fn ($key, $default = null) => $values[$key] ?? $default);
        $this->app->instance(\Botble\Setting\Supports\SettingStore::class, $settings);
        config(['app.url' => 'https://growbiz.example.test']);
        if (! \Illuminate\Support\Facades\Route::has('customer.overview')) {
            \Illuminate\Support\Facades\Route::get('/test-account', fn () => '')->name('customer.overview');
        }
        app(ActivityNotificationService::class)->send(30, null, 'customer', 'order', 41, 'Order update', 'Private order information');
        foreach (['first', 'second'] as $token) {
            $endpoint = 'https://fcm.googleapis.com/fcm/send/test-'.$token;
            $subscription = \Botble\Marketplace\Models\ActivityPushSubscription::create([
                'customer_id' => 30, 'audience' => 'customer', 'endpoint_hash' => hash('sha256', $endpoint),
                'subscription' => ['endpoint' => $endpoint, 'keys' => ['p256dh' => $keys['publicKey'], 'auth' => rtrim(strtr(base64_encode(random_bytes(16)), '+/', '-_'), '=')]],
            ]);
            $this->assertStringNotContainsString('fcm.googleapis.com', $subscription->getRawOriginal('subscription'));
        }
        $service = new class extends \Botble\Marketplace\Services\ActivityPushService
        {
            public int $requests = 0;

            protected function sender(array $auth): \Minishlink\WebPush\WebPush
            {
                $stack = \GuzzleHttp\HandlerStack::create(new \GuzzleHttp\Handler\MockHandler([new \GuzzleHttp\Psr7\Response(201), new \GuzzleHttp\Psr7\Response(410)]));
                $stack->push(\GuzzleHttp\Middleware::mapRequest(function ($request) {
                    $this->requests++;
                    if ($request->getHeaderLine('Content-Encoding') !== 'aes128gcm') {
                        throw new \RuntimeException('Push must use modern encrypted payloads.');
                    }

                    return $request;
                }));

                return new \Minishlink\WebPush\WebPush($auth, ['TTL' => 3600], 5, ['handler' => $stack, 'allow_redirects' => false]);
            }
        };
        $service->sendPending();
        $this->assertSame(2, $service->requests);
        $this->assertNotNull(ActivityNotification::first()->push_sent_at);
        $this->assertSame(1, \Botble\Marketplace\Models\ActivityPushSubscription::count());
        $this->assertSame(2, $service->requests);
        $service->sendPending();
        $this->assertSame(2, $service->requests);
    }
}
