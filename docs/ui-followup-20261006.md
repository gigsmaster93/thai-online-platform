# UI follow-up — five reported regressions — 2026-10-06

When the shared header's outer clear expanded it by 27 px, community/catalog pages showed an empty strip under the orange shell. Forum emoji fallback inherited photo margins, the product drawer remained trapped below the menu, the variant group lacked its legacy centering, and checkout had unlabelled stacked fields with no payment preference. This candidate repairs those specific causes.

Workspace: /home/thaionline/workstreams/release-finish-20261006
Branch: work/release-finish-20261006
Reviewed live/develop/deploy marker: 726c0a87e19023512bfd2f2d38a7363c59bce9f2
Parent: e4722f945b5c9a489692a82caa0a9443ca6a589d
State: CODE CANDIDATE VERIFIED; NOT DEPLOYED.

The user's October 6 instruction explicitly requests the product gallery above the header. This supersedes the October 5 MASTER note retaining the opposite layer order. Earlier integrated forum import/viewer/statistics/date/pagination code is preserved. The neighbouring forum-security checkout and status were not edited.

## Changes

| Report | Cause and resulting behavior |
| --- | --- |
| Strip on forum/catalog/reviews/gallery/taxi | Retain the outer header clear only on the homepage, matching legacy inner-page markup. Desktop header returns from 217 to 190 px. Remove the now-obsolete forum/contact -27 px compensation. Other measured content positions remain stable. |
| Forum messages/buttons | Exclude WordPress emoji/wp-smiley from photo sizing/margins and keep them at 1em inline. Previously emoji margins were 18.44 px on desktop; mobile icons expanded to 336.59 px. Restore 180 px topic search and field/button order. Add 8 px horizontal action spacing with wrapping, retaining the old mobile first-post position. |
| Gallery obscured by header | Release the product wrapper's z-index:0 stacking context so the existing drawer/shade/close control paint above the sticky menu. Existing product shade/Escape behavior is preserved; the gallery control closes it. |
| Variant shifted left | Restore the legacy 170 px centered group. Black Pearl desktop group x changes from 961 to 1062.5, matching current old exactly. Prices and variant metadata are untouched. |
| Checkout layout/payment | Checkout-only form has visible labels, two desktop columns and one mobile column. Add a native payment preference select whose allowlisted label enters the protected booking request. Default inline product forms remain byte-identical. |

Code files:
- wp-theme/header.php
- wp-theme/functions.php
- wp-theme/style.css
- wp-plugin/assets/community.css
- wp-plugin/src/Community.php
- wp-plugin/templates/shop-checkout.php

Theme and community CSS cache versions: 4.4.60. Shell JS, community JS, product renderer, price metadata, gateways and reCAPTCHA are unchanged.

## Payment source and behavior

Current public legacy Black Pearl FAQ supplies MIR/SBP in rubles, Thai bank transfer and payment at the office. Source snapshot/provenance and exact public paragraphs are recorded in:
 /home/thaionline/tmp-parity/ui-followup-20261006/payment-source.json

These are selectable booking preferences confirmed by staff. The original legacy checkout method labels could not be recovered from its empty-cart response; exact legacy cart-widget label parity is not claimed. No payment gateway, currency conversion or financial transaction is activated.

The new payment field is optional. Unknown values/arrays fail before saving/sending. Old callers omitting payment retain their existing request body. Date/quantity/phone validation, policy consent, nonce, honeypot, rate limit, private inbox and notification path are retained.

## Validation

Evidence: /home/thaionline/tmp-parity/ui-followup-20261006/
- 42 route/view cases: 19 each at 1440/390 and forum/topic/product484/checkout at 1920.
- Final browser checks: 413/413 PASS; JS errors 0, CSS failures 0, overflow 0.
- Geometry checks: 82/82 PASS. Forum first-post box matches old on both main widths. The mobile full Russian date retains its existing extra wrapped line versus the legacy relative-date label.
- Forum image viewer: preview/open/cross/Escape/backdrop PASS at 1440/390.
- Product galleries: 484,511,510,506,61,470; overlay, images and close/control contract PASS.
- Variant/quantity/sum and checkout selection transfer PASS; Black Pearl two adults without activities retains 3800 ฿.
- Native checkout payment selection works with pointer/keyboard and remains in FormData.
- Real WordPress checkout rendering: five product/empty/missing/escaping cases; 60 SELECT / 0 writes under a pre-bootstrap SQL guard. Account/registration/deploy state unchanged.
- Product inline booking form: exact current-live/candidate output equality.
- Booking handler: 12/12 isolated tests; valid preferences, omitted/empty input, bad/array/HTML input, nonce, honeypot, policy and rate limit. Actual DB operations/mail/orders 0.
- PHP lint and git diff --check PASS.
- Final verification summary: verification-summary.json; browser/geometry/handler details: candidate-checks.json, geometry-checks.json, payment-tests.json.

## Integration and remaining data

Review the current complete branch diff against current live; preserve any later MASTER commits. The new UI change is separate from the previous residual data tooling and review-order proposal. Do not integrate 9252a58 wholesale.

No deploy, push, develop change, live DB write, registration enablement or IP enforcement is authorized/performed here. Live remains 726c0a8. Registration remains users_can_register=0, default_role=subscriber, WP users=1. reCAPTCHA remains excluded.

The pending 158/510 description and 170/491 status proposals remain unapplied. Refresh their <=24h source and code/data/account fingerprints after expiry or any future deployment; never override guards. IP-ban remains BLOCKED_SOURCE_REQUIRED.
