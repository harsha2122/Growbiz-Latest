<?php

namespace Botble\Marketplace\Models;

use Botble\ACL\Models\User;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorSubscription extends BaseModel
{
    protected $table = 'mp_vendor_subscriptions';

    protected $fillable = [
        'store_id',
        'plan_id',
        'starts_at',
        'expires_at',
        'status',
        'assigned_by',
        'notes',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function isActive(): bool
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function isExpired(): bool
    {
        if ($this->expires_at && $this->expires_at->isPast()) {
            return true;
        }

        return $this->status === self::STATUS_EXPIRED;
    }

    public function getDaysRemainingAttribute(): int
    {
        if (!$this->expires_at) {
            return -1; // Unlimited
        }

        if ($this->expires_at->isPast()) {
            return 0;
        }

        return (int) now()->diffInDays($this->expires_at);
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->isExpired()) {
            return __('Expired');
        }

        return match ($this->status) {
            self::STATUS_ACTIVE => __('Active'),
            self::STATUS_CANCELLED => __('Cancelled'),
            default => __('Unknown'),
        };
    }

    public function canCreateProduct(int $currentProductCount): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        if (!$this->plan) {
            return false;
        }

        if ($this->plan->max_products == 0) {
            return true; // Unlimited
        }

        return $currentProductCount < $this->plan->max_products;
    }

    public function getProductsRemainingAttribute(): int
    {
        if (!$this->plan) {
            return 0;
        }

        if ($this->plan->max_products == 0) {
            return -1; // Unlimited
        }

        $currentCount = $this->store ? $this->store->products()->count() : 0;

        return max(0, $this->plan->max_products - $currentCount);
    }

    public static function getActiveForStore(int $storeId): ?self
    {
        return self::query()
            ->where('store_id', $storeId)
            ->where('status', self::STATUS_ACTIVE)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->with('plan')
            ->latest()
            ->first();
    }
}
