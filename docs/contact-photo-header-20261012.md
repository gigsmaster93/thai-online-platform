# Contact CAPTCHA, album viewer and home header — 2026-10-12

Worktree: /home/thaionline/workstreams/release-finish-20261006.
Branch: work/release-finish-20261006.
Baseline develop/live: d9068bff60116a37c54742c40cc4cfe0bc787958.
Scope requested by user October 12; standing October 7 code integration/deploy authorization applies. Production data apply, registration and IP enforcement remain outside this release.

Status: pre-deploy implementation and acceptance; deployment/live acceptance will be recorded below after publication.

## Contact form

The existing reCAPTCHA v2 pair now protects /contact/ as well as product price-match. Widget appears under message and above send; normal/compact sizing, loading/error/expiry status, disabled-until-verified submit, duplicate-submit lock and back-forward-cache reset.
The shared server guard verifies contact tokens before rate-state writes, message insertion and mail. Existing nonce/honeypot/rate/name/message/email guards remain. Contact email is validated before verification/write.
Provider HTTPS/TLS/hostname/error validation is unchanged. Secret remains in private wp-config and is never emitted, copied to Git or logged. reCAPTCHA asset version 1.1.0.

## Photo albums

The previous shared native viewer had only a text Close button. A photo-only capture handler now supplies separated round cross, original-size/fit toggle, canonical photo-page link, previous/next buttons and arrow keys, booking CTA, native modal focus/Escape and cleanup/opener-focus restoration.
The image and controls fit mobile/desktop viewport. Album mapping uses existing imported taxonomy _thai_shop_id/_thai_shop_label and resolves a published native product; no guessed link or data update. Missing tour mapping falls back to the canonical photo page.
The same viewer works from a photo-detail image. Product gallery and forum viewer retain their separate established contracts.
Cached Community/function entry points have safe method/function fallbacks during ordered deployment.

## Home header

The original public home has nine social destinations; the new header retained four. All nine original URLs/accessible labels are restored.
SVG sprite glyphs come from the site's existing Flaticon and FontAwesome fonts; Dzen uses the original Y motif. SVG symbols and fixed 22px icon sizing remove dependency on icon-font loading for these social links. FontAwesome glyph copyright: Dave Gandy 2016, font license SIL OFL (https://fontawesome.com/v4/license/). Original Flaticon public asset is retained unchanged.
Source font SHA256:
- Flaticon.ttf bcf1387962c16b5adbd783e65aac14d0fce8c7265f6f3c0001fe28450e9d50ae
- fontawesome-webfont.ttf aa58f33f239a0fb02f5c7a6c45c043d7a9ac9a093335806694ecd6d4edc0d6a8
The mobile social row is moved below the menu, with independent touch areas and no overlap. Existing contact-action/menu positions are preserved. Theme asset version 4.4.61 refreshes CSS/JS caches.
SVG generation used evidence-local fontTools only; no global server dependency/package change.

## Validation and evidence

- CAPTCHA server/controller: 64/64 including missing/invalid/network/wrong-host contact rejection with no rate write/save/mail; one fake accepted submission; prior price-match behavior retained.
- Booking-date regression: 72/72 retained.
- Price-match browser fixtures: 66/66 retained.
- Contact browser callback fixtures: 24/24 (1440/402/320), no real token or submission.
- Candidate home/album behavior: 75/75 (1440/402/320), including SVG scale/nine links/menu separation, photo routes/CTA/next/keyboard/zoom/close/Escape/focus and controls visible at original size.
- Candidate real Google contact widget: 21/21 (1440/402/320), correct Russian checkbox and normal/compact geometry. No challenge solved or form submitted.
- Candidate individual-photo page: 24/24 at 1440/402/320, with mapped CTA, single-photo controls, fitting image and close.
- Read-only actual-WP candidate rendering: 321 SELECT / 0 writes / 0 mail. Existing category 371 maps to product 169 and uses its actual published slug.
- PHP/JS lint and git diff --check pass.

Public sources captured by verified HTTPS; web lookup plus direct public-page/browser comparison. The old lightbox public code replaces its photo-page link with a red tour-booking CTA based on the photo category. New code resolves the already imported mapping without legacy AJAX.
Browser tests block all non-GET/HEAD requests; harmless blocked OPTIONS preflight requests are logged separately and do not count as form submissions. Google checkbox readiness waits for its label DOM selector; animation-frame polling of an offscreen provider frame was an unsuitable first test.
Photo-detail click tests wait for the existing 800ms preloader to disappear. An early synthetic browser click hit the loader, so no dialog opened; no production bug/change to the loader was required.

Evidence: /home/thaionline/tmp-parity/contact-photo-header-20261012/.
No wp-config/secret/DNS/payment gateway/IP-ban/registration/data/forum reimport changes.
Earlier four-product data manifest remains UNAPPLIED/STALE; do not reuse or weaken its source/code/age guards.

Technical references:
- https://developers.google.com/recaptcha/docs/display
- https://developers.google.com/recaptcha/docs/verify
