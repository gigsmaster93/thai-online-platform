# Remaining migration audit — 2026-10-06 Vladivostok / 2026-10-05 UTC

Scope: verify unfinished migration other than reCAPTCHA, prepare only genuine residual changes, preserve already integrated streams. No deploy, develop update or production DB writes are authorized.

Workspace / branch: /home/thaionline/workstreams/release-finish-20261006 / work/release-finish-20261006.
Reviewed production/develop/deploy marker: 726c0a87e19023512bfd2f2d38a7363c59bce9f2.
Evidence directory: /home/thaionline/tmp-parity/remaining-audit-20261006/.
Neighbouring forum-security, MASTER, develop and historical mixed worktrees were not edited.

## October 7 closure preparation

Fresh sources, the unified 158/170/491/510 manifest, guarded transactional apply/receipt/rollback and cache/count handling are complete in [release-closure-20261007.md](release-closure-20261007.md). The historical read-only-only importer and October 5 manifests below are superseded for execution. No live data or publication occurred.

## Confirmed residuals

| Item | Current live | Prepared result | Production state |
|---|---|---|---|
| Guestbook order | Pages 2 and 6 differ from legacy sequence | Scoped legacy-ID descending order; native WP reviews remain first in date order | Code only, not deployed |
| Product 158 / WP 670 | Outdated hotel check-in, schedule/contact and island-trip wording | Replace only inner #dscr from current authoritative old page | Manifest, not applied |
| Product 170 / WP 674 | Published; old URL redirects to -cancel, shows unavailable modal, absent from old catalog | Propose publish -> draft under existing suspended-tour policy | Manifest, not applied |
| Product 491 / WP 996 | Published; same current-old unavailable/cancel evidence | Propose publish -> draft | Manifest, not applied |
| Product 510 / WP 965 | Legacy main/description wrapper still missing in stored fragment | Previously prepared current-old content-only repair | Fresh dry-run PASS, not applied |
| IP-ban source | No authoritative current list, scope/expiry/admin-recovery source | Existing BLOCKED_SOURCE_REQUIRED manifest preserved | No ban enforcement applied |
| Registration | users_can_register=0 | Remains OFF pending source/enforcement review | Unchanged |

The guestbook code adds one posts_orderby filter gated by an explicit query flag and thai_guestbook type. It preserves pagination/status filtering and selects legacy sequence numbers without changing records. The scalar MAX lookup avoids duplicate rows from repeated metadata. At audit commit e4722f9 no theme/CSS/forum geometry/viewer/product-renderer delta remained against reviewed live. The subsequent authorized UI follow-up is documented in [ui-followup-20261006.md](ui-followup-20261006.md).

Product 158 keeps every byte outside #dscr unchanged. Current-old description wording is restored; prices and price variants already match and are not edited. The extra-charge amount was already present; this proposal updates wording/timing, not that amount.

Product 170/491 deactivation is a proposal based on current public sources, not a deletion. The read-only tool deliberately supplies no apply/rollback operation. A future guarded importer must account for WordPress status transitions, term counts/caches, verified backup and receipt-based rollback before MASTER applies it.

## Data proposals / guards

New manifest: /home/thaionline/tmp-parity/remaining-audit-20261006/product-residual-manifest.json.
SHA256: b207b0b4d92e9973ad0e3a40ba725702a9283de7502dcff3deb576513d755b39.

Read-only command:
```sh
wp --path=/home/thaionline/public_html eval-file /home/thaionline/workstreams/release-finish-20261006/tools/product-residual-dry-run.php /home/thaionline/tmp-parity/remaining-audit-20261006/product-residual-manifest.json
```

Guards: fixed reviewed IDs/fields, exact source/URL/file hashes, <=24h source/catalog/manifest age, live marker/develop/clean checkout/code hashes, exact row/meta/account fingerprints, registration OFF/default subscriber, source description equality, no executable proposed markup, byte-identical surrounding content, inactive banner plus catalog absence, projected row/rollback fingerprint. apply_allowed=false; apply and rollback modes always refuse.

