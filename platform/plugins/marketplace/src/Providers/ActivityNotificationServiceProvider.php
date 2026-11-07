<?php

namespace Botble\Marketplace\Providers;

use Botble\Ecommerce\Events\OrderCreated;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Shipment;
use Botble\Marketplace\Models\CustomerReferral;
use Botble\Marketplace\Models\Enquiry;
use Botble\Marketplace\Models\ProfileReview;
use Botble\Marketplace\Models\ServiceCompletion;
use Botble\Marketplace\Models\StoreEnquiryVisibility;
use Botble\Marketplace\Models\VendorSubscription;
use Botble\Marketplace\Services\ActivityNotificationService;
use Botble\Payment\Models\Payment;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class ActivityNotificationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([\Botble\Marketplace\Commands\SetupActivityPushCommand::class]);
        }
        $this->callAfterResolving(\Illuminate\Console\Scheduling\Schedule::class, function ($schedule): void {
            $schedule->call(fn () => app(\Botble\Marketplace\Services\ActivityPushService::class)->sendPending())
                ->name('marketplace:activity-push')->everyMinute()->withoutOverlapping();
        });
        Event::listen('eloquent.created: '.Enquiry::class, function (Enquiry $enquiry): void {
            app(ActivityNotificationService::class)->seller($enquiry->store_id, 'enquiry', $enquiry->id, __('New enquiry'), __('You have a new enquiry. Open Enquiries to view it.'), 'enquiry:'.$enquiry->id);
        });
        Event::listen(OrderCreated::class, fn (OrderCreated $event) => app(ActivityNotificationService::class)->order($event->order->fresh(), true));
        Event::listen('eloquent.updated: '.Order::class, function (Order $order): void {
            if ($order->wasChanged('store_id') && $order->store_id) {
                app(ActivityNotificationService::class)->order($order, true);
            } elseif ($order->wasChanged('status')) {
                app(ActivityNotificationService::class)->order($order);
            }
        });
        Event::listen('eloquent.updated: '.Shipment::class, function (Shipment $shipment): void {
            if ($shipment->wasChanged('status') && $order = Order::find($shipment->order_id)) {
                app(ActivityNotificationService::class)->order($order, false, __('Shipping status: :status', ['status' => (string) $shipment->status]));
            }
        });
        Event::listen('eloquent.updated: '.Payment::class, function (Payment $payment): void {
            if ($payment->wasChanged('status')) {
                foreach (Order::query()->where('payment_id', $payment->id)->get() as $order) {
                    app(ActivityNotificationService::class)->order($order, false, __('Payment status: :status', ['status' => (string) $payment->status]));
                }
            }
        });
        Event::listen('eloquent.saved: '.VendorSubscription::class, function (VendorSubscription $subscription): void {
            if ($subscription->wasRecentlyCreated || $subscription->wasChanged(['status', 'expires_at', 'plan_id'])) {
                app(ActivityNotificationService::class)->seller($subscription->store_id, 'subscription', $subscription->id, __('Subscription updated'), __('Your subscription has changed. Check your plan and expiry date.'));
            }
        });
        Event::listen('eloquent.saved: '.StoreEnquiryVisibility::class, function (StoreEnquiryVisibility $visibility): void {
            if ($visibility->wasRecentlyCreated || $visibility->wasChanged(['details_visible', 'expires_at'])) {
                app(ActivityNotificationService::class)->seller($visibility->store_id, 'enquiry', 0, __('Enquiry access updated'), __('Your enquiry access has changed. Open Enquiries to check availability.'));
            }
        });
        Event::listen('eloquent.created: '.ServiceCompletion::class, function (ServiceCompletion $completion): void {
            app(ActivityNotificationService::class)->send($completion->customer_id, null, 'customer', 'service', $completion->id, __('Service confirmed'), __('Your completed service has been recorded. You can now submit an eligible review.'));
        });
        Event::listen('eloquent.created: '.ProfileReview::class, function (ProfileReview $review): void {
            app(ActivityNotificationService::class)->seller($review->store_id, 'review', $review->id, __('New profile rating'), __('A customer has rated your profile. Reviews may require moderation.'));
        });
        Event::listen('eloquent.updated: '.CustomerReferral::class, function (CustomerReferral $referral): void {
            if ($referral->wasChanged('status')) {
                $service = app(ActivityNotificationService::class);
                $message = __('Referral status: :status', ['status' => str_replace('_', ' ', $referral->status)]);
                $service->send($referral->referrer_id, null, 'customer', 'referral', $referral->id, __('Referral earnings updated'), $message);
                $service->send($referral->customer_id, null, 'customer', 'referral', $referral->id, __('Referral activity updated'), $message);
            }
        });
    }
}
