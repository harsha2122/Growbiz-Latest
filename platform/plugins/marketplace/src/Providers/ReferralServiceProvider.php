<?php

namespace Botble\Marketplace\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Ecommerce\Events\OrderPlacedEvent;
use Botble\Marketplace\Models\CustomerReferral;
use Botble\Marketplace\Services\StoreReferralService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class ReferralServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(OrderPlacedEvent::class, function (OrderPlacedEvent $event): void {
            app(StoreReferralService::class)->trackOrder($event->order);
        });
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule): void {
            $schedule->call(function (): void {
                \Botble\Marketplace\Models\ReferralEvent::query()->where('status', 'awaiting_customer')->where('created_at', '<=', now()->subDays(7))->select('id')->chunkById(100, function ($events): void {
                    foreach ($events as $event) {
                        app(\Botble\Marketplace\Services\ReferralVerificationService::class)->expireUnconfirmed($event->id);
                    }
                });
                CustomerReferral::query()->where('status', 'tracked')->where('expires_at', '<=', now())
                    ->update(['status' => 'expired', 'reason' => __('The referral purchase window expired.')]);
                CustomerReferral::query()->whereIn('status', ['pending', 'available', 'paid'])->select('id')->chunkById(100, function ($rows): void {
                    foreach ($rows as $row) {
                        app(StoreReferralService::class)->reconcile($row->id);
                    }
                });
            })->name('marketplace:referral-rewards')->everyFiveMinutes()->withoutOverlapping();
        });
        DashboardMenu::for('vendor')->beforeRetrieving(function (): void {
            DashboardMenu::make()->registerItem([
                'id' => 'marketplace.vendor.refer-earn', 'priority' => 11, 'name' => __('Refer & Earn'),
                'url' => fn () => route('marketplace.vendor.refer-earn'), 'icon' => 'ti ti-gift',
            ]);
        });
        DashboardMenu::for('customer')->beforeRetrieving(function (): void {
            DashboardMenu::make()->registerItem([
                'id' => 'customer.refer-earn', 'priority' => 45, 'name' => __('Refer & Earn'),
                'url' => fn () => route('public.referral.account'), 'icon' => 'ti ti-gift',
            ]);
        });
        DashboardMenu::default()->beforeRetrieving(function (): void {
            DashboardMenu::make()->registerItem([
                'id' => 'marketplace.refer-earn', 'parent_id' => 'cms-plugins-marketplace', 'priority' => 12,
                'name' => __('Refer & Earn'), 'icon' => 'ti ti-gift', 'url' => fn () => route('marketplace.refer-earn.index'),
                'permissions' => ['marketplace.refer-earn.index'],
            ]);
        });
    }
}
