<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreVisit extends BaseModel
{
    protected $table = 'mp_store_visits';

    protected $fillable = [
        'store_id',
        'visitor_key',
        'visit_date',
    ];

    protected $casts = [
        'visit_date' => 'date',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    /**
     * Record a visit for this store, deduped per visitor per day. Safe to call on every
     * store page load - a repeat visit from the same visitor on the same day is a no-op.
     */
    public static function track(Store $store, Request $request): void
    {
        $customerId = Auth::guard('customer')->id();

        $visitorKey = $customerId
            ? 'customer_' . $customerId
            : 'guest_' . sha1($request->ip() . '|' . $request->userAgent());

        try {
            static::query()->firstOrCreate([
                'store_id' => $store->getKey(),
                'visitor_key' => $visitorKey,
                'visit_date' => now()->toDateString(),
            ]);
        } catch (QueryException) {
            // Unique constraint hit from a concurrent request for the same visitor/day - fine, already recorded.
        }
    }
}
