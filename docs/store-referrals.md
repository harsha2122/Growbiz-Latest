# Store Refer & Earn

The customer referral program is separate from the seller-invitation Referrals page. Sellers fund rewards and record external payments; the module does not transfer money automatically.

## Seller setup

Seller Dashboard → Refer & Earn offers three formats:

- **Qualified enquiry:** a signed-in, verified new customer unlocks the offer and submits an enquiry using their account email. Submitted Book Now/WhatsApp enquiry forms attach the signed-in account email automatically. Admin verifies the enquiry. Sellers can additionally require a completed-service confirmation from Profile Reviews & Services before admin approval. Merely opening WhatsApp is not a completed or verified enquiry.
- **Store visit:** choose online, offline, or both. An online visit qualifies when the verified customer unlocks the offer. Offline visits require a store record, customer confirmation and admin approval.
- **Product purchase:** choose online, offline, or both. Online rewards require completed payment, delivered/audited shipment and a completed order. Offline purchases require a receipt, customer confirmation and admin approval.

Visit and enquiry rewards are fixed amounts. Purchase rewards support a fixed amount or percentage with an optional cap. Sellers set their budget, friend offer, minimum retained purchase amount, waiting period, program expiry and additional exclusions. An optional JPG/PNG/WebP image up to 2 MB can replace the default animated gift graphic.

The front card shows the offer and a primary action. Full conditions load from a dedicated page in a responsive, accessible popup. The standalone terms URL also works without JavaScript. Reduced-motion preferences disable the illustration animation.

## Attribution and one-reward rule

The referrer signs in, verifies their email and joins from the store/product page. Their account shows a unique link, short referral code and shareable QR. Public links save store-specific attribution in the browser session for 30 days. The latest link wins before unlocking; another store cannot overwrite it. The referred customer signs in, verifies their email and unlocks the offer. Terms are then snapshotted, including format, channel, reward, friend offer, waiting period and exclusions.

There is one referral and one qualifying reward per customer per store program. Self-referrals, existing store customers and duplicate rewards are blocked. A visit followed by a purchase does not stack rewards. In a visit program with both channels enabled, an online unlock already qualifies the visit, so a subsequent offline visit does not earn another reward. Choose a purchase program when a purchase should be required.

Customers must have a registered, verified account for offline attribution too. A seller can start attribution from the referrer's short code plus that customer's registered email; this does not award an online visit. Anonymous walk-in contacts must register and verify before participating. The system cannot independently discover historical offline purchases without a POS integration; admin verifies first-customer eligibility with store records.

## Offline flow

1. The friend shows their private referral pass/QR from My Account → Refer & Earn. Alternatively, the seller uses their friend's short code plus the customer's registered email.
2. Seller Dashboard → Refer & Earn → Record / view offline activity: enter the code, date, invoice/visit reference and, for purchases, merchandise subtotal, discount, tax, shipping and receipt.
3. Activity must occur on or after attribution and within the attribution period. Amounts use platform base currency and dates use application timezone.
4. The customer confirms or declines in their account or on the private pass page. Confirmation must arrive within seven days; unconfirmed records then expire and release budget.
5. Admin → Marketplace → Refer & Earn: inspect the customer, receipt/enquiry and audit trail, then approve or reject with a reason. Approval is blocked until customer confirmation. An enquiry requiring completed service is blocked until that completion has been recorded.
6. Visit/enquiry rewards wait 24 hours after qualification/approval. Purchase rewards wait at least the platform return window after qualification/approval.

A scan proves presentation of a pass, not a purchase. Receipts (JPG/PNG/PDF, up to 5 MB) are stored on the private local disk and downloaded only through authorized admin, owning seller or referred-customer routes. Referrers and unrelated users cannot download their friend's receipt. Normalized invoice references and identical receipt files cannot qualify twice within a store.

## Budget, refunds and payouts

Rewards reserve budget atomically before qualifying records are accepted. Program/referral row locks serialize competing online/offline events; retries cannot award twice. Paid rewards continue to consume budget. Budget cannot be lowered below committed/paid rewards.

Customers request payment after earnings become available. Sellers pay externally and record the reference. Admin approval is not a bank transfer. The ledger distinguishes awaiting customer, awaiting admin, pending, available, paid, rejected and reversed states.

Online cancellations/returns/refunds use the existing order reconciliation. For offline purchases, admin records the cumulative merchandise refund against the approved receipt. Percentage rewards are recalculated from retained merchandise value, excluding discounts, tax and shipping; falling below the minimum rejects unpaid rewards and releases budget. Refunds after payout flag a manual adjustment and preserve the payment record; money is not automatically recovered.

Approvals, rejections, customer decisions, refunds and recorded payments retain actor/time audit records. A removed enquiry or required service-completion proof invalidates its pending reward. Existing accepted terms continue even if the seller later edits or pauses the program.

## Friend offers

Online-enabled programs issue personal, single-use store coupons for configured discounts. They cannot combine with promotions or flash sales. Offline-only programs do not issue an online coupon. Offline discounts and custom gifts/services are fulfilled by the seller and reflected on the receipt; a coupon does not automatically apply at an offline till. A custom offer does not add a gift SKU or adjust inventory automatically.

## Permissions and deployment

- Seller: Dashboard → Refer & Earn and Offline referrals, restricted to their own store.
- Customer: My Account → Refer & Earn for links, offers, passes, confirmations and earnings.
- Admin: Marketplace → Refer & Earn. `marketplace.refer-earn.index` allows viewing; `marketplace.refer-earn.edit` allows verification, refunds, pausing and rejection.

```sh
cd /var/www/Growbiz-Latest
git pull --ff-only origin work
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan optimize:clear
```

Keep Laravel's existing scheduler running every minute. Referral reconciliation and unconfirmed-activity expiry run every five minutes:

```cron
* * * * * cd /var/www/Growbiz-Latest && php artisan schedule:run >> /dev/null 2>&1
```

No asset build, new external QR service or payment-provider keys are needed. Existing online programs default to the online channel, and old referral snapshots retain their original behavior.
