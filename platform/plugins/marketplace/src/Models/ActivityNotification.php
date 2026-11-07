<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Models\BaseModel;

class ActivityNotification extends BaseModel
{
    protected $table = 'mp_activity_notifications';

    protected $guarded = ['id'];

    protected $casts = ['read_at' => 'datetime'];
}
