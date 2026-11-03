# Daily pricing, Monthly Limited-Hours Parking, and Requests

Hosts offering daily parking during limited hours (such as 6 PM–6 AM) can enter an optional monthly price called **“Monthly Limited-Hours Parking (CA$)”** for the same available days and hours.

## Host Listing Pricing Field

- Field name: `Monthly Limited-Hours Parking (CA$)`
- Toggle: Enable / Disable
- Minimum monthly price: CA$ 45.00
- Suggested price: intelligently estimated based on the daily price (e.g. CA$ 100.00 for $15/day, CA$ 80.00 for $12/day)
- Stored as `price_monthly_limited` on the `parking_spots` model.

## Progressive Discount Rate Calculation

When a driver books recurring daily parking for fewer days than a full month:
- Days 1 to 4 (or up to $k$ days depending on the selected monthly anchor) charge the host's daily rate.
- As more days are booked, the system intelligently applies a progressive discount.
- The total increases smoothly every single day, reaching the host's monthly limited-hours price at the end of the booking month (28 days).
- Beyond 28 days, each additional day adds `monthly price / 28`.
- If no monthly price is set or monthly price exceeds 28 daily rates, the daily rate applies throughout.
- The calculation respects the host's specified available days and hours. Sessions longer than 12 hours retain proportional extra-hour billing.

## Customer Price Fees

Customer pricing adds Solo Park platform fees sequentially:
1. **10% Solo Park Service Fee** on the subtotal.
2. **13% Applicable HST** on (subtotal + service fee).
3. **3% Stripe Processing Cost** on (subtotal + service fee + HST).
4. Final daily/recurring payment total is rounded up to the nearest whole dollar:
   `ceil(round(subtotal + service_fee + tax + gateway_fee, 8))`.

## Verification

```bash
npm.cmd run build
node --test tests/Unit/dailyPricing.test.mjs
```
