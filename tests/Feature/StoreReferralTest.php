<?php

namespace Tests\Feature;

use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Botble\Marketplace\Models\CustomerReferral;
use Botble\Marketplace\Models\ReferralLink;
use Botble\Marketplace\Models\ReferralProgram;
use Botble\Marketplace\Services\StoreReferralService;
use Composer\Autoload\ClassLoader;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StoreReferralTest extends TestCase
{
    private ClassLoader $loader;

    private StoreReferralService $service;

    private ReferralProgram $program;

    private ReferralLink $link;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'referral_test', 'database.connections.referral_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ]]);
        $this->loader = new ClassLoader;
        foreach (['Marketplace', 'Ecommerce', 'Payment'] as $plugin) {
            $this->loader->addPsr4('Botble\\'.$plugin.'\\', base_path('platform/plugins/'.strtolower($plugin).'/src'));
        }
        $this->loader->register();
        Schema::create('ec_customers', function (Blueprint $t): void {
            $t->id();
            $t->string('name');
            $t->string('email');
            $t->timestamp('confirmed_at')->nullable();
            $t->timestamps();
        });
        Schema::create('mp_stores', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('customer_id');
            $t->string('name');
            $t->string('status');
            $t->timestamps();
        });
        Schema::create('ec_orders', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('store_id');
            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('payment_id')->nullable();
            $t->string('status');
            $t->string('code')->nullable();
            $t->boolean('is_finished')->default(true);
            $t->decimal('sub_total', 14, 2)->default(0);
            $t->decimal('discount_amount', 14, 2)->default(0);
            $t->text('description')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();
        });
        Schema::create('payments', function (Blueprint $t): void {
            $t->id();
            $t->string('status');
            $t->decimal('refunded_amount', 14, 2)->default(0);
            $t->timestamps();
        });
        Schema::create('ec_shipments', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('order_id');
            $t->string('status');
            $t->timestamps();
        });
        Schema::create('ec_order_returns', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('order_id');
            $t->string('return_status');
            $t->timestamps();
        });
        Schema::create('ec_order_return_items', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('order_return_id');
            $t->decimal('refund_amount', 14, 2);
        });
        Schema::create('ec_discounts', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('store_id');
            foreach (['title', 'code', 'type', 'type_option', 'target', 'discount_on', 'description'] as $column) {
                $t->string($column)->nullable();
            }
            foreach (['value', 'quantity', 'total_used'] as $column) {
                $t->decimal($column, 14, 2)->nullable();
            }
            foreach (['can_use_with_promotion', 'can_use_with_flash_sale', 'display_at_checkout'] as $column) {
                $t->boolean($column)->default(false);
            }
            $t->timestamp('start_date')->nullable();
            $t->timestamp('end_date')->nullable();
            $t->timestamps();
        });
        (require base_path('platform/plugins/marketplace/database/migrations/2026_10_29_000001_create_store_referral_programs.php'))->up();
        Schema::create('mp_enquiries', function (Blueprint $t): void {
            $t->id();
            $t->integer('store_id');
            $t->integer('product_id')->nullable();
            $t->string('source')->nullable();
            $t->string('name')->nullable();
            $t->string('contact_number')->nullable();
            $t->string('email');
            $t->text('address')->nullable();
            $t->timestamps();
        });
        (require base_path('platform/plugins/marketplace/database/migrations/2026_11_01_000001_extend_referral_channels_and_verification.php'))->up();
        foreach ([1, 2, 3, 4] as $id) {
            DB::table('ec_customers')->insert(['id' => $id, 'name' => 'Customer '.$id, 'email' => $id.'@example.com', 'confirmed_at' => now()]);
        }
        DB::table('mp_stores')->insert(['id' => 1, 'customer_id' => 1, 'name' => 'Store', 'status' => 'published']);
        $this->program = ReferralProgram::create([
            'store_id' => 1, 'enabled' => true, 'mode' => 'purchase', 'reward_type' => 'fixed', 'reward_value' => 100,
            'budget' => 1000, 'offer_type' => 'none', 'minimum_order' => 0, 'hold_days' => 30,
        ])->fresh();
        $this->link = ReferralLink::create(['program_id' => $this->program->id, 'customer_id' => 2, 'token' => str_repeat('a', 40)]);
        $this->customer = Customer::findOrFail(3);
        $this->service = new StoreReferralService;
    }

    protected function tearDown(): void
    {
        $this->loader->unregister();
        parent::tearDown();
    }

    private function order(array $changes = []): Order
    {
        $id = DB::table('ec_orders')->insertGetId(array_merge([
            'store_id' => 1, 'user_id' => 3, 'status' => 'pending', 'sub_total' => 1000,
            'discount_amount' => 100, 'created_at' => now(), 'updated_at' => now(),
        ], $changes));

        return Order::findOrFail($id);
    }

    private function deliver(Order $order): void
    {
        $paymentId = DB::table('payments')->insertGetId(['status' => 'completed']);
        DB::table('ec_shipments')->insert(['order_id' => $order->id, 'status' => 'delivered']);
        DB::table('ec_orders')->where('id', $order->id)->update(['status' => 'completed', 'payment_id' => $paymentId]);
    }

    public function test_visit_rewards_are_unique_budgeted_and_held(): void
    {
        $this->program->update(['mode' => 'visit', 'budget' => 100]);
        $referral = $this->service->claim($this->link, $this->customer);
        $this->assertSame('pending', $referral->status);
        $this->assertEquals(100, $this->program->fresh()->reserved);
        $this->assertSame($referral->id, $this->service->claim($this->link, $this->customer)->id);
        $this->service->reconcile($referral->id);
        $this->assertSame('pending', $referral->fresh()->status);
        $this->travel(25)->hours();
        $this->service->reconcile($referral->id);
        $this->assertSame('available', $referral->fresh()->status);
        $this->travelBack();
        try {
            $this->service->claim($this->link, Customer::find(4));
            $this->fail('Budget was exceeded');
        } catch (ValidationException) {
            $this->assertSame(1, CustomerReferral::count());
            $this->assertEquals(100, $this->program->fresh()->reserved);
        }
    }

    public function test_self_unverified_existing_and_paused_referrals_are_rejected(): void
    {
        foreach ([1, 2] as $id) {
            try {
                $this->service->claim($this->link, Customer::find($id));
                $this->fail('Self referral accepted');
            } catch (ValidationException) {
                $this->assertSame(0, CustomerReferral::count());
            }
        }
        $this->customer->confirmed_at = null;
        try {
            $this->service->claim($this->link, $this->customer);
            $this->fail('Unverified accepted');
        } catch (ValidationException) {
            $this->assertSame(0, CustomerReferral::count());
        }
        $this->customer->confirmed_at = now();
        $this->program->update(['enabled' => false]);
        try {
            $this->service->claim($this->link, $this->customer);
            $this->fail('Paused accepted');
        } catch (ValidationException) {
            $this->assertSame(0, CustomerReferral::count());
        }
        $this->program->update(['enabled' => true]);
        $this->order();
        $this->expectException(ValidationException::class);
        $this->service->claim($this->link, $this->customer);
    }

    public function test_purchase_reward_requires_paid_delivery_and_return_hold_and_uses_snapshot(): void
    {
        $this->program->update(['reward_type' => 'percentage', 'reward_value' => 10, 'reward_cap' => 80]);
        $referral = $this->service->claim($this->link, $this->customer);
        $this->program->update(['reward_value' => 50]);
        $order = $this->order();
        $this->service->trackOrder($order);
        $this->service->trackOrder($order);
        $this->assertEquals(80, $referral->fresh()->amount);
        $this->assertEquals(80, $this->program->fresh()->reserved);
        $this->service->reconcile($referral->id);
        $this->assertNull($referral->fresh()->available_at);
        $this->deliver($order);
        $this->service->reconcile($referral->id);
        $this->assertSame('pending', $referral->fresh()->status);
        $this->travel(31)->days();
        $this->service->reconcile($referral->id);
        $this->assertSame('available', $referral->fresh()->status);
        $this->service->recordPayout($referral->id, 1, 'BANK-123', 1);
        $this->assertSame('paid', $referral->fresh()->status);
        $this->assertSame('BANK-123', $referral->fresh()->payout_reference);
        $this->expectException(ValidationException::class);
        $this->service->recordPayout($referral->id, 1, 'DUPLICATE', 1);
    }

    public function test_cancelled_orders_release_reserved_budget(): void
    {
        $referral = $this->service->claim($this->link, $this->customer);
        $order = $this->order();
        $this->service->trackOrder($order);
        DB::table('ec_orders')->where('id', $order->id)->update(['status' => 'canceled']);
        $this->service->reconcile($referral->id);
        $this->assertSame('rejected', $referral->fresh()->status);
        $this->assertEquals(0, $this->program->fresh()->reserved);
        $this->service->reconcile($referral->id);
        $this->assertEquals(0, $this->program->fresh()->reserved);
    }

    public function test_partial_refunds_reduce_percentage_rewards_and_pending_returns_block_release(): void
    {
        $this->program->update(['reward_type' => 'percentage', 'reward_value' => 10]);
        $referral = $this->service->claim($this->link, $this->customer);
        $order = $this->order();
        $this->service->trackOrder($order);
        $this->deliver($order);
        $returnId = DB::table('ec_order_returns')->insertGetId(['order_id' => $order->id, 'return_status' => 'pending']);
        $this->service->reconcile($referral->id);
        $this->assertNull($referral->fresh()->available_at);
        DB::table('ec_order_returns')->where('id', $returnId)->update(['return_status' => 'completed']);
        DB::table('ec_order_return_items')->insert(['order_return_id' => $returnId, 'refund_amount' => 300]);
        DB::table('ec_orders')->where('id', $order->id)->update(['status' => 'partial_returned']);
        $this->service->reconcile($referral->id);
        $this->assertEquals(60, $referral->fresh()->amount);
        $this->assertSame('pending', $referral->fresh()->status);
    }

    public function test_personal_coupons_are_scoped_to_store_and_customer_and_custom_offers_reach_order(): void
    {
        $this->program->update(['offer_type' => 'percentage', 'offer_value' => 10]);
        $referral = $this->service->claim($this->link, $this->customer);
        $this->assertNotNull($referral->coupon);
        $this->assertEquals(1, $referral->coupon->store_id);
        $this->assertEquals(1, $referral->coupon->quantity);
        $this->assertEquals(3, $referral->customer_id);
        $this->assertEquals(10, $referral->coupon->value);
        $this->program->update(['offer_type' => 'custom', 'offer_text' => 'Free installation']);
        $other = $this->service->claim($this->link, Customer::find(4));
        $order = $this->order(['user_id' => 4]);
        $this->service->trackOrder($order);
        $this->assertStringContainsString('Free installation', $order->fresh()->description);
        $this->assertNull($other->coupon_id);
    }

    public function test_expired_attribution_and_other_store_orders_do_not_earn(): void
    {
        $referral = $this->service->claim($this->link, $this->customer);
        $wrongStore = $this->order(['store_id' => 2]);
        $this->service->trackOrder($wrongStore);
        $this->assertNull($referral->fresh()->order_id);
        $this->travel(31)->days();
        $this->service->trackOrder($this->order());
        $this->assertNull($referral->fresh()->order_id);
        $this->assertEquals(0, $this->program->fresh()->reserved);
    }

    public function test_visit_payout_cannot_be_released_or_paid_twice(): void
    {
        $this->program->update(['mode' => 'visit']);
        $referral = $this->service->claim($this->link, $this->customer);
        $this->travel(25)->hours();
        $this->service->reconcile($referral->id);
        $this->service->recordPayout($referral->id, 1, 'VISIT-1', 1);
        $this->service->reconcile($referral->id);
        $this->assertSame('paid', $referral->fresh()->status);
        $this->expectException(ValidationException::class);
        $this->service->recordPayout($referral->id, 1, 'VISIT-2', 1);
    }

    public function test_admin_rejection_releases_budget_and_expires_coupon(): void
    {
        $this->program->update(['mode' => 'visit', 'offer_type' => 'fixed', 'offer_value' => 25]);
        $referral = $this->service->claim($this->link, $this->customer);
        $this->service->rejectReward($referral->id, 'Admin #1: Duplicate account');
        $this->assertSame('rejected', $referral->fresh()->status);
        $this->assertEquals(0, $this->program->fresh()->reserved);
        $this->assertTrue($referral->fresh()->coupon->isExpired());
    }

    public function test_refund_after_payout_is_flagged_without_claiming_money_was_recovered(): void
    {
        $referral = $this->service->claim($this->link, $this->customer);
        $order = $this->order();
        $this->service->trackOrder($order);
        $this->deliver($order);
        $this->service->reconcile($referral->id);
        $this->travel(31)->days();
        $this->service->recordPayout($referral->id, 1, 'PAID-1', 1);
        DB::table('ec_orders')->where('id', $order->id)->update(['status' => 'returned']);
        $this->service->reconcile($referral->id);
        $this->assertSame('reversed', $referral->fresh()->status);
        $this->assertNotNull($referral->fresh()->paid_at);
        $this->assertEquals(100, $this->program->fresh()->reserved);
    }

    public function test_coupon_guard_rejects_other_accounts_stores_expiry_and_insufficient_total(): void
    {
        $this->assertTrue(is_plugin_active('marketplace'));
        $this->program->update(['offer_type' => 'percentage', 'offer_value' => 10, 'minimum_order' => 200]);
        $referral = $this->service->claim($this->link, $this->customer);
        $discount = $referral->coupon;
        $discount->can_use_with_flash_sale = true;
        $products = collect([(object) ['id' => 10, 'original_product' => (object) ['store_id' => 1]]]);
        $total = 1000;
        $cart = \Mockery::mock(\Botble\Ecommerce\Cart\Cart::class);
        $cart->shouldReceive('products')->andReturnUsing(fn () => $products);
        $cart->shouldReceive('content')->andReturn(collect([(object) ['id' => 10]]));
        $cart->shouldReceive('rawSubTotalByItems')->andReturnUsing(function () use (&$total) {
            return $total;
        });
        $cart->shouldReceive('rawTotalByItems')->andReturnUsing(function () use (&$total) {
            return $total;
        });
        \Botble\Ecommerce\Facades\Cart::shouldReceive('instance')->with('cart')->andReturn($cart);
        $checker = new \Botble\Ecommerce\Services\HandleApplyCouponService;
        $this->actingAs($this->customer, 'customer');
        $this->assertFalse($checker->checkConditionDiscount($discount, [], 3)['error']);
        $this->actingAs(Customer::find(4), 'customer');
        $this->assertTrue($checker->checkConditionDiscount($discount, [], 4)['error']);
        $this->assertTrue($checker->checkConditionDiscount($discount, [], 3)['error']);
        $this->actingAs($this->customer, 'customer');
        $total = 100;
        $this->assertTrue($checker->checkConditionDiscount($discount, [], 3)['error']);
        $total = 1000;
        $products->first()->original_product->store_id = 2;
        $this->assertTrue($checker->checkConditionDiscount($discount, [], 3)['error']);
        $products->first()->original_product->store_id = 1;
        $this->travel(31)->days();
        $this->assertTrue($checker->checkConditionDiscount($discount, [], 3)['error']);
    }

    public function test_delivered_but_unpaid_order_stays_pending_and_zero_orders_do_not_reserve(): void
    {
        $referral = $this->service->claim($this->link, $this->customer);
        $zero = $this->order(['sub_total' => 100, 'discount_amount' => 100, 'is_finished' => false]);
        $this->service->trackOrder($zero);
        $this->assertNull($referral->fresh()->order_id);
        $this->assertEquals(0, $this->program->fresh()->reserved);
        $order = $this->order();
        $this->service->trackOrder($order);
        $this->deliver($order);
        DB::table('payments')->update(['status' => 'pending']);
        $this->service->reconcile($referral->id);
        $this->travel(60)->days();
        $this->service->reconcile($referral->id);
        $this->assertSame('pending', $referral->fresh()->status);
        $this->assertNull($referral->fresh()->available_at);
        $this->expectException(ValidationException::class);
        $this->service->recordPayout($referral->id, 1, 'UNPAID', 1);
    }

    public function test_offline_purchase_requires_customer_and_admin_then_waits_for_returns(): void
    {
        $this->program->update(['channel' => 'offline', 'reward_type' => 'percentage', 'reward_value' => 10]);
        $ref = $this->service->claim($this->link, $this->customer);
        $verification = new \Botble\Marketplace\Services\ReferralVerificationService;
        $event = $verification->offline(1, 1, ['code' => $ref->pass_code, 'reference' => 'BILL-101', 'occurred_on' => today()->toDateString(), 'subtotal' => 1000, 'discount' => 100, 'tax' => 180, 'shipping' => 50, 'evidence_path' => 'private/test.pdf', 'receipt_hash' => hash('sha256', 'receipt')]);
        $this->assertSame('awaiting_customer', $ref->fresh()->status);
        $this->assertEquals(90, $ref->fresh()->reserved_amount);
        try {
            $verification->review($event->id, 1, true, 'Verified');
            $this->fail('Unconfirmed activity approved');
        } catch (ValidationException) {
            $this->assertSame('awaiting_customer', $event->fresh()->status);
        }
        $verification->confirm($event->id, 3, true);
        $this->assertSame('awaiting_approval', $ref->fresh()->status);
        $verification->review($event->id, 1, true, 'Receipt verified');
        $this->service->reconcile($ref->id);
        $this->assertSame('pending', $ref->fresh()->status);
        $this->travel(31)->days();
        $this->service->reconcile($ref->id);
        $this->assertSame('available', $ref->fresh()->status);
        $verification->refund($event->id, 1, 300, 'Partial refund');
        $this->assertEquals(60, $ref->fresh()->amount);
        $this->service->recordPayout($ref->id, 1, 'OFFLINE-PAID', 1);
        $verification->refund($event->id, 1, 900, 'Full refund');
        $this->assertSame('reversed', $ref->fresh()->status);
        $this->assertNotNull($ref->fresh()->paid_at);
        $this->assertGreaterThanOrEqual(5, DB::table('mp_referral_audits')->where('referral_id', $ref->id)->count());
    }

    public function test_offline_visits_do_not_earn_on_online_unlock_and_decline_releases_budget(): void
    {
        $this->program->update(['mode' => 'visit', 'channel' => 'offline']);
        $ref = $this->service->claim($this->link, $this->customer);
        $this->assertSame('tracked', $ref->status);
        $verification = new \Botble\Marketplace\Services\ReferralVerificationService;
        $event = $verification->offline(1, 1, ['code' => $ref->pass_code, 'reference' => 'VISIT-1', 'occurred_on' => today()->toDateString()]);
        $verification->confirm($event->id, 3, false);
        $this->assertSame('rejected', $ref->fresh()->status);
        $this->assertEquals(0, $this->program->fresh()->reserved);
    }

    public function test_enquiry_rewards_require_admin_and_do_not_pay_twice(): void
    {
        $this->program->update(['mode' => 'enquiry']);
        $ref = $this->service->claim($this->link, $this->customer);
        $enquiry = \Botble\Marketplace\Models\Enquiry::create(['customer_id' => 3, 'store_id' => 1, 'email' => '3@example.com', 'name' => 'Customer 3']);
        $this->assertSame('awaiting_approval', $ref->fresh()->status);
        $this->assertEquals(100, $ref->fresh()->reserved_amount);
        \Botble\Marketplace\Models\Enquiry::create(['customer_id' => 3, 'store_id' => 1, 'email' => '3@example.com']);
        $this->assertSame(1, \Botble\Marketplace\Models\ReferralEvent::count());
        $verification = new \Botble\Marketplace\Services\ReferralVerificationService;
        $verification->review($ref->fresh()->event_id, 1, true, 'Genuine enquiry');
        $this->travel(25)->hours();
        $this->service->reconcile($ref->id);
        $this->assertSame('available', $ref->fresh()->status);
        $this->service->trackOrder($this->order());
        $this->assertNull($ref->fresh()->order_id);
        $this->assertEquals(100, $this->program->fresh()->reserved);
    }

    public function test_online_and_offline_cannot_stack(): void
    {
        $this->program->update(['mode' => 'visit', 'channel' => 'both']);
        $ref = $this->service->claim($this->link, $this->customer);
        $this->expectException(ValidationException::class);
        (new \Botble\Marketplace\Services\ReferralVerificationService)->offline(1, 1, ['code' => $ref->pass_code, 'reference' => 'REPEAT', 'occurred_on' => today()->toDateString()]);
    }

    public function test_service_completed_enquiry_cannot_approve_without_service_proof(): void
    {
        Schema::create('mp_service_completions', function (Blueprint $t): void {
            $t->id();
            $t->integer('store_id');
            $t->integer('customer_id');
            $t->date('completed_at');
        });
        $this->program->update(['mode' => 'enquiry', 'enquiry_trigger' => 'service_completed']);
        $ref = $this->service->claim($this->link, $this->customer);
        \Botble\Marketplace\Models\Enquiry::create(['customer_id' => 3, 'store_id' => 1, 'email' => '3@example.com']);
        $verification = new \Botble\Marketplace\Services\ReferralVerificationService;
        try {
            $verification->review($ref->fresh()->event_id, 1, true, 'Done');
            $this->fail('Missing service proof accepted');
        } catch (ValidationException) {
            $this->assertSame('awaiting_approval', $ref->fresh()->status);
        }
        DB::table('mp_service_completions')->insert(['store_id' => 1, 'customer_id' => 3, 'completed_at' => today()]);
        $verification->review($ref->fresh()->event_id, 1, true, 'Service confirmed');
        $this->assertSame('pending', $ref->fresh()->status);
    }

    public function test_duplicate_invoice_is_rejected_for_another_customer_and_no_budget_leaks(): void
    {
        $this->program->update(['channel' => 'offline']);
        $verification = new \Botble\Marketplace\Services\ReferralVerificationService;
        $first = $this->service->claim($this->link, $this->customer);
        $second = $this->service->claim($this->link, Customer::find(4));
        $data = ['code' => $first->pass_code, 'reference' => 'INV-123', 'occurred_on' => today()->toDateString(), 'subtotal' => 1000, 'evidence_path' => 'private/1.pdf'];
        $verification->offline(1, 1, $data);
        try {
            $verification->offline(1, 1, array_replace($data, ['code' => $second->pass_code, 'reference' => 'inv 123']));
            $this->fail('Duplicate invoice accepted');
        } catch (\Illuminate\Database\QueryException) {
            $this->assertEquals(100, $this->program->fresh()->reserved);
        }
        $this->assertSame('tracked', $second->fresh()->status);
    }

    public function test_another_customer_cannot_confirm_an_offline_visit(): void
    {
        $this->program->update(['mode' => 'visit', 'channel' => 'offline']);
        $ref = $this->service->claim($this->link, $this->customer);
        $verification = new \Botble\Marketplace\Services\ReferralVerificationService;
        $event = $verification->offline(1, 1, ['code' => $ref->pass_code, 'reference' => 'PRIVATE-VISIT', 'occurred_on' => today()->toDateString()]);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $verification->confirm($event->id, 4, true);
    }

    public function test_guest_or_mismatched_enquiry_email_cannot_claim_reward(): void
    {
        $this->program->update(['mode' => 'enquiry']);
        $ref = $this->service->claim($this->link, $this->customer);
        \Botble\Marketplace\Models\Enquiry::create(['store_id' => 1, 'email' => '3@example.com']);
        \Botble\Marketplace\Models\Enquiry::create(['customer_id' => 3, 'store_id' => 1, 'email' => '4@example.com']);
        $this->assertSame('tracked', $ref->fresh()->status);
        $this->assertSame(0, \Botble\Marketplace\Models\ReferralEvent::count());
    }

    public function test_referrer_code_can_start_an_offline_visit_without_an_online_visit_reward(): void
    {
        $this->program->update(['mode' => 'visit', 'channel' => 'both']);
        $this->link->update(['short_code' => 'G-INSTORE-CODE']);
        $verification = new \Botble\Marketplace\Services\ReferralVerificationService;
        $event = $verification->offline(1, 1, ['code' => 'G-INSTORE-CODE', 'customer_email' => '4@example.com', 'reference' => 'WALK-IN', 'occurred_on' => today()->toDateString()]);
        $this->assertSame('awaiting_customer', $event->referral->status);
        $this->assertEquals(4, $event->referral->customer_id);
        $this->assertEquals(2, $event->referral->referrer_id);
        $this->assertEquals(100, $this->program->fresh()->reserved);
    }

    public function test_unconfirmed_offline_activity_expires_and_releases_budget(): void
    {
        $this->program->update(['mode' => 'visit', 'channel' => 'offline']);
        $ref = $this->service->claim($this->link, $this->customer);
        $verification = new \Botble\Marketplace\Services\ReferralVerificationService;
        $event = $verification->offline(1, 1, ['code' => $ref->pass_code, 'reference' => 'UNCONFIRMED', 'occurred_on' => today()->toDateString()]);
        $this->travel(8)->days();
        $verification->expireUnconfirmed($event->id);
        $this->assertSame('rejected', $ref->fresh()->status);
        $this->assertEquals(0, $this->program->fresh()->reserved);
    }
}
