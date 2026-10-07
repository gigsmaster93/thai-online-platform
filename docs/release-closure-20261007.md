# Release closure preparation — 2026-10-07

Publication update: the user authorized code integration/deploy later on October 7. Actual deployed code, trusted HTTPS acceptance and the current post-deploy dry-run manifest are recorded in [release-deploy-20261007.md](release-deploy-20261007.md). The preparation-only publication state and 37e2d1e8 manifest below are historical. Product-data application remains unauthorized.


The remaining four-product data package now has fresh authoritative public sources, an executable guarded importer and receipt-based rollback. The previous UI and guestbook candidates are retained. Production application and publication remain prohibited by the user's standing instructions.

Worktree: /home/thaionline/workstreams/release-finish-20261006
Branch: work/release-finish-20261006
Parent code candidate: ac56de94c3e77ee54a7c944c1ed468924fb26882
Reviewed live/develop/deploy marker: 726c0a87e19023512bfd2f2d38a7363c59bce9f2
Neighbour forum-security: b17380af23968c5edad88e3a6fb248634069efeb, unchanged.

## Authoritative data package

Current manifest: /home/thaionline/tmp-parity/release-close-20261007/reviewed/product-data-manifest.json
SHA256: 37e2d1e8f9e92a797fe678702bb8c2aa8c56562f218d3e44dc8e0468db2167c2
Source snapshots/catalog: /home/thaionline/tmp-parity/release-close-20261007/final/
Sources refreshed 2026-10-07 at 05:08–05:09 UTC using verified TLS and GET only.

| Legacy / WP ID | Proposed change | Other fields |
| --- | --- | --- |
| 158 / 670 | Replace only inner #dscr from current old page | Byte-identical surrounding content |
| 170 / 674 | publish -> draft, old redirect to -cancel, unavailable banner and catalog absence | Preserve record and term relationship |
| 491 / 996 | publish -> draft, same independent evidence | Preserve record; no category assigned |
| 510 / 965 | Restore sanitized current legacy main-product wrapper | post_content only |

Old catalog: 10/10 pages, 224 active products; published WP excursions remain 226. Projection would give 224. Current four-product base prices and variant strings match old exactly; no price/metadata changes proposed. Term taxonomy 58 count projects 5 -> 4; counts 55 and 64 stay 20 and 11.

This single batch supersedes both October 5 product manifests and the provisional root/final manifests in this day's evidence. Do not apply an old 510 repair and this batch together. Historical manifests/receipts are retained unchanged.

## Tools and read-only entry

- tools/capture-product-close-sources.py: four fixed source URLs and ten catalog pages; verified TLS, redirects checked, no overwrite of source bundles.
- tools/prepare-product-data-manifest.php: builds immutable before/after/snapshot artifacts and reviewed code/data fingerprints.
- tools/product-data-readonly.php: SQL guard installed before WordPress, cron/mail/HTTP disabled; accepts build/review only and rejects apply/rollback before WP bootstrap.
- tools/product-data-guard.php: default dry-run; explicitly gated apply, rollback and verify-receipt.
- tests/product-data-guard-test.php: hermetic in-memory tests and stubbed native cache primitives; never bootstraps WordPress or connects to a DB.

Current safe review command:
```sh
php /home/thaionline/workstreams/release-finish-20261006/tools/product-data-readonly.php review /home/thaionline/tmp-parity/release-close-20261007/reviewed
```

Refresh by capturing into a NEW private directory, then running read-only build on that directory. The builder requires the pre-bootstrap SQL guard. Do not edit code hashes, timestamps or row fingerprints manually.

## Execution guards and lifecycle

The importer validates exact four identities/fields, source/catalog hashes and <=24h freshness, captured catalog pagination/index, canonical sanitized content, full rows/meta/comments/relationships, related taxonomy rows/member statuses, accounts/registration, 16 live/core files, its own code hash and database table coverage. Product taxonomies must retain the verified native publish-only count policy; cache invalidation must be active; no reviewed product may have a scheduled future-publication job.

Application requires separate THAI_PRODUCT_DATA_MASTER_REVIEW equal to the exact manifest SHA and THAI_PRODUCT_DATA_CONFIRM=APPLY_PRODUCT_DATA:<SHA>. A private full SQL backup must be independently reviewed and supplied via THAI_PRODUCT_DATA_BACKUP and THAI_PRODUCT_DATA_BACKUP_SHA256; the tool checks hash, age, time after manifest, completion marker and all current DB table declarations. No production backup/application was performed here.

