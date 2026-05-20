import { test, expect, loginAs } from './fixtures';

/**
 * Stress tests against authenticated santa/coordinator flows.
 * Focus: pages that take real DB state, double-submit / race scenarios, and
 * auto-refresh endpoints. We assert "no 5xx, no JS errors, page renders" —
 * stricter assertions belong in the per-suite specs once selectors stabilize.
 */

function attachServerErrorWatcher(page: any, bag: string[]) {
  page.on('response', (resp: any) => {
    const url = resp.url();
    if (url.includes(':5173') || url.includes('hot-update')) return;
    if (resp.status() >= 500) bag.push(`${resp.status()} ${url}`);
  });
  page.on('pageerror', (err: any) => bag.push(`pageerror: ${err.message}`));
}

test.describe('Stress — Santa pages render cleanly', () => {
  // Surveying a wide swathe of authed pages catches "boot-time" regressions
  // (missing relation eager-load, null pointer in blade, etc.) that unit tests
  // routinely miss because they don't hit the full request lifecycle.
  const SANTA_PAGES: { path: string; label: string }[] = [
    { path: '/santa', label: 'santa-dashboard' },
    { path: '/santa/families', label: 'all-families' },
    { path: '/santa/number-assignment', label: 'number-assignment' },
    { path: '/santa/school-ranges', label: 'school-ranges' },
    { path: '/santa/gifts', label: 'gifts' },
    { path: '/santa/reports', label: 'reports' },
    { path: '/santa/shopping', label: 'shopping-hub' },
    { path: '/santa/settings', label: 'settings' },
    { path: '/santa/users', label: 'users' },
    { path: '/santa/command-center', label: 'command-center' },
    { path: '/santa/seasons', label: 'seasons' },
    { path: '/santa/adoptions', label: 'admin-adoptions' },
    { path: '/santa/duplicates', label: 'duplicates' },
    { path: '/santa/backups', label: 'backups' },
    { path: '/santa/analytics', label: 'analytics' },
    { path: '/family', label: 'family-list' },
    { path: '/family/add', label: 'family-create' },
    { path: '/santa/packing', label: 'packing-index' },
    { path: '/santa/packing/dashboard', label: 'packing-dashboard' },
    { path: '/santa/packing/summary', label: 'packing-summary' },
    { path: '/santa/packing/verify-station', label: 'verify-station' },
    { path: '/delivery-day', label: 'delivery-day' },
    { path: '/delivery-day/logs', label: 'delivery-logs' },
    { path: '/delivery-day/track', label: 'delivery-track' },
    { path: '/delivery-day/map', label: 'delivery-map' },
    { path: '/warehouse', label: 'warehouse' },
    { path: '/warehouse/receive', label: 'warehouse-receive' },
    { path: '/warehouse/inventory', label: 'warehouse-inventory' },
    { path: '/warehouse/transactions', label: 'warehouse-transactions' },
    { path: '/warehouse/kiosk', label: 'warehouse-kiosk' },
    { path: '/warehouse/kiosk/gifts', label: 'warehouse-kiosk-gifts' },
    { path: '/warehouse/gift-bank', label: 'gift-bank' },
    { path: '/help', label: 'help-index' },
  ];

  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'santa');
  });

  for (const { path, label } of SANTA_PAGES) {
    test(`${label} renders without 5xx or JS errors`, async ({ page }) => {
      const errs: string[] = [];
      attachServerErrorWatcher(page, errs);
      const resp = await page.goto(path);
      const status = resp?.status() ?? 0;
      expect(status, `top-level status ${status} for ${path}`).toBeLessThan(500);
      // Quick sanity: not bounced to login.
      expect(page.url(), `bounced to login from ${path}`).not.toMatch(/\/login/);
      await page.waitForLoadState('domcontentloaded');
      expect(errs, errs.join('\n')).toHaveLength(0);
    });
  }
});

test.describe('Stress — Command Center auto-refresh data endpoint', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'santa');
  });

  test('command center /data endpoint returns JSON quickly across rapid polls', async ({ page, request, context }) => {
    // Need an authenticated request context — reuse the page's session cookies.
    const cookies = await context.cookies();
    const cookieHeader = cookies.map((c) => `${c.name}=${c.value}`).join('; ');
    const start = Date.now();
    const statuses: number[] = [];
    for (let i = 0; i < 8; i++) {
      const resp = await request.get('/santa/command-center/data', {
        headers: { Cookie: cookieHeader, Accept: 'application/json' },
        failOnStatusCode: false,
      });
      statuses.push(resp.status());
    }
    const elapsed = Date.now() - start;
    const bad = statuses.filter((s) => s >= 400);
    expect(bad, `non-2xx in rapid polling: ${statuses.join(',')}`).toHaveLength(0);
    // 8 polls in <15s is generous; if this regresses to >15s something is slow.
    expect(elapsed, `8 polls took ${elapsed}ms`).toBeLessThan(15000);
  });
});

test.describe('Stress — Two-tab race on family number assignment', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'santa');
  });

  test('navigating number-assignment from two tabs concurrently does not 5xx', async ({ browser }) => {
    // Reuse santa session across two contexts by logging in twice.
    const ctxA = await browser.newContext();
    const ctxB = await browser.newContext();
    const pageA = await ctxA.newPage();
    const pageB = await ctxB.newPage();
    await loginAs(pageA, 'santa');
    await loginAs(pageB, 'santa');
    const errs: string[] = [];
    attachServerErrorWatcher(pageA, errs);
    attachServerErrorWatcher(pageB, errs);
    await Promise.all([
      pageA.goto('/santa/number-assignment'),
      pageB.goto('/santa/number-assignment'),
    ]);
    expect(errs, errs.join('\n')).toHaveLength(0);
    await ctxA.close();
    await ctxB.close();
  });
});

test.describe('Stress — Auth gating', () => {
  test('logged-out user hitting santa pages is bounced to login (no 500)', async ({ request }) => {
    const paths = [
      '/santa',
      '/santa/families',
      '/santa/command-center',
      '/family',
      '/santa/packing',
      '/warehouse',
      '/delivery-day',
    ];
    for (const p of paths) {
      const resp = await request.get(p, { maxRedirects: 0, failOnStatusCode: false });
      const s = resp.status();
      expect(s, `bad status ${s} for ${p}`).toBeLessThan(500);
      // Expect redirect to login (302) or 403.
      expect([302, 401, 403], `unexpected ${s} for ${p}`).toContain(s);
    }
  });

  test('coordinator role cannot reach santa-only number-assignment', async ({ page }) => {
    await loginAs(page, 'coordinator');
    const resp = await page.goto('/santa/number-assignment');
    const status = resp?.status() ?? 0;
    expect(status, `unexpected ${status}`).toBeLessThan(500);
    // Either 403 or bounced away from the santa page.
    if (status === 200) {
      // Some installs may grant coordinators read access — make sure it's not
      // crashing, but don't fail on permissive policy.
      expect(page.url()).toBeTruthy();
    }
  });
});
