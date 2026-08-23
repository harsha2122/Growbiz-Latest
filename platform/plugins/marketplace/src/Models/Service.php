<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends BaseModel
{
    protected $table = 'mp_services';

    protected $fillable = [
        'store_id',
        'name',
        'description',
        'image',
        'price',
        'status',
        'order',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'name' => SafeContent::class,
        'description' => SafeContent::class,
        'price' => 'float',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(ServiceClick::class, 'service_id');
    }

    /**
     * @param 'today'|'7days'|'30days'|null $period null = all-time
     */
    public function clicksCount(?string $period = null): int
    {
        $query = $this->clicks();

        match ($period) {
            'today' => $query->whereDate('created_at', now()->toDateString()),
            '7days' => $query->where('created_at', '>=', now()->subDays(7)),
            '30days' => $query->where('created_at', '>=', now()->subDays(30)),
            default => null,
        };

        return $query->count();
    }
}
