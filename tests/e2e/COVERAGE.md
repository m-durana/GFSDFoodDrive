# WORKFLOWS.md Test Coverage Matrix

Tracks one e2e (or PHPUnit feature) test per workflow ID listed in
`docs/WORKFLOWS.md`. Statuses:

- `passing` — test exists and is green against current master
- `skipped` — `test.skip()` placeholder, flow not yet asserted
- `fixme` — `test.fixme()` — feature missing/broken, documents the gap
- `fail` — test exists, fails against current master (documented bug)
- `pending` — no test yet

Filled in as phases complete. Baseline taken 2026-04-17.

---

## §2 Public / Guest

| ID | Workflow | Test file | Status |
|----|----------|-----------|--------|
| G-01 | View public homepage | suite-m-public-tokens | pending |
| G-02 | Open Adopt-a-Tag portal | suite-b-adopt-a-tag | passing |
| G-03 | View child tag detail | suite-b-adopt-a-tag | pending |
| G-04 | Claim a tag | suite-b-adopt-a-tag | skipped |
| G-05 | View "My adoption" confirmation | suite-b-adopt-a-tag | pending |
| G-06 | Mark adopted gift delivered | suite-b-adopt-a-tag | pending |
| G-07 | Self-register a family (apply) | suite-a-family-intake | pending |
| G-08 | Submit self-registration | suite-a-family-intake | skipped |
| G-09 | See self-registration success | suite-a-family-intake | pending |
| G-10 | View family status page | suite-m-public-tokens | pending |
| G-11 | Scan child QR code | suite-m-public-tokens | pending |
| G-12 | Update scan status | suite-m-public-tokens | pending |

## §3 Authentication

| ID | Workflow | Test file | Status |
|----|----------|-----------|--------|
| A-01 | View login form | suite-g-auth | passing |
| A-02 | Submit login | suite-g-auth | passing |
| A-03 | Log out | suite-g-auth | pending |
| A-04 | Dashboard redirect | suite-g-auth | passing |
| A-05 | Begin Google OAuth | suite-g-auth | pending |
| A-06 | OAuth callback | suite-g-auth | pending (fixme — external) |
| A-07 | Request access via OAuth | suite-g-auth | pending |
| A-08 | Submit access request | suite-g-auth | pending |
| A-09 | View / edit own profile | suite-g-auth | pending |
| A-10 | Update profile | suite-g-auth | pending |

## §4 Advisor workflows (family intake)

| ID | Workflow | Test file | Status |
|----|----------|-----------|--------|
| A-INTAKE-01 | Advisor dashboard | suite-a-family-intake | passing |
| A-INTAKE-02 | Start new family application | suite-a-family-intake | passing |
| A-INTAKE-03 | Submit new family application | suite-a-family-intake | pending |
| A-INTAKE-04 | View family detail | suite-a-family-intake | pending |
| A-INTAKE-05 | Edit family information | suite-a-family-intake | pending |
| A-INTAKE-06 | Save family edits | suite-a-family-intake | pending |
| A-INTAKE-07 | Add a child to application | suite-a-family-intake | pending |
| A-INTAKE-08 | Update a child | suite-a-family-intake | pending |
| A-INTAKE-09 | Delete a child | suite-a-family-intake | pending |
| A-INTAKE-10 | Mark application complete | suite-a-family-intake | pending |

## §5 Coordinator

| ID | Workflow | Test file | Status |
|----|----------|-----------|--------|
| C-01 | Coordinator dashboard | suite-p-coordinator | pending |
| C-02 | Generate gift tags | suite-f-pdf | skipped |
| C-03 | Poll PDF generation status | suite-f-pdf | pending |
| C-04 | Download generated PDF | suite-f-pdf | pending |
| C-05 | View family summary report | suite-f-pdf | skipped |
| C-06 | View delivery-day overview | suite-p-coordinator | pending |
| C-07 | Regenerate family status token | suite-p-coordinator | pending |

## §6 Santa — Administration

