<?php

namespace Tests\Feature;

use Botble\Ecommerce\Cart\Cart;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Services\Products\StoreWarrantyService;
use Botble\Ecommerce\Supports\OrderHelper;
use Composer\Autoload\ClassLoader;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProductWarrantyTest extends TestCase
{
    private ClassLoader $loader;

    private Product $product;

    private StoreWarrantyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'warranty_test', 'database.connections.warranty_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ]]);
        $this->loader = new ClassLoader;
        $this->loader->addPsr4('Botble\\Ecommerce\\', base_path('platform/plugins/ecommerce/src'));
        $this->loader->register();
        Schema::create('ec_products', function (Blueprint $t): void {
            $t->id();
            $t->string('product_type');
            $t->boolean('is_variation')->default(false);
            $t->integer('store_id')->nullable();
            $t->timestamps();
        });
        Schema::create('mp_stores', function (Blueprint $t): void {
            $t->id();
            $t->string('status');
        });
        Schema::create('ec_options', function (Blueprint $t): void {
            $t->id();
            $t->integer('product_id');
            $t->string('name');
            $t->string('option_type');
            $t->boolean('required')->default(false);
            $t->integer('order')->default(0);
            $t->timestamps();
        });
        Schema::create('ec_option_value', function (Blueprint $t): void {
            $t->id();
            $t->integer('option_id');
            $t->text('option_value');
            $t->decimal('affect_price', 14, 2);
            $t->integer('affect_type');
            $t->integer('order');
            $t->timestamps();
        });
        (require base_path('platform/plugins/ecommerce/database/migrations/2026_10_31_000001_add_product_warranty_options.php'))->up();
        DB::table('ec_products')->insert(['id' => 1, 'product_type' => 'physical']);
        $this->product = Product::withoutGlobalScopes()->findOrFail(1);
        $this->service = new StoreWarrantyService;
    }

    protected function tearDown(): void
    {
        $this->loader->unregister();
        parent::tearDown();
    }

    private function request(array $changes = []): Request
    {
        return Request::create('/', 'POST', array_replace([
            'has_warranty_settings' => 1, 'warranty_enabled' => 1,
            'included_warranty' => 'Manufacturer warranty included', 'warranty_terms' => 'Covers manufacturing defects. No accidental damage.',
            'warranty_plans' => [['name' => '1 year extended warranty', 'months' => 12, 'price' => 500]],
        ], $changes));
    }

    public function test_warranty_is_saved_as_optional_paid_product_option_and_can_be_disabled(): void
    {
        $this->service->save($this->product, $this->request());
        $option = $this->product->options()->where('is_warranty', true)->with('values')->firstOrFail();
        $this->assertFalse((bool) $option->required);
        $this->assertCount(2, $option->values);
        $this->assertEquals(0, $option->values->first()->affect_price);
        $this->assertEquals(500, $option->values->last()->affect_price);
        $this->assertEquals(12, $option->values->last()->warranty_months);
        $this->service->save($this->product, $this->request(['warranty_enabled' => 0]));
        $this->assertSame(0, $this->product->options()->where('is_warranty', true)->count());
        $this->assertSame(0, DB::table('ec_option_value')->count());
    }

    public function test_server_prices_selection_and_snapshot_survives_configuration_changes(): void
    {
        $this->service->save($this->product, $this->request());
        $option = $this->product->options()->where('is_warranty', true)->firstOrFail();
        $input = [$option->id => ['option_type' => 'field', 'values' => '1 year extended warranty', 'affect_price' => 1]];
        $data = app(OrderHelper::class)->getProductOptionData($input, 1);
        $value = $data['optionCartValue'][$option->id][0];
        $this->assertEquals(500, $value['affect_price']);
        $this->assertSame('radio', $value['option_type']);
        $this->assertSame('delivery', $value['warranty']['starts']);
        $this->assertEquals(12, $value['warranty']['months']);
        $unitPrice = app(Cart::class)->getPriceByOptions(10000, $data);
        $this->assertEquals(10500, $unitPrice);
        $this->assertEquals(21000, 2 * $unitPrice);
        $this->service->save($this->product, $this->request(['warranty_terms' => 'Changed terms', 'warranty_plans' => [['name' => 'New plan', 'months' => 24, 'price' => 900]]]));
        $this->assertSame('Covers manufacturing defects. No accidental damage.', $value['warranty']['terms']);
        $this->assertEquals(500, $value['affect_price']);
    }

    public function test_cross_product_or_stale_selections_are_rejected(): void
    {
        $this->service->save($this->product, $this->request());
        $option = $this->product->options()->where('is_warranty', true)->firstOrFail();
        foreach ([[$option->id => ['option_type' => 'radio', 'values' => 'Unavailable plan']], [$option->id => ['option_type' => 'radio', 'values' => ['No additional warranty', '1 year extended warranty']]], [$option->id => ['option_type' => 'radio', 'values' => [['invalid' => 'nested']]]]] as $data) {
            try {
                app(OrderHelper::class)->getProductOptionData($data, 1);
                $this->fail('Invalid warranty was accepted');
            } catch (ValidationException) {
                $this->assertTrue(true);
            }
        }
        $this->expectException(ValidationException::class);
        app(OrderHelper::class)->getProductOptionData([$option->id => ['option_type' => 'radio', 'values' => '1 year extended warranty']], 2);
    }

    public function test_non_physical_product_cannot_keep_paid_warranty(): void
    {
        $this->service->save($this->product, $this->request());
        $this->product->product_type = 'service';
        $this->service->save($this->product, Request::create('/', 'POST'));
        $this->assertSame(0, $this->product->options()->where('is_warranty', true)->count());
    }

    public function test_request_validation_rejects_invalid_prices_durations_and_duplicate_names(): void
    {
        $rules = array_filter((new \Botble\Ecommerce\Http\Requests\ProductRequest)->rules(), fn ($key) => str_starts_with($key, 'warranty_'), ARRAY_FILTER_USE_KEY);
        $this->assertTrue(Validator::make($this->request()->all(), $rules)->passes());
        foreach ([['price' => -10], ['price' => '0.001'], ['months' => 0], ['months' => 121], ['name' => 'No additional warranty']] as $invalid) {
            $values = $this->request(['warranty_plans' => [array_replace(['name' => 'Plan', 'months' => 12, 'price' => 500], $invalid)]])->all();
            $this->assertTrue(Validator::make($values, $rules)->fails());
        }
        $values = $this->request()->all();
        $values['warranty_plans'][] = $values['warranty_plans'][0];
        $this->assertTrue(Validator::make($values, $rules)->fails());
        $values['warranty_plans'] = [];
        $this->assertTrue(Validator::make($values, $rules)->fails());
    }

    public function test_warranty_migration_rolls_back(): void
    {
        (require base_path('platform/plugins/ecommerce/database/migrations/2026_10_31_000001_add_product_warranty_options.php'))->down();
        $this->assertFalse(Schema::hasColumn('ec_options', 'is_warranty'));
        $this->assertFalse(Schema::hasColumn('ec_option_value', 'warranty_months'));
    }

    public function test_color_variations_use_their_parent_warranty_options(): void
    {
        Schema::create('ec_product_variations', function (Blueprint $t): void {
            $t->id();
            $t->integer('product_id');
            $t->integer('configurable_product_id');
        });
        DB::table('ec_products')->insert(['id' => 2, 'product_type' => 'physical', 'is_variation' => true]);
        DB::table('ec_product_variations')->insert(['id' => 1, 'product_id' => 2, 'configurable_product_id' => 1]);
        $this->service->save($this->product, $this->request());
        $option = $this->product->options()->where('is_warranty', true)->firstOrFail();
        $data = app(OrderHelper::class)->getProductOptionData([$option->id => ['option_type' => 'radio', 'values' => '1 year extended warranty']], 2);
        $this->assertEquals(12500, app(Cart::class)->getPriceByOptions(12000, $data));
    }
}
