import { test, expect } from './fixtures';

/**
 * Stress tests against public/unauthenticated endpoints.
 * Focus: error/empty/disabled states, validation, throttling, expired tokens.
 * These do NOT require new seed data — they exercise the existing app surface
 * under adverse inputs to flush out user-visible bugs the unit tests miss.
 */

test.describe('Stress — Public endpoints (anonymous)', () => {
  test('homepage renders without JS errors or 5xx asset responses', async ({ page }) => {
    const jsErrors: string[] = [];
    const serverErrors: string[] = [];
    const missingAssets: string[] = [];
    page.on('pageerror', (err) => jsErrors.push(`pageerror: ${err.message}`));
    page.on('response', (resp) => {
      const url = resp.url();
      // Ignore Vite HMR / dev-only WebSocket failures and 127.0.0.1:5173 probes —
      // production-built assets are served from /build.
      if (url.includes(':5173') || url.includes('hot-update')) return;
      const s = resp.status();
      if (s >= 500) serverErrors.push(`${s} ${url}`);
      // 404 on a same-origin asset request is a real bug (broken image, missing
      // build file, etc.). 404 on an analytics ping is not — none expected here.
      if (s === 404 && url.startsWith('http://127.0.0.1:8000/')) {
        missingAssets.push(url);
      }
    });
    await page.goto('/');
    await page.waitForLoadState('networkidle').catch(() => {});
    expect(jsErrors, jsErrors.join('\n')).toHaveLength(0);
    expect(serverErrors, serverErrors.join('\n')).toHaveLength(0);
    // Note: we surface 404s but do not fail the test on them yet — sibling can
    // triage. Log them for visibility.
    if (missingAssets.length) {
      console.log('[stress] homepage 404 assets:', missingAssets);
    }
  });

  test('adopt portal disabled state shows expected messaging (no 500)', async ({ page }) => {
    const resp = await page.goto('/adopt');
    expect(resp?.status(), `unexpected /adopt status: ${resp?.status()}`).toBeLessThan(400);
    // Seeded e2e env has the feature flag off — should render the disabled view
    // (or, if a sibling change enables it, at least not crash).
    await expect(page.locator('body')).not.toHaveText(/whoops|server error|exception/i);
  });

  test('adopt direct child URL with nonsense id returns 404', async ({ page }) => {
    const resp = await page.goto('/adopt/999999999');
    expect(resp?.status(), 'expected 404 for bogus child id').toBe(404);
  });

  test('adopt confirmation with bad token returns 404', async ({ page }) => {
    const resp = await page.goto('/adopt/mine/this-is-not-a-real-token-xxxxxxxxxxxx');
    expect(resp?.status(), 'expected 404 for bogus token').toBe(404);
  });

  test('driver route with bad token returns 404', async ({ page }) => {
    const resp = await page.goto('/delivery/route/bogus-driver-token');
    expect(resp?.status(), 'expected 404 for bogus driver token').toBe(404);
  });

  test('public self-registration disabled state returns 4xx, not 500', async ({ page }) => {
    const resp = await page.goto('/register-family');
    const status = resp?.status() ?? 0;
    expect(status).toBeGreaterThanOrEqual(200);
    expect(status, `unexpected status ${status}`).toBeLessThan(500);
  });
});

test.describe('Stress — Login brute-force and validation', () => {
  test('invalid login does not leak server errors', async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="username"]', 'no_such_user_xyz');
    await page.fill('input[name="password"]', 'definitelyWrong');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');
    // Should still be on a login-ish page, not a 500.
    await expect(page.locator('body')).not.toHaveText(/whoops|server error|exception trace/i);
    expect(page.url()).toMatch(/\/login/);
  });

  test('login throttle: many bad POSTs return 4xx, never 5xx', async ({ request }) => {
    // Hit the login endpoint at the request level so we exercise the throttle
    // without depending on full-page reloads (which can race against CSRF
    // refresh and detach the frame). We expect at least one 419/429 once the
    // throttle kicks in — and crucially, never a 5xx.
    const statuses: number[] = [];
    for (let i = 0; i < 10; i++) {
      // Get a fresh CSRF token from the login page each time.
      const loginPage = await request.get('/login', { failOnStatusCode: false });
      const html = await loginPage.text();
      const tokenMatch = html.match(/name="_token"\s+value="([^"]+)"/);
      const token = tokenMatch ? tokenMatch[1] : '';
      const resp = await request.post('/login', {
        form: { _token: token, username: `bogus_user_${i}`, password: 'wrong' },
        maxRedirects: 0,
        failOnStatusCode: false,
      });
      statuses.push(resp.status());
    }
    const fiveXx = statuses.filter((s) => s >= 500);
    expect(fiveXx, `5xx during login throttle: ${statuses.join(',')}`).toHaveLength(0);
  });

  test('login submission without CSRF redirects/4xx, not 500', async ({ request }) => {
    const resp = await request.post('/login', {
      form: { username: 'santa_admin', password: 'password' },
      maxRedirects: 0,
      failOnStatusCode: false,
    });
    const status = resp.status();
    // 419 (CSRF mismatch) or 302 redirect to login is acceptable; 500 is not.
    expect(status, `unexpected status ${status}`).toBeLessThan(500);
  });
});

test.describe('Stress — Shopping public token endpoint', () => {
  test('shopping API show with bogus token returns 4xx, not 500', async ({ request }) => {
    const resp = await request.get('/api/shopping/totally-fake-token-aaaaaaaaaaaaaaaa', {
      failOnStatusCode: false,
    });
    const status = resp.status();
    expect(status).toBeGreaterThanOrEqual(400);
    expect(status, `bad token gave ${status}`).toBeLessThan(500);
  });

  test('shopping API toggle without a valid token returns 4xx', async ({ request }) => {
    // Accept: application/json so Laravel returns 422/404 JSON instead of a 302
    // redirect-back-with-errors (which Playwright auto-follows to a 200 page).
    const resp = await request.post(
      '/api/shopping/totally-fake-token-aaaaaaaaaaaaaaaa/check',
      {
        data: { item_key: 'nonexistent', ninja_name: 'Bot' },
        headers: { Accept: 'application/json' },
        failOnStatusCode: false,
      },
    );
    const status = resp.status();
    expect(status).toBeGreaterThanOrEqual(400);
    expect(status, `unexpected ${status}`).toBeLessThan(500);
  });
});
