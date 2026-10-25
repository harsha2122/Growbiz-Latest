<?php

namespace Tests\Feature;

use Botble\Ecommerce\Models\Customer;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Http\Requests\Fronts\VendorStoreRequest;
use Botble\Marketplace\Http\Requests\StoreRequest;
use Botble\Marketplace\Models\Store;
use Composer\Autoload\ClassLoader;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreHighlightsTest extends TestCase
{
    protected ClassLoader $pluginLoader;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'store_highlights_test',
            'database.connections.store_highlights_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);

        // An empty test database has no activated-plugin settings.
        $this->pluginLoader = new ClassLoader;
        $this->pluginLoader->addPsr4('Botble\\Marketplace\\', base_path('platform/plugins/marketplace/src'));
        $this->pluginLoader->addPsr4('Botble\\Ecommerce\\', base_path('platform/plugins/ecommerce/src'));
        $this->pluginLoader->register();
        AliasLoader::getInstance()->alias('MarketplaceHelper', MarketplaceHelper::class);
        view()->addNamespace('plugins/marketplace', base_path('platform/plugins/marketplace/resources/views'));
    }

    protected function tearDown(): void
    {
        $this->pluginLoader->unregister();

        parent::tearDown();
    }

    public function test_seller_highlights_validate_optional_values_and_reject_invalid_input(): void
    {
        $rules = array_intersect_key((new StoreRequest)->rules(), array_flip([
            'completed_projects', 'years_of_experience', 'happy_customers', 'public_location',
        ]));

        $this->assertCount(4, $rules);

        foreach ([[], ['years_of_experience' => null, 'happy_customers' => null, 'public_location' => null], [
            'completed_projects' => 250, 'years_of_experience' => 12, 'happy_customers' => 12500, 'public_location' => 'Bengaluru, Karnataka',
        ], ['years_of_experience' => 0, 'happy_customers' => 0]] as $values) {
            $this->assertTrue(Validator::make($values, $rules)->passes());
        }

        foreach ([
            ['completed_projects' => -1], ['completed_projects' => 2.5], ['completed_projects' => 2147483648], ['years_of_experience' => -1], ['years_of_experience' => 151], ['years_of_experience' => 2.5],
            ['happy_customers' => -1], ['happy_customers' => 2147483648], ['happy_customers' => 'many'],
            ['public_location' => str_repeat('a', 121)], ['public_location' => ['Bengaluru']],
        ] as $values) {
            $this->assertTrue(Validator::make($values, $rules)->fails());
        }
    }

    public function test_highlights_can_be_saved_cleared_and_migrated_back(): void
    {
        // Isolated SQLite schema: never depend on or modify the sample MySQL data.
        Schema::create('mp_stores', function (Blueprint $table): void {
            $table->id();
            $table->timestamps();
        });

        $migration = require base_path('platform/plugins/marketplace/database/migrations/2026_10_24_000001_add_store_highlights_to_mp_stores.php');
        $migration->up();
        $projectsMigration = require base_path('platform/plugins/marketplace/database/migrations/2026_10_25_000001_add_completed_projects_to_mp_stores.php');
        $projectsMigration->up();

        Store::withoutEvents(function (): void {
            $store = new Store;
            $store->fill(['completed_projects' => '250', 'years_of_experience' => '12', 'happy_customers' => '12500', 'public_location' => 'Bengaluru']);
            $store->save();
            $store->refresh();

            $this->assertSame(250, $store->completed_projects);
            $this->assertSame(12, $store->years_of_experience);
            $this->assertSame(12500, $store->happy_customers);
            $this->assertSame('Bengaluru', $store->public_location);

            $store->fill(['completed_projects' => null, 'years_of_experience' => null, 'happy_customers' => null, 'public_location' => null])->save();
            $store->refresh();
            $this->assertNull($store->completed_projects);
            $this->assertNull($store->years_of_experience);
            $this->assertNull($store->happy_customers);
            $this->assertNull($store->public_location);
        });

        $projectsMigration->down();
        $this->assertFalse(Schema::hasColumn('mp_stores', 'completed_projects'));
        $migration->down();
        $this->assertFalse(Schema::hasColumn('mp_stores', 'years_of_experience'));
        $this->assertFalse(Schema::hasColumn('mp_stores', 'happy_customers'));
        $this->assertFalse(Schema::hasColumn('mp_stores', 'public_location'));
    }

    public function test_store_cards_format_numbers_escape_location_and_hide_empty_values(): void
    {
        MarketplaceHelper::shouldReceive('hideStoreAddress')->andReturn(false);
        $store = new Store(['is_minimal_profile' => true, 'completed_projects' => 250, 'years_of_experience' => 12, 'happy_customers' => 12500, 'public_location' => '<script>alert(1)</script>']);
        $html = view('plugins/marketplace::themes.includes.store-highlights', compact('store'))->render();

        $this->assertStringContainsString('12,500', $html);
        $this->assertStringContainsString('Years of experience', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('<script>', $html);

        $store = new Store(['is_minimal_profile' => true]);
        $this->assertStringNotContainsString('role="list"', view('plugins/marketplace::themes.includes.store-highlights', compact('store'))->render());

        $store->happy_customers = 0;
        $this->assertStringContainsString('Happy customers', view('plugins/marketplace::themes.includes.store-highlights', compact('store'))->render());
    }

    public function test_public_location_respects_the_store_address_privacy_setting(): void
    {
        MarketplaceHelper::shouldReceive('hideStoreAddress')->andReturn(true);
        $store = new Store(['is_minimal_profile' => true, 'public_location' => 'Private location', 'years_of_experience' => 5]);
        $html = view('plugins/marketplace::themes.includes.store-highlights', compact('store'))->render();

        $this->assertStringNotContainsString('Private location', $html);
        $this->assertStringContainsString('Years of experience', $html);
    }

    public function test_all_stores_show_profile_statistics_and_service_chips(): void
    {
        MarketplaceHelper::shouldReceive('hideStoreAddress')->andReturn(false);
        $store = new Store(['is_minimal_profile' => false, 'completed_projects' => 250, 'years_of_experience' => 12, 'happy_customers' => 12500, 'service_tags' => ['Interior design']]);
        $this->assertStringContainsString('role="list"', view('plugins/marketplace::themes.includes.store-highlights', compact('store'))->render());
        $this->assertStringContainsString('bb-store-chip', view('plugins/marketplace::themes.includes.store-service-chips', compact('store'))->render());
        $store->is_minimal_profile = true;
        $this->assertStringContainsString('Completed projects', view('plugins/marketplace::themes.includes.store-highlights', compact('store'))->render());
        $this->assertStringContainsString('Interior design', view('plugins/marketplace::themes.includes.store-service-chips', compact('store'))->render());
    }

    public function test_all_vendors_can_submit_profile_fields(): void
    {
        $store = new Store(['is_minimal_profile' => false]);
        $customer = new Customer;
        $customer->setRelation('store', $store);
        $request = new VendorStoreRequest;
        $request->setUserResolver(fn () => $customer);
        $values = ['completed_projects' => 250, 'happy_customers' => 12500, 'years_of_experience' => 12, 'service_tags_input' => 'Interior design'];
        $rules = array_intersect_key($request->rules(), $values);
        $this->assertTrue(Validator::make($values, $rules)->passes());
        $this->assertTrue(Validator::make([], $rules)->passes());
        $store->is_minimal_profile = true;
        $rules = array_intersect_key($request->rules(), $values);
        $this->assertTrue(Validator::make($values, $rules)->passes());
    }

    public function test_enquiry_access_expires_at_the_exact_deadline_and_can_be_revoked(): void
    {
        $this->travelTo(now()->startOfSecond());
        $visibility = new \Botble\Marketplace\Models\StoreEnquiryVisibility([
            'details_visible' => true, 'expires_at' => now()->addHour(),
        ]);
        $store = new Store;
        $store->setRelation('enquiryVisibility', $visibility);
        $this->assertTrue($store->enquiryDetailsVisible());
        $visibility->details_visible = false;
        $this->assertFalse($store->enquiryDetailsVisible());
        $visibility->details_visible = true;
        $this->travel(1)->hours();
        $this->assertFalse($store->enquiryDetailsVisible());
        $visibility->expires_at = now()->addDays(7);
        $this->assertTrue($store->enquiryDetailsVisible());
        $visibility->expires_at = null;
        $this->assertFalse($store->enquiryDetailsVisible());
        $this->travelBack();
    }

    public function test_enquiry_visibility_expiry_migration_persists_and_rolls_back(): void
    {
        Schema::create('mp_store_enquiry_visibility', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->boolean('details_visible');
            $table->timestamps();
        });
        $migration = require base_path('platform/plugins/marketplace/database/migrations/2026_10_26_000001_add_expiry_to_enquiry_visibility.php');
        $migration->up();
        $visibility = \Botble\Marketplace\Models\StoreEnquiryVisibility::create([
            'store_id' => 1, 'details_visible' => true, 'expires_at' => now()->addDays(7),
        ])->fresh();
        $this->assertTrue($visibility->isActive());
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $visibility->expires_at);
        Schema::create('mp_stores', function (Blueprint $table): void {
            $table->id();
        });
        \Illuminate\Support\Facades\DB::table('mp_stores')->insert(['id' => 1]);
        $controller = new \Botble\Marketplace\Http\Controllers\EnquiryController;
        foreach ([null, 0, -1, 8761, 'invalid'] as $duration) {
            try {
                $controller->toggleVisibility(\Illuminate\Http\Request::create('/', 'POST', [
                    'store_id' => 1, 'visible' => true, 'duration_hours' => $duration,
                ]));
                $this->fail('Invalid duration was accepted.');
            } catch (\Illuminate\Validation\ValidationException $exception) {
                $this->assertArrayHasKey('duration_hours', $exception->errors());
            }
        }
        $controller->toggleVisibility(\Illuminate\Http\Request::create('/', 'POST', [
            'store_id' => 1, 'visible' => true, 'duration_hours' => 24,
        ]));
        $this->assertTrue($visibility->fresh()->isActive());
        $controller->toggleVisibility(\Illuminate\Http\Request::create('/', 'POST', [
            'store_id' => 1, 'visible' => false,
        ]));
        $this->assertFalse($visibility->fresh()->isActive());
        $this->assertNull($visibility->fresh()->expires_at);
        $migration->down();
        $this->assertFalse(Schema::hasColumn('mp_store_enquiry_visibility', 'expires_at'));
    }
}
