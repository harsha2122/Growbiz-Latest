# Seller and customer notifications

## What users see

- The seller dashboard shows **new enquiries** and **total enquiries**. Counts refresh every 30 seconds while the page is active, and when returning to the tab.
- Opening Enquiries marks all existing accessible enquiries viewed, up to the request’s starting ID. Enquiries arriving after that snapshot remain new. Pagination does not leave older pages unread.
- Hidden/expired enquiry access shows counts without contact details and does not mark enquiries viewed.
- A notification bell opens a drawer on seller pages and signed-in customer pages/storefronts. It shows the latest 30 updates and an unread badge. Opening the drawer alone does not clear it. Clicking an item marks that notification read, and **Mark all read** clears only the current notification snapshot. This does not mark enquiries viewed.
- **Enable sound** opts into an in-page beep. Browsers require a user interaction to unlock audio after navigation. Preferences belong to that signed-in account and seller/customer context on the device.
- **Phone alerts** opts into background Web Push on supported browsers over HTTPS. iPhone/iPad users need iOS/iPadOS 16.4+ and must add the site to the Home Screen and open it there. OS permissions, battery controls, browser support and Do Not Disturb determine delivery and sound. Background alerts cannot guarantee a beep.
- Phone alerts are generic, without customer contact details or amounts. They open the authenticated dashboard/account. Disabling Phone alerts unsubscribes that device. Accounts on the same browser replace previous accounts’ subscriptions when enabling alerts.

## Events currently covered

Seller: new enquiries; enquiry-access changes; new orders; order/payment/shipping status changes; subscription assignment/status/expiry-date changes; new profile ratings.

Customer: new orders and order/payment/shipping changes; referral status/earnings changes; confirmed service completion. New-order events are deduplicated even when the store assignment and order-created event both fire.

Notifications are persisted server-side. These listeners cover application model/event updates; direct SQL or mass query updates bypass Eloquent events. The automated expiry job that hides enquiry details does not generate an extra notification, but access enforcement still takes effect immediately. New event types can use `ActivityNotificationService`.

## Production deployment

From the existing checkout, using the normal deployment account:

```bash
cd /var/www/Growbiz-Latest
sudo -u www-data php artisan down
git pull --ff-only origin work
composer install --no-dev --prefer-dist --optimize-autoloader
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan marketplace:notifications-setup
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan up
```

The setup command generates a VAPID keypair once. The private key and browser subscription data are encrypted with Laravel's application key. Re-running setup keeps existing keys/subscriptions. Preserve `APP_KEY` and the database backups; do not copy private keys into source control.

The existing Laravel scheduler must run every minute. If not already configured, add **one** cron entry for the web user:

```cron
* * * * * cd /var/www/Growbiz-Latest && php artisan schedule:run >> /dev/null 2>&1
```

`marketplace:activity-push` sends pending unread notifications once a minute. Only updates from after setup and within the last hour are eligible. Delivery retries at most three scheduled attempts; expired browser subscriptions are removed. It uses encrypted `aes128gcm` payloads and HTTPS endpoints restricted to known Chrome/Firefox/Apple/Windows push gateways, with redirects disabled. Up to 10 device subscriptions per recipient context are kept. The sender limits each run to approximately 45 seconds and will continue next minute.

Serve `/growbiz-notifications-sw.js`, `/growbiz-notifications.webmanifest` and `/growbiz-notification-icon.svg` directly from `public/` over HTTPS. The server needs outbound HTTPS to the configured browser's push gateway. No Firebase project or paid notification provider is required. The service worker does not cache customer pages or intercept requests.

## Validation

Automated tests cover snapshot boundaries, tenant isolation at service level, recipient deduplication, both order recipients, push endpoint restrictions, encrypted storage, encrypted mocked delivery, expired subscriptions, and duplicate dispatch prevention. Browser checks cover enquiry privacy, counts, read-state separation, seller/customer isolation, phone drawer, audio opt-in and mocked push subscription opt-in/out. No real push messages are sent by these tests. Verify one opted-in Android/iPhone device after production deployment to confirm the server's outbound connectivity and device permission settings.
