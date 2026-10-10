# Publication follow-up — latest

reCAPTCHA v2 is now deployed and verified at 1846c0512ccd80c1755085c8ceb1a21e1209c980. The earlier eb77896 code-release record below is historical. Current handoff: [recaptcha-setup-20261007.md](recaptcha-setup-20261007.md). Current unapplied four-product manifest is e9a2b13c; every previous manifest is superseded. Registration and IP enforcement remain unchanged.

# Release deployment — 2026-10-07

The user explicitly authorized integration and code deploy after receiving the reCAPTCHA registration instructions. Code is now published; product-data application remains unauthorized. This record supersedes the earlier preparation-only publication state in release-closure-20261007.md.

## Published code

GitHub develop, local develop and deploy marker: eb77896f20b2f7e0e0f070fa807fc7614f82f1df.
Reviewed source snapshot: 5c766b0ca8feaf0c9d26d1b5dbaf4af454186957, tree 3cc5eef83a8884ce3e4e8d518730614decdc8c56.
GitHub publication used the connected API and a non-force expected-base ref update from 726c0a8. The local HTTPS push had no credentials; its temporary merge was never deployed. The own branch subsequently records the actual deployed integration without changing runtime files.

Seven live files changed: theme header/functions/style, community CSS/Community.php, guestbook template and checkout template. This publishes the header-strip/forum spacing/emoji fixes, gallery above header, centered tour variants, labelled responsive checkout/payment preferences and legacy review ordering. No payment gateway activated. Previously integrated forum import/routes/viewer/dates/statistics and pricelist printing are preserved.

Private full theme/plugin code backup: /home/thaionline/backups/release-code-before-20261007T055203Z.
All 70 tracked runtime files matched the baseline and the deployed tree. Existing backup-root traverse ACL was preserved. Deployment and cache flushing used a pre-bootstrap SQL write guard; account/product snapshots, all post counts and comments remained unchanged. Receipt: /home/thaionline/tmp-parity/release-deploy-20261007/deployment-receipt.json.

## Acceptance and HTTPS

- Actual live UI: 42 cases: 19 each at 1440/390 and four at 1920; 413/413 checks PASS, no JS/CSS/overflow errors, no orders submitted.
- Trusted HTTPS browser acceptance: 12 cases at 1440/390, 139/139 PASS, certificate errors are not ignored.
- Eight strict HTTPS route/asset checks PASS, including topic 102 page 48/49, invalid forum pages, theme CSS, legacy CSS and killed.gif.
- Current old/new header/navigation geometry: 48/48 equal. The visible legacy menu position is retained.
- Fresh data guard tests: 63/63 PASS, memory store only; no WordPress/DB connection.
- Final guarded data/account comparison: 26 SELECT / 0 writes; fingerprints unchanged. PHP lint: all 33 theme/plugin PHP files passed.

The new-site certificate was self-signed since April and the domain was excluded from AutoSSL. The account contains only new.thai-online.org. A trusted Let's Encrypt YR2 certificate was issued for that hostname, valid through 2027-01-05; AutoSSL reports active, will_renew=1 and no domain problems. Only the main hostname's inclusion setting changed; all original service/www exclusions remain. www.new.thai-online.org has no public DNS and was restored to its previous excluded setting. No DNS record was created. Initial certificate-failure logs remain as diagnostic history; the trusted HTTPS acceptance is authoritative.

## Current data review and remaining boundaries

Current post-deploy manifest: /home/thaionline/tmp-parity/release-deploy-20261007/data-review/product-data-manifest.json.
SHA256: 4a7619174d5c940c783d340d7367c15aaeb6ef78af6b202c700468202e8989c9.
Sources refreshed 2026-10-07 05:58–05:59 UTC. Real guarded build: 52 SELECT / 2 SHOW / 0 writes, apply_authorized=false.

The proposals remain: 158/WP670 inner description; 170/WP674 and 491/WP996 publish -> draft from cancelled authoritative sources; 510/WP965 main-product wrapper/content. Old catalog has 224 active products; WordPress remains 226, projection 224. Prices/meta/dates/comments/relationships/accounts/forum data preserved. This manifest replaces the earlier 37e2d1e8 reviewed bundle and all historical/provisional manifests. Never override code/data/age guards or apply old 510 and the unified batch together.

Registration remains OFF (users_can_register=0, default_role=subscriber, WP users=1). IP-ban remains BLOCKED_SOURCE_REQUIRED. No product-data apply, rollback, forum reimport, registration activation or IP enforcement occurred.

reCAPTCHA v2 Site key was received from the user on October 7 at 16:46 Asia/Vladivostok and is recorded in docs/recaptcha-setup-20261007.md. Activation awaits the matching privately configured THAI_RECAPTCHA_SECRET_KEY. No secret is stored in Git/chat/status and no reCAPTCHA runtime code is in this release.

Evidence root: /home/thaionline/tmp-parity/release-deploy-20261007/.
Neighbour work/forum-security remains clean at b17380af23968c5edad88e3a6fb248634069efeb; its files/status were not edited.
