# Product and profile review policy

- Login is required to publish ratings and reviews. Guests can see and select profile stars, then submit to sign in. Their choice is kept in the session and they return to the profile to confirm submission; login alone never publishes a rating.
- Stars alone never require a purchase.
- Written product reviews and photos require a completed order containing that product (or its variation), or an admin-confirmed completion for that listed service.
- Written profile reviews require a completed order from that vendor or a seller/admin-confirmed service from that vendor.
- The purchase requirement cannot be disabled for written reviews or enabled for stars. Previous purchase-policy toggles have been replaced with a policy notice in Marketplace and Ecommerce review settings. Product review enablement still applies to product reviews. Minimal profiles always enable ratings/reviews independently of that switch; approval and customer-name privacy settings still apply.

## Profile-only vendors

A visible star form appears on profile-only store pages even for guests, with a Ratings & Reviews shortcut beside the store name. No stars are selected by default for a first-time visitor. One rating per customer/vendor is maintained, and the customer can add a written review later after qualifying. Updating stars without text preserves an earlier written review. Sellers cannot rate their own profile.

Profile averages/counts use only published profile submissions, including stars-only ratings. Normal vendors continue to use published product ratings. Profile feedback is not mixed into product ratings. Switching profile-only mode off hides the profile section but retains its data.

Profile-only sellers see their own feedback under Dashboard → Reviews. Admins moderate it under Marketplace → Profile Reviews & Services. The existing Ecommerce review approval setting also applies to written profile reviews.

## Confirming a service

WhatsApp Book Now creates an enquiry, not proof that a service was received. A profile-only seller can open Seller Dashboard → Reviews → Confirm a completed service, enter the verified customer account email, completion date and invoice/service reference. This is scoped to their own store, cannot confirm their own experience and records the seller identity separately from the admin identity. Repeating the same customer/reference reuses the existing confirmation. This unlocks a written profile review, without creating a product/service listing. Admins retain moderation and revocation control.

Alternatively, after independently confirming completion, an admin opens Marketplace → Profile Reviews & Services and records:

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
