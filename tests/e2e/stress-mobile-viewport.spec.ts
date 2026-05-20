import { test, expect, loginAs } from './fixtures';

/**
 * Mobile-viewport stress tests. Runs across all configured projects but is
 * most meaningful under `--project=mobile-chrome` / `--project=mobile-safari`.
 *
 * Focus: hamburger / drawer toggles, kiosk + scan pages, public adopt page,
 * tap-target hit areas. We do not assert visual pixels — only that the page
 * loads, no 5xx, and the obvious nav affordance is present + interactable.
 */

test.describe('Mobile — Public surfaces load without errors', () => {
  const PUBLIC_PATHS = ['/', '/login', '/adopt', '/register-family'];

  for (const path of PUBLIC_PATHS) {
    test(`public path ${path} renders without 5xx`, async ({ page }) => {
      const errs: string[] = [];
      page.on('response', (resp) => {
        if (resp.status() >= 500) errs.push(`${resp.status()} ${resp.url()}`);
      });
      page.on('pageerror', (err) => errs.push(`pageerror: ${err.message}`));
      const resp = await page.goto(path);
      const s = resp?.status() ?? 0;
      expect(s, `top-level ${s} for ${path}`).toBeLessThan(500);
      await page.waitForLoadState('domcontentloaded');
      expect(errs, errs.join('\n')).toHaveLength(0);
    });
  }
});

test.describe('Mobile — Santa pages reachable from a small viewport', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'santa');
  });

  test('santa dashboard renders and has a visible nav affordance', async ({ page }) => {
    await page.goto('/santa');
    // Don't assert a specific hamburger selector — different themes use different
    // markup. Instead, assert *some* button-like nav control exists in the
    // viewport. If the page has zero buttons in a mobile layout, that's a bug.
    const buttonCount = await page.locator('button, [role="button"], a.btn').count();
    expect(buttonCount, 'expected at least one button/nav control on mobile santa dashboard').toBeGreaterThan(0);
  });

  test('warehouse mobile-scan page renders on small viewport', async ({ page }) => {
    const resp = await page.goto('/warehouse/mobile-scan');
    expect(resp?.status() ?? 0).toBeLessThan(500);
    // Page should not have collapsed to width 0.
    const bodyBox = await page.locator('body').boundingBox();
    expect(bodyBox?.width ?? 0).toBeGreaterThan(300);
  });

  test('warehouse kiosk page renders on small viewport', async ({ page }) => {
    const resp = await page.goto('/warehouse/kiosk');
    expect(resp?.status() ?? 0).toBeLessThan(500);
  });

  test('packing dashboard renders on small viewport', async ({ page }) => {
    const resp = await page.goto('/santa/packing/dashboard');
    expect(resp?.status() ?? 0).toBeLessThan(500);
  });

  test('command center is usable on small viewport (stats present)', async ({ page }) => {
    const resp = await page.goto('/santa/command-center');
    expect(resp?.status() ?? 0).toBeLessThan(500);
    // Stats grid should expose at least some text content.
    const text = await page.locator('body').textContent();
    expect((text ?? '').trim().length, 'command center body empty').toBeGreaterThan(50);
  });
});
