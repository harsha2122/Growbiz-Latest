<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends BaseModel
{
    protected $table = 'mp_subscription_plans';

    protected $fillable = [
        'name',
        'description',
        'max_products',
        'duration_days',
        'price',
        'is_default',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'name' => SafeContent::class,
        'description' => SafeContent::class,
        'max_products' => 'integer',
        'duration_days' => 'integer',
        'price' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function vendorSubscriptions(): HasMany
    {
        return $this->hasMany(VendorSubscription::class, 'plan_id');
    }

    public static function getDefault(): ?self
    {
        return self::query()
            ->where('is_active', true)
            ->where('is_default', true)
            ->first();
    }

    public static function getActivePlans()
    {
        return self::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function getDurationTextAttribute(): string
    {
        if ($this->duration_days == 0) {
            return __('Unlimited');
        }

        if ($this->duration_days % 365 == 0) {
            $years = $this->duration_days / 365;
            return $years == 1 ? __('1 Year') : __(':count Years', ['count' => $years]);
        }

        if ($this->duration_days % 30 == 0) {
            $months = $this->duration_days / 30;
            return $months == 1 ? __('1 Month') : __(':count Months', ['count' => $months]);
        }

        return $this->duration_days == 1 ? __('1 Day') : __(':count Days', ['count' => $this->duration_days]);
    }

    public function getProductLimitTextAttribute(): string
    {
        if ($this->max_products == 0) {
            return __('Unlimited');
        }

        return (string) $this->max_products;
    }
}