| ID | Workflow | Test file | Status |
|----|----------|-----------|--------|
| S-01 | Santa dashboard | suite-i-santa-admin | pending |
| S-02 | Browse all families | suite-i-santa-admin | pending |
| S-03 | Number assignment board | suite-i-santa-admin | pending |
| S-04 | Manually assign family number | suite-i-santa-admin | pending |
| S-05 | Auto-assign numbers | suite-i-santa-admin | pending |
| S-06 | List school ranges | suite-i-santa-admin | pending |
| S-07 | Create school range | suite-i-santa-admin | pending |
| S-08 | Update school range | suite-i-santa-admin | pending |
| S-09 | Delete school range | suite-i-santa-admin | pending |
| S-10 | View gifts view | suite-i-santa-admin | pending |
| S-11 | View reports | suite-i-santa-admin | pending |
| S-12 | Export families (XLSX) | suite-i-santa-admin | pending |
| S-13 | Shopping Hub | suite-c-shopping | passing |
| S-14 | Legacy shopping list redirect | suite-c-shopping | passing |
| S-15 | Legacy shopping day redirect | suite-c-shopping | passing |
| S-16 | Add grocery item to formula | suite-c-shopping | pending |
| S-17 | Edit grocery item | suite-c-shopping | pending |
| S-18 | Delete grocery item | suite-c-shopping | pending |
| S-19 | Import grocery items (CSV) | suite-c-shopping | pending |
| S-20 | Export grocery formula | suite-c-shopping | pending |
| S-21 | Create shopping assignment | suite-c-shopping | pending |
| S-22 | Delete shopping assignment | suite-c-shopping | pending |
| S-23 | View settings | suite-i-santa-admin | pending |
| S-24 | Update settings | suite-i-santa-admin | pending |
| S-25 | Send test email | suite-i-santa-admin | pending (feature test) |
| S-26 | Manage users | suite-i-santa-admin | pending |
| S-27 | Create user | suite-i-santa-admin | pending |
| S-28 | Update user | suite-i-santa-admin | pending |
| S-29 | Reset user password | suite-i-santa-admin | pending |
| S-30 | Bulk update users | suite-i-santa-admin | pending |
| S-31 | Delete user | suite-i-santa-admin | pending |
| S-32 | Randomize user avatar | suite-i-santa-admin | pending |
| S-33 | Approve access request | suite-i-santa-admin | pending |
| S-34 | Deny access request | suite-i-santa-admin | pending |
| S-35 | Command Center | suite-h-command-center | passing |
| S-36 | Command Center live data | suite-h-command-center | pending |

## §7 Santa — Delivery teams & routes

| ID | Workflow | Test file | Status |
|----|----------|-----------|--------|
| S-40 | Create delivery team | suite-k-routes-teams | pending |
| S-41 | Update delivery team | suite-k-routes-teams | pending |
| S-42 | Delete delivery team | suite-k-routes-teams | pending |
| S-43 | List delivery routes | suite-k-routes-teams | pending |
| S-44 | Create delivery route | suite-k-routes-teams | pending |
| S-45 | Delete delivery route | suite-k-routes-teams | pending |
| S-46 | Optimise routes (TSP) | suite-k-routes-teams | pending |
| S-47 | Update route's families | suite-k-routes-teams | pending |
| S-48 | Recalculate route ETA | suite-k-routes-teams | pending |

## §8 Santa — Season archive & import

| ID | Workflow | Test file | Status |
|----|----------|-----------|--------|
| S-50 | List seasons | suite-j-seasons | pending |
| S-51 | Open import form | suite-j-seasons | pending |
| S-52 | Preview import | suite-j-seasons | pending |
| S-53 | Execute import | suite-j-seasons | pending |
| S-54 | Poll import status | suite-j-seasons | pending |
| S-55 | List Access tables | suite-j-seasons | pending |
| S-56 | Preview Access table | suite-j-seasons | pending |
| S-57 | Import legacy PHP DB | suite-j-seasons | pending |
| S-58 | Import all Access data | suite-j-seasons | pending |
| S-59 | Import all legacy data | suite-j-seasons | pending |
| S-60 | Archive current season | suite-j-seasons | pending (destructive-gated) |
| S-61 | View archived season | suite-j-seasons | pending |
| S-62 | View archived season families | suite-j-seasons | pending |

