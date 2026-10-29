<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralProgram extends BaseModel
{
    protected $table = 'mp_referral_programs';

    protected $guarded = ['id', 'reserved'];

    protected $casts = ['enabled' => 'boolean', 'ends_at' => 'datetime'];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function isActive(): bool
    {
        return $this->enabled && (! $this->ends_at || $this->ends_at->isFuture())
            && $this->store?->status == 'published';
    }

    public function snapshot(): array
    {
        return $this->only(['mode', 'reward_type', 'reward_value', 'reward_cap', 'offer_type', 'offer_value', 'offer_text', 'minimum_order', 'hold_days']);
    }
}
