<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceClick extends BaseModel
{
    protected $table = 'mp_service_clicks';

    protected $fillable = [
        'service_id',
        'visitor_key',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    /**
     * Every "Book Now" click is a meaningful intent signal, so - unlike store visits -
     * these are NOT deduped; each click is logged.
     */
    public static function track(Service $service, Request $request): void
    {
        $customerId = Auth::guard('customer')->id();

        $visitorKey = $customerId
            ? 'customer_' . $customerId
            : 'guest_' . sha1($request->ip() . '|' . $request->userAgent());

        static::query()->create([
            'service_id' => $service->getKey(),
            'visitor_key' => $visitorKey,
        ]);
    }
}
