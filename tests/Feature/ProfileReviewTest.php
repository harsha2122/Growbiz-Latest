<?php

namespace Tests\Feature;

use Botble\Ecommerce\Models\Customer;
use Botble\Marketplace\Models\ProfileReview;
use Botble\Marketplace\Models\ServiceCompletion;
use Botble\Marketplace\Models\Store;
use Botble\Marketplace\Services\ProfileReviewService;
use Composer\Autoload\ClassLoader;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProfileReviewTest extends TestCase
{
    private ClassLoader $loader;

    private ProfileReviewService $service;

    private Store $store;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'profile_review_test', 'database.connections.profile_review_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ]]);
        $this->loader = new ClassLoader;
        foreach (['Marketplace', 'Ecommerce'] as $plugin) {
            $this->loader->addPsr4('Botble\\'.$plugin.'\\', base_path('platform/plugins/'.strtolower($plugin).'/src'));
        }
        $this->loader->register();
        Schema::create('ec_customers', function (Blueprint $t): void {
            $t->id();
            $t->string('name');
            $t->boolean('is_vendor')->default(false);
            $t->timestamp('vendor_verified_at')->nullable();
        });
        Schema::create('mp_stores', function (Blueprint $t): void {
            $t->id();
            $t->integer('customer_id');
            $t->string('name');
            $t->string('status');
            $t->boolean('is_minimal_profile');
        });
        Schema::create('ec_orders', function (Blueprint $t): void {
            $t->id();
            $t->integer('store_id');
            $t->integer('user_id');
            $t->string('status');
        });
        (require base_path('platform/plugins/marketplace/database/migrations/2026_10_29_000002_create_profile_reviews_and_service_completions.php'))->up();
        DB::table('ec_customers')->insert([['id' => 1, 'name' => 'Seller'], ['id' => 2, 'name' => 'Customer'], ['id' => 3, 'name' => 'Other customer']]);
        DB::table('ec_customers')->where('id', 1)->update(['is_vendor' => true, 'vendor_verified_at' => now()]);
        DB::table('mp_stores')->insert([
            ['id' => 1, 'customer_id' => 1, 'name' => 'Profile vendor', 'status' => 'published', 'is_minimal_profile' => true],
            ['id' => 2, 'customer_id' => 1, 'name' => 'Other vendor', 'status' => 'published', 'is_minimal_profile' => false],
        ]);
        $this->store = Store::findOrFail(1);
        $this->customer = Customer::findOrFail(2);
        $this->service = new ProfileReviewService;
    }

    protected function tearDown(): void
    {
        $this->loader->unregister();
        parent::tearDown();
    }

    public function test_customer_can_rate_without_purchase_but_cannot_write_a_review(): void
    {
        $rating = $this->service->submit($this->store, $this->customer, 4, null);
        $this->assertSame('published', $rating->status);
        $this->assertEquals(4, $rating->star);
        $this->assertNull($rating->comment);
        $this->assertFalse($this->service->canWrite(2, 1));
        try {
            $this->service->submit($this->store, $this->customer, 5, 'Unverified text');
            $this->fail('Unverified review was accepted');
        } catch (ValidationException) {
            $this->assertEquals(4, $rating->fresh()->star);
            $this->assertSame(1, ProfileReview::count());
        }
    }

    public function test_service_confirmation_unlocks_review_and_upgrades_the_same_rating(): void
    {
        $rating = $this->service->submit($this->store, $this->customer, 3, null);
        ServiceCompletion::create(['store_id' => 1, 'customer_id' => 2, 'verified_by' => 1, 'completed_at' => today(), 'reference' => 'SERVICE-1']);
        $this->assertTrue($this->service->canWrite(2, 1));
        $review = $this->service->submit($this->store, $this->customer, 5, 'Service completed successfully');
        $this->assertSame($rating->id, $review->id);
        $this->assertSame(1, ProfileReview::count());
        $this->service->submit($this->store, $this->customer, 4, null);
        $this->assertSame('Service completed successfully', $review->fresh()->comment);
        $this->assertEquals(4, $review->fresh()->star);
    }

    public function test_only_the_same_customer_and_vendor_completed_purchase_or_service_qualifies(): void
    {
        DB::table('ec_orders')->insert([
            ['store_id' => 1, 'user_id' => 3, 'status' => 'completed'],
            ['store_id' => 2, 'user_id' => 2, 'status' => 'completed'],
            ['store_id' => 1, 'user_id' => 2, 'status' => 'pending'],
        ]);
        ServiceCompletion::create(['store_id' => 1, 'customer_id' => 3, 'verified_by' => 1, 'completed_at' => today(), 'reference' => 'OTHER']);
        $this->assertFalse($this->service->canWrite(2, 1));
        DB::table('ec_orders')->insert(['store_id' => 1, 'user_id' => 2, 'status' => 'completed']);
        $this->assertTrue($this->service->canWrite(2, 1));
    }

    public function test_public_average_excludes_pending_or_rejected_submissions(): void
    {
        $this->service->submit($this->store, $this->customer, 4, null);
        ProfileReview::create(['store_id' => 1, 'customer_id' => 3, 'star' => 1, 'status' => 'pending']);
        $summary = $this->store->publicRatingSummary();
        $this->assertSame(1, $summary['count']);
        $this->assertSame(4.0, $summary['average']);
        ProfileReview::query()->where('customer_id', 3)->update(['status' => 'published']);
        $this->assertSame(2.5, $this->store->publicRatingSummary()['average']);
        ProfileReview::query()->where('customer_id', 3)->update(['status' => 'rejected']);
        $this->assertSame(4.0, $this->store->publicRatingSummary()['average']);
    }

    public function test_normal_store_cannot_receive_profile_reviews(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->service->submit(Store::findOrFail(2), $this->customer, 5, null);
    }

    public function test_vendor_cannot_rate_their_own_profile(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->service->submit($this->store, Customer::findOrFail(1), 5, null);
    }

    public function test_migration_can_be_rolled_back(): void
    {
        $migration = require base_path('platform/plugins/marketplace/database/migrations/2026_10_29_000002_create_profile_reviews_and_service_completions.php');
        $migration->down();
        $this->assertFalse(Schema::hasTable('mp_profile_reviews'));
        $this->assertFalse(Schema::hasTable('mp_service_completions'));
    }
}
