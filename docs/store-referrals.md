# Store Refer & Earn

This customer referral program is separate from the existing seller-invitation Referrals page.

## Access

- Sellers: Dashboard → Refer & Earn (`/vendor/refer-earn`). Each seller manages their own store only.
- Customers: My Account → Refer & Earn (`/customer/refer-earn`). Join from a participating store or product page.
- Admin: Marketplace → Refer & Earn. Grant `marketplace.refer-earn.index` to view and `marketplace.refer-earn.edit` to pause programs or reject unpaid rewards.

## Tracking and eligibility

A customer creates a shareable link after verifying their email. The referral link saves a store-specific attribution in the visitor's session for 30 days. Optional `?product=123` opens a published product belonging to that store; other product IDs fall back to the store. The visitor signs in, verifies their email, and chooses **Unlock referral offer**. This explicit POST prevents crawlers and link previews from creating paid visit rewards.

The latest link for a store wins before an offer is unlocked. Once unlocked, the referrer and program terms are fixed. A different store's referral cannot overwrite them. There is one referral per customer per store program. Store owners, self-referrals, and customers with an existing finished, non-cancelled/non-returned store order do not qualify. Unfinished checkout drafts are ignored. These controls prevent straightforward duplicates; verified accounts alone cannot identify every person operating multiple accounts.

- **Visit:** a fixed reward is reserved when the verified visitor unlocks the offer, then becomes available after 24 hours.
- **Purchase:** a reward is reserved on the customer's first eligible order from that store. Payment must be completed, the shipment delivered/audited, and the order completed or partially returned. Earnings remain pending for at least the platform return window after qualification. Open returns block release. Tax, shipping, discounts and recorded refunds are excluded from the reward base. Percentage rewards are capped if configured. Zero-value orders do not earn.
- Cancelled, fully returned or invalid-payment orders reject unpaid rewards and release their reserved budget. Partial refunds reduce the reward. Refunds after payment are flagged as **reversed**, preserving the original payment reference and budget expenditure for a manual seller adjustment. Money is not automatically recovered.
- Program edits affect new referrals. Existing unlocked terms continue until their original attribution deadline; order rewards already reserved continue through fulfilment even if the program is paused.

## Customer offers

Fixed and percentage offers issue an account-bound, single-use coupon scoped to the seller's store. The customer enters that code at checkout. A minimum eligible merchandise amount applies after the referral discount, excluding tax and shipping. Coupons cannot combine with promotions or flash sales. Existing checkout rules still apply.

Custom offers, such as a free gift or installation, are shown to the customer and attached to the qualifying order description. The seller fulfils these manually; they do not automatically add a gift SKU, adjust inventory, or deduct a checkout amount.

## Budgets and payouts

Budgets are seller commitments, not a funded wallet. Reservation and ledger changes run in transactions with row locks; repeated order events and payout submissions cannot award twice. Paid rewards continue to consume budget. Budget cannot be reduced below reserved and paid commitments.

The referrer requests payment from their account after earnings become available. The seller sees the request and referrer's account email, pays them externally, then records the transaction reference. Recording payment does not initiate a bank transfer. Admins can reject suspicious unpaid referrals with a reason; the ledger retains the reason and any earlier payment details.

## Deployment

```sh
cd /var/www/Growbiz-Latest
git pull --ff-only origin work
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan optimize:clear
```

Laravel's scheduler must run every minute. If not already installed, add this entry to the `www-data` crontab:

```cron
* * * * * cd /var/www/Growbiz-Latest && php artisan schedule:run >> /dev/null 2>&1
```

Referral reconciliation runs every five minutes. The account and payout actions also recheck eligibility. No queue worker, npm build, payment-provider keys, or external transfer integration is required for this version.

The store video CTA now reads **Watch Offer Video**.
