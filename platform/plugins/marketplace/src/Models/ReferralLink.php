<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralLink extends BaseModel
{
    protected $table = 'mp_referral_links';

    protected $guarded = ['id'];

    public function program(): BelongsTo
    {
        return $this->belongsTo(ReferralProgram::class, 'program_id');
    }
}
