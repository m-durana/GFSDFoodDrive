# E2E Tests (Playwright)

Foundation laid in Phase 3 of [ROADMAP.md](../../ROADMAP.md). Most suites are
scaffolded with one or two real assertions plus `test.skip` placeholders for
the deeper flows that need stable selectors / fixture data.

## One-time setup

```bash
npm install
npx playwright install --with-deps chromium webkit
```

Then make sure your local app can run with a throwaway DB. Add to `.env`:

```
E2E_RESET_TOKEN=e2e-local-token
```

The reset endpoint at `POST /__e2e/reset` is registered in `routes/web.php` and
**only** mounts when `app()->environment() !== 'production'`. It runs
`migrate:fresh --seed`, so the dev DB at `database/database.sqlite` is wiped
between runs — use a separate `.env.e2e` if you care about the dev data.

## Running

```bash
npm run e2e            # headless, all projects
npm run e2e:ui         # interactive UI mode
npm run e2e -- --project=desktop-chrome
npm run e2e:report     # open last HTML report
```

Playwright auto-starts `php artisan serve` on 127.0.0.1:8000. Set
`E2E_NO_SERVER=1` to skip that and point at a server you started yourself.

## Suites

| File | Critical path |
|------|----------------|
| `suite-a-family-intake.spec.ts` | Family create, duplicate detection, self-service, number assignment |
| `suite-b-adopt-a-tag.spec.ts` | Public adopt listing, claim flow, deadline reminders |
| `suite-c-shopping.spec.ts` | Shopping hub, assignment generation, kiosk reconciliation |
| `suite-d-packing.spec.ts` | Barcode scan, dietary conflict, substitution, QR verify |
| `suite-e-delivery.spec.ts` | Dispatch board, ORS routing (mocked), driver flow |
| `suite-f-pdf.spec.ts` | Gift-tag, family summary, delivery sheet PDFs + async SLA |
| `suite-g-auth.spec.ts` | Login, role gating, invalid creds |
| `suite-h-command-center.spec.ts` | Stats grid, operations snapshot, auto-refresh |
| `a11y.spec.ts` | axe-core smoke against login + homepage + adopt + santa dashboard |

## Expanding skipped tests

Each `test.skip(...)` is a placeholder. Pattern for converting one:

1. Add `data-testid="..."` hooks to the relevant Blade template.
2. Replace `test.skip` with `test`, write the flow.
3. If the flow needs specific seeded data beyond `TestDataSeeder`, add a
   dedicated seeder and call it from `globalSetup` (or per-suite `beforeAll`).

## CI

Skipped for now — solo dev, run `npm run e2e` locally before pushing. If
contributors or a PHP-version matrix ever become a concern, a GitHub Actions
workflow that installs PHP+Node+browsers, builds assets, and runs the suite
is the right shape.
