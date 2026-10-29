# Product and profile review policy

- Login is required for all customer ratings and reviews.
- Stars alone never require a purchase.
- Written product reviews and photos require a completed order containing that product (or its variation), or an admin-confirmed completion for that listed service.
- Written profile reviews require a completed order from that vendor or an admin-confirmed service from that vendor.
- The purchase requirement cannot be disabled for written reviews or enabled for stars. Previous purchase-policy toggles have been replaced with a policy notice in Marketplace and Ecommerce review settings. Review enablement, approval and customer-name privacy settings still apply.

## Profile-only vendors

A Ratings & Reviews section appears on profile-only store pages. One rating per customer/vendor is maintained, and the customer can add a written review later after qualifying. Updating stars without text preserves an earlier written review. Sellers cannot rate their own profile.

Profile averages/counts use only published profile submissions, including stars-only ratings. Normal vendors continue to use published product ratings. Profile feedback is not mixed into product ratings. Switching profile-only mode off hides the profile section but retains its data.

Profile-only sellers see their own feedback under Dashboard → Reviews. Admins moderate it under Marketplace → Profile Reviews & Services. The existing Ecommerce review approval setting also applies to written profile reviews.

## Confirming a service

WhatsApp Book Now creates an enquiry, not proof that a service was received. After independently confirming completion, an admin opens Marketplace → Profile Reviews & Services and records:

1. Vendor and customer's registered account email.
2. A listed service, when applicable (optional for offline profile services).
3. Completion date and booking/invoice reference.

This unlocks written reviews for that customer/vendor. Selecting a listed service also unlocks that service's product review. A confirmation for one service does not unlock unrelated products or other vendors. Future completion dates and cross-vendor service selections are rejected. Admin identity is recorded; confirmations can be revoked.

Permissions: `marketplace.profile-reviews.index` to view and `marketplace.profile-reviews.edit` to moderate/confirm/revoke.

## Deployment

```sh
cd /var/www/Growbiz-Latest
git pull --ff-only origin work
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan optimize:clear
```

No new scheduled task or asset build is required.
