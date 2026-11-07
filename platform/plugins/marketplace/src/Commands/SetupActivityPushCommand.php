<?php

namespace Botble\Marketplace\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Minishlink\WebPush\VAPID;

class SetupActivityPushCommand extends Command
{
    protected $signature = 'marketplace:notifications-setup';

    protected $description = 'Create server push keys once, preserving existing browser subscriptions';

    public function handle(): int
    {
        if (setting('activity_push_public_key') && setting('activity_push_private_key')) {
            $this->info('Background notifications are already configured. Existing keys were kept.');

            return self::SUCCESS;
        }
        $keys = VAPID::createVapidKeys();
        setting()->set([
            'activity_push_public_key' => $keys['publicKey'],
            'activity_push_private_key' => Crypt::encryptString($keys['privateKey']),
            'activity_push_enabled_at' => now()->toIso8601String(),
        ])->save();
        $this->info('Background push configured. Keep the Laravel scheduler running every minute. Customers must enable Phone alerts on their device.');

        return self::SUCCESS;
    }
}