Before/after content files and exact rollback values are outside webroot. The manifest describes backup, explicit confirmation, transaction/lock/journal and native status lifecycle prerequisites for future MASTER implementation. It creates no WP users and changes no production data.

Product 510 manifest and content guard are recorded in release-finish-20261006.md. Do not apply both a recreated 510 batch and the original 510 manifest. Both product manifests must be refreshed after expiry or production/code/data drift.

## Completed / verified data

- Fresh old catalog: 10/10 pages, 224 unique products; missing published products in WP: 0. WP currently publishes 226; the two extras are the cancelled products above. Drafting only those two would reconcile active-catalog count.
- Fresh old-source comparison for all 226 published WP products: base prices 226/226 and price-variant strings 226/226 match; HTTP/source errors 0.
- Description audit: 221 exact normalized matches; five raw differences classified as 158 wording, 510 missing wrapper, 170/491 unavailable notices and the rehosted 511 image-reference path.
- Rehosted product 511 image: freshly fetched old file and current WP upload have identical SHA256 (82ab36cba012bb03a08a3f7cefa65625d7c6e4dcb54cc882ad35c6289435f467); no image reimport is needed.
- All 79 public guestbook IDs, message text hashes and displayed dates match old across six pages; the confirmed order mismatch is handled by this code proposal.
- All 26,900 published photos have full/thumb metadata; 53,800 local file paths exist and are readable. No empty/non-local/missing photo paths.
- Public-link corpus: 8,341 required photo IDs and album associations, 320 forum topic IDs/sections: missing/bad/nonpublished 0. 324 gallery aliases: missing/bad 0.
- 121 catalog-linked IDs audited; historical missing IDs 159/147/153 and intentional drafts 38/32/133/272/139 are documented exceptions, not candidates for republishing.
- FAQ: all 12 published records retained. Contacts, service pages, galleries, reviews and main menu routes load in the live smoke suite.
- Forum committed truth remains 1,372 topics / 2,587 replies / 5,039 public-directory members, authenticated WP users 1. The previously applied +9 topics/+23 messages/+4 members batch is not repeated. Unsupported old aggregate-only +2 topics/+15 replies are not fabricated.

## Verification

- Current live smoke: 30 routes x desktop/mobile (1440/390), HTTP 200, JS errors 0, horizontal overflow 0. Tested menu and product selectors pass.
- The ten raw asset/broken-image failure records collapse to three known historical missing paths, freshly confirmed 404 on old and new. The full 33-image missing gallery for product 174 also returns old404/new404: 66 HEAD checks. No fabricated replacements.
- Candidate guestbook query: 6/6 page ID orders exactly match current old; query-scope controls and native-review-first fixture pass. SQL writes 0.
- New guard suite: 17/17 cases pass; valid proposal, age/hash/identity/field/account/row/meta/source/executable-markup/outside-description/rollback-projection refusals. 77 read queries, 0 write queries. Unsupported apply mode exits 1 before any mutation.
- Candidate 158 response preview, using current live CSS and proposed description only: 1440/390 PASS. Description hash equals current old, tabs and gallery open/control-close work, all 33 gallery images load, calculator works, JS errors/overflow 0. Preview is not deployment.
- Product 510 fresh actual dry-run: mutations 0, accounts unchanged, registration 0. Earlier content apply/rollback guard tests ran in memory only.
- PHP lint and git diff --check pass. No booking, comment, review or contact form was submitted.

## Integration boundary

The earlier 9252a58 visual delta was withdrawn at e4722f9 and must not be integrated wholesale. The October 6 user report authorizes the subsequent targeted UI candidate, including gallery above the header; review [ui-followup-20261006.md](ui-followup-20261006.md). Review only the current complete diff against 726c0a8, preserving any newer MASTER work.

Code deployment and DB application remain prohibited by the user's standing instructions. IP bans cannot be completed without an authoritative current uCoz source; registration remains OFF. reCAPTCHA was excluded and untouched.
