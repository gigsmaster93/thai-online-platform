# reCAPTCHA installation — 2026-10-07

Status: REVIEWED_CODE_READY_FOR_DEPLOY. Current live baseline: eb77896f20b2f7e0e0f070fa807fc7614f82f1df.

The user supplied the public Site key at 16:46 Asia/Vladivostok and confirmed private Secret key setup / continuation at 17:45. Code integration/deployment is authorized. Registration activation, IP enforcement and the four-product data apply remain outside this code release.

Public Site key: `6LesJOMtAAAAACLMzyJ18Z4MMAeqVGxCY71YzxNW`.
Type: reCAPTCHA v2 checkbox, verified by a real Google widget on new.thai-online.org. The matching Secret key is configured privately as THAI_RECAPTCHA_SECRET_KEY in wp-config.php; no secret is in Git, chat, status or artifacts.

## Configuration repair and verification

An incomplete inserted line 109 caused a PHP parse error. The isolated syntax repair commented that line only, preserving all other bytes, owner and mode 0600. A private mode-0600 backup was saved under a new mode-0700 tmp-parity directory; backup-root ACL was not changed. The user subsequently installed the complete constant successfully.
Configuration lint passes, one non-empty real secret declaration exists, and the homepage returns 200 over verified HTTPS.
A Google siteverify request using a deliberately invalid response returned only invalid-input-response, without invalid/missing-secret errors. This checks connectivity/configuration; it does not prove a completed human challenge or pair matching.

## Reviewed scope

Five runtime paths only:
- wp-plugin/src/Recaptcha.php
- wp-plugin/assets/recaptcha.css
- wp-plugin/assets/recaptcha.js
- wp-plugin/src/Community.php
- wp-plugin/thai-online-platform.php

Only the existing product “Нашли дешевле?” form is protected. Booking, contact, reviews, forum and login flows are preserved; registration stays disabled.

The widget loads lazily on opening the form. The form keeps its nonce, product reference, four fields and honeypot. Server verification runs after nonce/honeypot/read-only rate validation and before transient writes, insertion or mail. Missing/malformed/expired/reused tokens, wrong hostname, invalid JSON, provider failure or missing configuration fail closed. Verification uses fixed HTTPS POST, trusted configured home hostname, TLS validation, no redirects, a 10-second timeout and a bounded response. No visitor IP is sent to Google.

Google appends its challenge iframe to body, outside the form. This form therefore uses its existing native dialog in nonmodal mode with a dedicated backdrop, fixed centering, focus management and Escape/close/backdrop handling; it is moved to body to avoid containing-block/stacking effects. The provider challenge remains above the form and can handle Escape independently. Other native dialogs/gallery behavior are untouched. Closing resets verification and restores trigger focus. Normal/compact widget sizing fits 1440/390/320.

## Validation

- Server/controller hermetic tests: 52/52 PASS; no real WP bootstrap/DB/network/mail.
- Isolated browser callbacks/expiry/error/close/focus tests: 66/66 PASS at 1440/390/320; no real CAPTCHA or live form submission.
- Real Google candidate widget: 54/54 PASS at 1440/390/320; correct checkbox, lazy loading, popup layering, close controls, focus restoration and no overflow.
- PHP/JS syntax and diff whitespace checks pass.

Reproducible tests:
`php tests/recaptcha-test.php`
`node tests/recaptcha-browser-test.cjs fixture /private/evidence/directory`
The browser test also accepts candidate and live modes. No mode solves a CAPTCHA or submits a live form.

## Publication guard

Review exact tree and base before non-force GitHub publication; hold the existing autodeploy lock across ref update and scoped atomic copy. Back up/verify all baseline runtime files without altering the backup-root ACL. Install new files first, plugin require next and Community.php last. Cache/fingerprint operations must use the existing pre-bootstrap read-only SQL guard.

After deployment, verify actual HTTPS widget and regression, account/product fingerprints, and regenerate the guarded UNAPPLIED product manifest against the new live commit. Never patch old code hashes or freshness timestamps. No full SQL backup or data apply is included.

Evidence: /home/thaionline/tmp-parity/recaptcha-install-20261007/.
References: https://developers.google.com/recaptcha/docs/display and https://developers.google.com/recaptcha/docs/verify.