All touched/guarded tables must be InnoDB. An exclusive advisory lock and serializable transaction protect product rows, metadata/comments, category membership/peers, taxonomy counters and accounts/options. Preconditions are repeated after row locks. Only the four reviewed fields and required taxonomy count change are written; a complete projected fingerprint is verified before COMMIT.

This is a field-scoped migration import: save/status hooks, notifications, revisions and date edits are intentionally not invoked. Native publish-only term-count semantics are reproduced under the transaction. Native post/term caches and publication-count/date/modified caches are cleared after commit; term hierarchy options are preserved. Cache hooks are prohibited from writing DB or sending mail/HTTP.

An exclusive private fsynced PREPARED journal precedes writes. COMMITTED and cache state are recorded after commit. A post-commit finalization failure is reported as DATABASE COMMITTED; verify-receipt distinguishes APPLIED, ORIGINAL and DRIFT. Never blindly repeat an uncertain operation. Rollback requires the exact receipt SHA confirmation ROLLBACK_PRODUCT_DATA:<receipt SHA>, separate MASTER review and exact applied fingerprints, and restores only the reviewed fields/counters. Later edits are refused. A PREPARED journal can recover a committed operation only when the full applied fingerprint matches. Rollback remains available after source age expiry while hashes/code/data guards still match. Cache failure is recorded PENDING.

## Validation

- Reviewed actual WordPress dry-run: 52 SELECT / 2 SHOW / 0 writes.
- Unauthorized real apply refused before any transaction/write/receipt: 31 SELECT / 1 SHOW / 0 writes.
- Read-only entry apply/rollback refusals verified before WP bootstrap.
- Guard suite: 63/63 PASS, including malformed/stale sources, code/schema/account/row/meta/comment/term drift, concurrent edit/lock, partial-write/commit failure, exact apply/rollback, PREPARED recovery and native cache-count/date handling.
- Two actual WordPress product renderings: 33 SELECT / 0 writes, in-memory cloned content only.
- Combined UI + proposed 158/510 data at 1440/390: 4 cases, 78/78 PASS; JS/CSS/overflow failures 0. Descriptions, variants/quantity/checkout URL, payment FAQ, gallery above header and control-close contract pass. All 33 images per gallery load; 510 lazy images are checked by real drawer scrolling.
- Fresh price/variant comparisons: 4/4 PASS.
- Existing unchanged UI suite remains 413/413 and geometry 82/82; previous guestbook ordering test remains 6/6. These were not rerun wholesale for a tooling-only change.
- PHP lint, Python syntax and git diff --check PASS.

Evidence: /home/thaionline/tmp-parity/release-close-20261007/
Review bundle: reviewed/product-data-manifest.json, build-dry-run.json, apply-refusal.json, scope-checks.json.
UI/price evidence: final/combined-product-qa-scrolled.json, render-product158.json, render-product510.json, fresh-price-check.json.
Offline test artifacts are fixtures, not production backups or receipts.

## Completion boundary / future MASTER sequence

Preparation is complete; no deploy, push, develop update, production data apply/rollback, registration enablement or IP enforcement occurred. The UI/guestbook and data candidates remain unapplied.

If publication/application is later explicitly authorized: review the complete current branch diff, integrate UI/guestbook code preserving any later MASTER commits, deploy only from MASTER, then regenerate a fresh data bundle against that actual deployed SHA/code/account/data state and rerun read-only review. Only after separate exact-delta approval and a fresh full backup apply that newly reviewed batch, verify active count 224, terms, product routes/prices, accounts and registration. Preserve the backup-root traverse ACL; never chmod the backup root. Rollback after later code drift requires another review; do not bypass guards.

The already COMMITTED forum import/routes/viewer/statistics/date and pricelist printing are preserved and must not be repeated. IP-ban remains BLOCKED_SOURCE_REQUIRED; fresh offline dry-run is not ready for application and does not interpret its empty set as zero bans. Required external input remains a current authenticated uCoz security export with exact entries/ranges, scope, expiry/reasons/time/hash and verified administrator recovery/allowlist. Existing guarded plans remain at /home/thaionline/tmp-parity/forum-security-20260929/. Registration remains OFF; reCAPTCHA stays excluded. No registration/enforcement implementation was activated or inferred.