## §9 Santa — Backups, analytics, duplicates, adoptions

| ID | Workflow | Test file | Status |
|----|----------|-----------|--------|
| S-70 | View backups | suite-o-backups-analytics | pending |
| S-71 | Create backup | suite-o-backups-analytics | pending |
| S-72 | Download backup | suite-o-backups-analytics | pending |
| S-73 | Roll back to backup | suite-o-backups-analytics | pending (destructive-gated) |
| S-74 | View analytics | suite-o-backups-analytics | pending |
| S-75 | Export analytics | suite-o-backups-analytics | pending |
| S-76 | Duplicate-family inbox | suite-o-backups-analytics | pending |
| S-77 | Dismiss duplicate pair | suite-o-backups-analytics | pending |
| S-78 | Merge duplicates | suite-o-backups-analytics | pending (destructive-gated) |
| S-79 | Geocode families | suite-o-backups-analytics | pending |
| S-80 | Adoption admin dashboard | suite-o-backups-analytics | pending |
| S-81 | Release adoption | suite-o-backups-analytics | pending |
| S-82 | Complete adoption | suite-o-backups-analytics | pending |

## §10 Delivery Day

| ID | Workflow | Test file | Status |
|----|----------|-----------|--------|
| S-90 | Delivery-day dashboard | suite-e-delivery | passing |
| S-91 | Update family delivery status (form) | suite-e-delivery | pending |
| S-92 | Update family delivery status (AJAX) | suite-e-delivery | pending |
| S-93 | Assign family to team | suite-e-delivery | pending |
| S-94 | Bulk-assign team | suite-e-delivery | pending |
| S-95 | Add delivery log entry | suite-e-delivery | pending |
| S-96 | View delivery logs | suite-e-delivery | pending |
| S-97 | Open map | suite-e-delivery | pending |
| S-98 | Map data (AJAX) | suite-e-delivery | pending |
| S-99 | Update own location (HQ side) | suite-e-delivery | pending |
| S-100 | Live track page | suite-e-delivery | pending |
| S-101 | Quick-assign unrouted family | suite-e-delivery | pending |
| S-102 | Add families to route | suite-e-delivery | pending |
| S-103 | Mark route returning | suite-e-delivery | pending |

## §11 Driver (token-secured)

| ID | Workflow | Test file | Status |
|----|----------|-----------|--------|
| D-01 | Driver route view | suite-m-public-tokens | pending |
| D-02 | Live route data | suite-m-public-tokens | pending |
| D-03 | Complete a stop | suite-m-public-tokens | pending |
| D-04 | Update driver location | suite-m-public-tokens | pending |
| D-05 | Mark "heading to next" | suite-m-public-tokens | pending |
| D-06 | Mark route returning | suite-m-public-tokens | pending |

## §12 Warehouse

| ID | Workflow | Test file | Status |
|----|----------|-----------|--------|
| W-01 | Warehouse home | suite-l-warehouse | pending |
| W-02 | Open receiving form | suite-l-warehouse | pending |
| W-03 | Record receipt | suite-l-warehouse | pending |
| W-04 | View inventory | suite-l-warehouse | pending |
| W-05 | View transactions | suite-l-warehouse | pending |
| W-06 | Look up barcode | suite-l-warehouse | pending |
| W-07 | Gift drop-off (tag workflow) | suite-l-warehouse | pending |
| W-08 | Confirm gift drop-off | suite-l-warehouse | pending |
| W-09 | Kiosk mode (intake) | suite-l-warehouse | pending |
| W-10 | Kiosk mode (gifts) | suite-l-warehouse | pending |
| W-11 | Gifts intake index | suite-l-warehouse | pending |
| W-12 | Child gifts detail | suite-l-warehouse | pending |
| W-13 | Item detail | suite-l-warehouse | pending |
| W-14 | Change item location | suite-l-warehouse | pending |
| W-15 | Remove item | suite-l-warehouse | pending |
| W-16 | Mobile scanner | suite-m-public-tokens | pending |

