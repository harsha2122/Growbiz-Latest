# Physical-product warranties and colors

## Configure a warranty

Edit a physical product in either the admin panel or Seller Dashboard → Products. Open **Warranty** and enable **Warranty available**.

- Optionally describe the included manufacturer warranty.
- Add up to ten paid plans, each with a unique name, duration in months, and fixed additional price per unit.
- Enter coverage, exclusions, and claim contact details. Explain whether this overlaps or extends included manufacturer coverage.
- Save the product. Buyers start with **No additional warranty** selected.

Product Options must be enabled in Admin → Ecommerce → Settings → Products. Warranty settings are not offered for services or digital products.

## Purchase behavior

The selector displays each add-on charge, coverage duration, expandable terms, and the unit price including selected options. It works with ordinary product variations and the existing custom size/color price selector. Warranty pricing is resolved from stored product options on the server. Browser-supplied option prices and custom variation prices are not trusted.

Example: product 10,000 + warranty 500 = 10,500 per unit. Two units cost 21,000 before applicable tax, shipping, and discounts. The charge follows existing product tax and coupon rules. It is included in the purchased item's unit price rather than a separate warranty SKU.

Different warranty selections remain separate cart rows but share the physical product/variation stock limit. Quantity updates cannot change prices or option snapshots. Removing or refunding an entire item uses the warranty-inclusive item price under the existing order/refund process; this feature does not add a separate warranty-only refund or claims workflow.

The order and invoice retain the plan, additional charge, coverage duration, included warranty text, and coverage terms captured at add-to-cart time. Subsequent catalog edits do not change those saved snapshots. Coverage is described as starting from delivery; no certificate or expiry-reminder system is created.

The default invoice template prints the selected plan/charge/duration and coverage terms. Existing customized invoice templates retain their layout; their Product Options field contains the selected plan, fee, and duration.

## Colors

Use the existing attribute-based variation system for color swatches with independent inventory:

1. Admin → Ecommerce → Product Attributes: create/edit **Color**, select its color display layout, and add named color values with swatches.
2. Edit the product, select the Color attribute and available values.
3. Generate variations and set each color's price, stock, SKU, and images.

Warranty is applied on top of the selected variation price. No catalog colors are created automatically.

The seller form's **Product Variations (Size/Color with Price)** remains available for simple named choices. Its prices are now also loaded from the stored selection, not trusted from browser JSON. Prefer native attribute variations when per-color stock and images are needed.

## Deployment

```sh
cd /var/www/Growbiz-Latest
git pull --ff-only origin work
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan optimize:clear
```

No asset build or new scheduler entry is required. Product-option APIs expose warranty metadata; cart snapshots include coverage data.
