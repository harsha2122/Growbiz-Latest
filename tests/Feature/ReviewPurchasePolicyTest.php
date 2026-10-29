<?php

namespace Tests\Feature;

use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Supports\ReviewEligibility;
use Botble\Ecommerce\Traits\CheckReviewConditionForApiTrait;
use Botble\Ecommerce\Traits\CheckReviewConditionTrait;
use Composer\Autoload\ClassLoader;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReviewPurchasePolicyTest extends TestCase
{
    protected ClassLoader $pluginLoader;

    protected bool $reviewRequired = false;

    protected bool $ratingRequired = false;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'review_policy_test',
            'database.connections.review_policy_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
            'auth.guards.customer' => ['driver' => 'session', 'provider' => 'customers'],
            'auth.providers.customers' => ['driver' => 'eloquent', 'model' => Customer::class],
        ]);
        $this->pluginLoader = new ClassLoader;
        $this->pluginLoader->addPsr4('Botble\\Ecommerce\\', base_path('platform/plugins/ecommerce/src'));
        $this->pluginLoader->register();

        EcommerceHelper::shouldReceive('onlyAllowCustomersPurchasedToReview')->andReturnUsing(fn () => $this->reviewRequired);
        EcommerceHelper::shouldReceive('onlyAllowCustomersPurchasedToRate')->andReturnUsing(fn () => $this->ratingRequired);

        Schema::create('ec_reviews', function (Blueprint $table): void {
            $table->id();
            $table->integer('customer_id');
            $table->integer('product_id');
            $table->unsignedTinyInteger('star')->default(5);
            $table->text('comment')->nullable();
            $table->json('images')->nullable();
            $table->string('status')->default('published');
            $table->timestamps();
        });
        Schema::create('ec_customers', function (Blueprint $table): void {
            $table->id();
        });
        DB::table('ec_customers')->insert(['id' => 7]);
        Schema::create('mp_service_completions', function (Blueprint $table): void {
            $table->id();
            $table->integer('customer_id');
            $table->integer('product_id')->nullable();
            $table->date('completed_at');
        });
        Schema::create('ec_orders', function (Blueprint $table): void {
            $table->id();
            $table->integer('user_id');
            $table->string('status');
        });
        Schema::create('ec_order_product', function (Blueprint $table): void {
            $table->integer('order_id');
            $table->integer('product_id');
        });
        Schema::create('ec_product_variations', function (Blueprint $table): void {
            $table->integer('product_id');
            $table->integer('configurable_product_id');
        });
    }

    protected function tearDown(): void
    {
        $this->pluginLoader->unregister();
        parent::tearDown();
    }

    public function test_ratings_are_free_and_written_reviews_require_experience_regardless_of_legacy_settings(): void
    {
        $policy = new ReviewEligibility;
        foreach ([false, true] as $reviewRequired) {
            foreach ([false, true] as $ratingRequired) {
                $this->reviewRequired = $reviewRequired;
                $this->ratingRequired = $ratingRequired;
                $this->assertTrue($policy->check(7, 42, true)['error']);
                $this->assertFalse($policy->check(7, 42, false)['error']);
                $form = $policy->check(7, 42);
                $this->assertFalse($form['error']);
                $this->assertFalse($form['can_review']);
                $this->assertTrue($form['can_rate']);
            }
        }
    }

    public function test_login_is_required_for_ratings_and_reviews(): void
    {
        foreach ([null, false, true] as $writtenReview) {
            $result = (new ReviewEligibility)->check(null, 42, $writtenReview);
            $this->assertTrue($result['error']);
            $this->assertSame('login_required', $result['type']);
        }
    }

    public function test_only_a_completed_purchase_by_the_same_customer_qualifies(): void
    {
        $this->reviewRequired = $this->ratingRequired = true;
        DB::table('ec_orders')->insert([
            ['id' => 1, 'user_id' => 7, 'status' => 'pending'],
            ['id' => 2, 'user_id' => 8, 'status' => 'completed'],
            ['id' => 3, 'user_id' => 7, 'status' => 'completed'],
        ]);
        DB::table('ec_order_product')->insert([
            ['order_id' => 1, 'product_id' => 42],
            ['order_id' => 2, 'product_id' => 42],
            ['order_id' => 3, 'product_id' => 99],
        ]);
        $policy = new ReviewEligibility;
        $this->assertTrue($policy->check(7, 42, true)['error']);
        $this->assertFalse($policy->check(7, 42, false)['error']);
        DB::table('ec_order_product')->insert(['order_id' => 3, 'product_id' => 42]);
        $this->assertFalse($policy->check(7, 42, true)['error']);
        $this->assertFalse($policy->check(7, 42, false)['error']);
    }

    public function test_a_completed_variation_purchase_qualifies_for_its_parent(): void
    {
        $this->reviewRequired = $this->ratingRequired = true;
        DB::table('ec_orders')->insert(['id' => 1, 'user_id' => 7, 'status' => 'completed']);
        DB::table('ec_order_product')->insert(['order_id' => 1, 'product_id' => 99]);
        DB::table('ec_product_variations')->insert(['product_id' => 99, 'configurable_product_id' => 42]);
        $this->assertFalse((new ReviewEligibility)->check(7, 42, true)['error']);
        $this->assertFalse((new ReviewEligibility)->check(7, 42, false)['error']);
    }

    public function test_existing_written_review_cannot_be_submitted_again(): void
    {
        DB::table('ec_reviews')->insert(['customer_id' => 7, 'product_id' => 42, 'comment' => 'Written review']);
        foreach ([true, false] as $writtenReview) {
            $this->assertSame('already_reviewed', (new ReviewEligibility)->check(7, 42, $writtenReview)['type']);
        }
    }

    public function test_web_and_api_use_the_same_purchase_policy(): void
    {
        $customer = new Customer;
        $customer->id = 7;
        auth('customer')->setUser($customer);
        $request = Request::create('/');
        $request->setUserResolver(fn () => $customer);
        $web = new class
        {
            use CheckReviewConditionTrait { checkReviewCondition as public check; }
        };
        $api = new class
        {
            use CheckReviewConditionForApiTrait { checkReviewCondition as public check; }
        };
        foreach ([false, true] as $reviewRequired) {
            foreach ([false, true] as $ratingRequired) {
                $this->reviewRequired = $reviewRequired;
                $this->ratingRequired = $ratingRequired;
                foreach ([null, false, true] as $writtenReview) {
                    $this->assertSame($web->check(42, $writtenReview), $api->check(42, $request, $writtenReview));
                }
            }
        }
    }

    public function test_an_existing_star_rating_can_be_upgraded_after_purchase_without_counting_twice(): void
    {
        $submission = new \Botble\Ecommerce\Supports\SubmitCustomerReview;
        $rating = $submission->save(7, 42, ['star' => 4, 'comment' => '', 'status' => 'published']);
        $this->assertSame('already_reviewed', (new ReviewEligibility)->check(7, 42, false)['type']);
        $this->assertTrue((new ReviewEligibility)->check(7, 42, true)['error']);
        DB::table('ec_orders')->insert(['id' => 1, 'user_id' => 7, 'status' => 'completed']);
        DB::table('ec_order_product')->insert(['order_id' => 1, 'product_id' => 42]);
        $review = $submission->save(7, 42, ['star' => 5, 'comment' => 'Verified purchase review', 'status' => 'published']);
        $this->assertSame($rating->id, $review->id);
        $this->assertSame(1, DB::table('ec_reviews')->count());
        $this->assertSame('Verified purchase review', $review->comment);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $submission->save(7, 42, ['star' => 5, 'comment' => 'Duplicate', 'status' => 'published']);
    }

    public function test_only_a_confirmed_completed_service_for_the_same_customer_and_product_qualifies(): void
    {
        DB::table('mp_service_completions')->insert([
            ['customer_id' => 8, 'product_id' => 42, 'completed_at' => today()],
            ['customer_id' => 7, 'product_id' => 99, 'completed_at' => today()],
            ['customer_id' => 7, 'product_id' => 42, 'completed_at' => today()->addDay()],
        ]);
        $this->assertTrue((new ReviewEligibility)->check(7, 42, true)['error']);
        DB::table('mp_service_completions')->insert(['customer_id' => 7, 'product_id' => 42, 'completed_at' => today()]);
        $this->assertFalse((new ReviewEligibility)->check(7, 42, true)['error']);
    }
}