### §12a Gift Bank

| ID | Workflow | Test file | Status |
|----|----------|-----------|--------|
| GB-01 | Gift bank overview | suite-l-warehouse | pending |
| GB-02 | Add item to gift bank | suite-l-warehouse | pending |
| GB-03 | Assign gift to child | suite-l-warehouse | pending |
| GB-04 | Unassign gift | suite-l-warehouse | pending |
| GB-05 | Remove gift bank item | suite-l-warehouse | pending |
| GB-06 | Suggestions for a child | suite-l-warehouse | pending |

## §13 Packing

| ID | Workflow | Test file | Status |
|----|----------|-----------|--------|
| P-01 | Packing index | suite-d-packing | passing (if flag on) |
| P-02 | Packing dashboard | suite-d-packing | pending |
| P-03 | Packing summary | suite-d-packing | pending |
| P-04 | Generate packing lists (batch) | suite-d-packing | pending |
| P-05 | Print batch | suite-d-packing | pending |
| P-06 | Verification station | suite-d-packing | pending |
| P-07 | Show packing list | suite-d-packing | pending |
| P-08 | Print single list | suite-d-packing | pending |
| P-09 | Refresh list | suite-d-packing | pending |
| P-10 | Verify list | suite-d-packing | pending |
| P-11 | Update list notes | suite-d-packing | pending |
| P-12 | Mark item packed | suite-d-packing | pending |
| P-13 | Generate single list | suite-d-packing | pending |

## §14 Shopping companion (token)

| ID | Workflow | Test file | Status |
|----|----------|-----------|--------|
| SH-01 | Shopper assignment page | suite-m-public-tokens | pending |
| SH-02 | Per-family checklist | suite-m-public-tokens | pending |

## §15 Help / Wiki

| ID | Workflow | Test file | Status |
|----|----------|-----------|--------|
| H-01 | Help index | suite-n-help | pending |
| H-02 | Help topic | suite-n-help | pending |

## §16 System / automated (PHPUnit)

| ID | Workflow | Test file | Status |
|----|----------|-----------|--------|
| SYS-01 | E2E DB reset | tests/Feature/Workflows/SysResetTest.php | pending |
| SYS-02 | permission middleware | tests/Feature/Workflows/PermissionMiddlewareTest.php | pending |
| SYS-03 | signed middleware | tests/Feature/Workflows/SignedScanTest.php | pending |
| SYS-04 | PackingSystemEnabled middleware | tests/Feature/Workflows/PackingFlagTest.php | pending |
| SYS-05 | Throttle middleware | tests/Feature/Workflows/ClaimThrottleTest.php | pending |
| SYS-06 | Async PDF job | tests/Feature/Workflows/PdfJobTest.php | pending |
| SYS-07 | Season import job | tests/Feature/Workflows/SeasonImportJobTest.php | pending |

---

## Baseline snapshot

Raw baseline run (2026-04-17, master, `npm run e2e`):

- 56 passed / 66 skipped / 1 failed across 3 projects (desktop-chrome,
  mobile-chrome, mobile-safari)
- Unique test names: ~41
- Single failure: `a11y.spec.ts` login page timeout (cold-start flake; not
  yet diagnosed)
- Broken route refs spotted while writing this matrix (to fix opportunistically):
  - `suite-d-packing.spec.ts` uses `/packing` — real route is `/santa/packing`
  - `suite-h-command-center.spec.ts` uses `/command-center` — real route is
    `/santa/command-center`
  - Both currently "pass" only because they redirect to `/login` (< 500)
