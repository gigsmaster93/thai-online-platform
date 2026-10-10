# Booking-date regression — 2026-10-10

Scope: user-reported past dates available on the new checkout; integration/deploy remains authorized by the user's October 7 instruction. Work belongs to work/release-finish-20261006 only.

Baseline develop/live: 1846c0512ccd80c1755085c8ceb1a21e1209c980.
Status: implementation and pre-deploy validation complete; actual deployment/acceptance receipt is recorded separately after publication.

## Cause and resulting behavior

Both native date inputs in TOP_Community lacked min. submit_booking checked only YYYY-MM-DD syntax, accepting past and impossible calendar dates.

Both checkout and inline product forms now render a minimum date and required native input. The shared server controller rejects invalid Gregorian dates and any day before business today before a rate write, message save or mail. Today and future days remain accepted, including valid leap days. Existing product, consent, quantity, email, payment, nonce, honeypot and rate guards remain.

Tour-day timezone comes from existing top_site_profile.timezone, currently Asia/Bangkok, with Bangkok fallback for missing/invalid configuration. WordPress global timezone is currently +00:00 and is unchanged. The business date is therefore independent of the visitor timezone and UTC server day.

A scoped booking-dates.js asset updates min on load, field interaction, pre-validation click/Enter, submit, focus, pageshow, visibility restoration and every minute. This covers cached HTML, long-open tabs and Bangkok midnight. IANA zones use Intl calendar parts; fixed-offset/older Intl fallback uses the server-provided numeric offset. The server remains authoritative.

Runtime delta: wp-plugin/src/Community.php and new wp-plugin/assets/booking-dates.js. No layout, gateway, secret/configuration, registration or IP-enforcement changes.

## Validation

- Hermetic server/controller cases: 72/72; no real WP/DB/network/mail. Invalid/past inputs cannot write rate state, save a message or mail. Includes Bangkok midnight/year rollover, leap dates, tampered arrays and both renderers.
- Existing reCAPTCHA server cases: 52/52 unchanged.
- Browser fixtures: 23/23 with visitor zones Los Angeles and Vladivostok; cached/overnight dates, keyboard/click/submit, interval and fixed-offset fallback.
- Candidate live-page UI: 47/47 across checkout 1440/402/320 and inline product 470 at 390; native yesterday rejection/today and tomorrow acceptance, payment choices, no overflow/JS errors or form submission.
- PHP/JS syntax and git diff --check pass.

Candidate browser checks injected only the proposed date metadata/asset in a local browser page; no candidate runtime code had been copied to production during this phase. After deployment run live mode without substitution.

Public old checkout was read via verified HTTPS but has an empty anonymous cart; its booking-date field is unavailable without establishing a cart. No old cart/order POST was sent. User report establishes the expected exclusion of past dates; the new missing min and server-calendar defect were independently reproduced.

Evidence: /home/thaionline/tmp-parity/booking-dates-20261010/.
All browser non-GET/HEAD requests blocked. No orders/messages/mails submitted.

The earlier four-product data manifest e9a2b13c is UNAPPLIED and now older than its 24h source freshness limit; after this code deploy it also fails the code guard. Do not reuse or alter its hashes/age. A future authorized data apply requires a fresh exact bundle and verified SQL backup.
Registration OFF, IP-ban BLOCKED_SOURCE_REQUIRED. No forum reimport.

Technical references consulted:
- https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Elements/input/date
- https://developer.wordpress.org/reference/functions/wp_date/
