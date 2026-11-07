<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Models\BaseModel;

class ActivityPushSubscription extends BaseModel
{
    protected $table = 'mp_activity_push_subscriptions';

    protected $guarded = ['id'];

    protected $casts = ['subscription' => 'encrypted:array'];
}
