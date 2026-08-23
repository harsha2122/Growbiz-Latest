<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class ProductClick extends BaseModel
{
    protected $table = 'mp_product_clicks';

    protected $fillable = [
        'product_id',
        'visitor_key',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // Every click is logged individually (not deduped), unlike store visits.
    public static function track(Product $product, Request $request): void
    {
        $visitorKey = auth('customer')->check()
            ? 'customer_' . auth('customer')->id()
            : 'guest_' . sha1($request->ip() . '|' . $request->userAgent());

        static::query()->create([
            'product_id' => $product->getKey(),
            'visitor_key' => $visitorKey,
        ]);
    }

    /**
     * @param 'today'|'7days'|'30days'|null $period null = all-time
     */
    public static function countForProduct(int|string $productId, ?string $period = null): int
    {
        $query = static::query()->where('product_id', $productId);

        match ($period) {
            'today' => $query->whereDate('created_at', now()->toDateString()),
            '7days' => $query->where('created_at', '>=', now()->subDays(7)),
            '30days' => $query->where('created_at', '>=', now()->subDays(30)),
            default => null,
        };

        return $query->count();
    }
}
