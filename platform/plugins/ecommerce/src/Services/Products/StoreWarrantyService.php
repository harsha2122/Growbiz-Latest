<?php

namespace Botble\Ecommerce\Services\Products;

use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Option\OptionType\RadioButton;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StoreWarrantyService
{
    public function save(Product $product, Request $request): void
    {
        if (! $product->isTypePhysical()) {
            $product->options()->where('is_warranty', true)->get()->each->delete();

            return;
        }
        if (! $request->has('has_warranty_settings')) {
            return;
        }
        DB::transaction(function () use ($product, $request): void {
            Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail();
            $query = $product->options()->where('is_warranty', true);
            if (! $product->isTypePhysical() || ! $request->boolean('warranty_enabled')) {
                $query->get()->each->delete();

                return;
            }
            if (! EcommerceHelper::isEnabledProductOptions()) {
                throw ValidationException::withMessages(['warranty_enabled' => __('Enable Product Options in Ecommerce settings before enabling a paid warranty.')]);
            }
            $option = $query->firstOrNew();
            $option->forceFill([
                'product_id' => $product->id, 'name' => __('Warranty'), 'option_type' => RadioButton::class,
                'required' => false, 'order' => 999, 'is_warranty' => true,
                'warranty_terms' => trim((string) $request->input('warranty_terms')),
                'included_warranty' => trim((string) $request->input('included_warranty')),
            ])->save();
            $option->values()->delete();
            $option->values()->create(['option_value' => __('No additional warranty'), 'affect_price' => 0, 'affect_type' => 0, 'order' => 0]);
            foreach (array_values($request->input('warranty_plans', [])) as $index => $plan) {
                $option->values()->create([
                    'option_value' => trim($plan['name']), 'affect_price' => $plan['price'], 'affect_type' => 0,
                    'warranty_months' => $plan['months'], 'order' => $index + 1,
                ]);
            }
        }, 3);
    }
}
