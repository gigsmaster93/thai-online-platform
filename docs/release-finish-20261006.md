# Release-finish handoff — 2026-10-06 Vladivostok

Current final preparation: [release-closure-20261007.md](release-closure-20261007.md). The fresh unified four-product manifest and guarded importer supersede the historical product proposals below.

Current UI result: [ui-followup-20261006.md](ui-followup-20261006.md).
Pending data/ordering package: [remaining-audit-20261006.md](remaining-audit-20261006.md).
Worktree: /home/thaionline/workstreams/release-finish-20261006.
Branch: work/release-finish-20261006. Reviewed live/base: 726c0a87e19023512bfd2f2d38a7363c59bce9f2.

The earlier 9252a58 visual candidate is superseded. Its visual changes were withdrawn at e4722f9. The October 6 user report now authorizes a new measured UI follow-up, including gallery above the header. The prior intentional 12 px action-row spacing remains untouched. Do not cherry-pick 9252a58 as a complete release.

The remaining package contains scoped guestbook ordering, read-only data review tooling and the previously prepared product 510 content guard. The current branch also contains the separately verified October 6 header/forum/gallery/variant/checkout changes described in the UI handoff. Do not repeat the COMMITTED forum import, existing forum viewer/geometry, pagination integration or pricelist printing.

Product 510 proposal remains unapplied:
- /home/thaionline/tmp-parity/release-finish-20261006/product510-manifest.json
- SHA256 cc5d9f39c71865ffd165e25a20f06bfa9628f4782307e5d0ade7afa625123484
- tools/product-content-guard.php defaults to dry-run; apply requires explicit authorization, fresh verified DB backup, exact confirmation and unchanged code/data/account guards.
- Only post_content is proposed: 49740 -> 52537 bytes. Metadata/prices/accounts/registration remain unchanged.
- Source/manifest expire after 24h. Refresh after drift; never override hashes. Guarded receipt-based content-only rollback exists; neither apply nor rollback was run live.

Additional product 158/170/491 review:
- /home/thaionline/tmp-parity/remaining-audit-20261006/product-residual-manifest.json
- SHA256 b207b0b4d92e9973ad0e3a40ba725702a9283de7502dcff3deb576513d755b39
- tools/product-residual-dry-run.php is strictly read-only and rejects apply/rollback. This is a guarded proposal, not an executable production importer.
- MASTER must review a fresh source, DB backup, transactional apply/receipt/rollback implementation and WordPress status/term-count/cache effects before any application.

No deploy, develop update, production DB write, registration enablement or IP enforcement was performed by this chat. IP bans remain BLOCKED_SOURCE_REQUIRED; registration stays OFF; reCAPTCHA is excluded by the user.
