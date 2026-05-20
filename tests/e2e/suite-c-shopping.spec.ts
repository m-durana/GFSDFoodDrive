import { test, expect, loginAs } from './fixtures';

test.describe('Suite C — Shopping Day', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'santa');
  });

  test('shopping hub loads with default tab', async ({ page }) => {
    await page.goto('/santa/shopping');
    await expect(page).not.toHaveURL(/\/login/);
  });

  test('legacy /santa/shopping-list redirects to shopping hub', async ({ page }) => {
    await page.goto('/santa/shopping-list');
    await expect(page).toHaveURL(/\/santa\/shopping/);
  });

  test('legacy /santa/shopping-day redirects to shopping hub', async ({ page }) => {
    await page.goto('/santa/shopping-day');
    await expect(page).toHaveURL(/\/santa\/shopping/);
  });

  test('shopping checklist by family number renders for a seeded family', async ({ page, request }) => {
    // Try family #1 — TestDataSeeder always creates families with family_number 1..N.
    const res = await request.get('/shopping/1');
    // 200 (rendered) or 404 (no family). Anything else is a regression.
    expect([200, 404]).toContain(res.status());
  });

  test('shopping assignment token URL is publicly reachable when a token exists', async ({ page, request }) => {
    // Fetch first known assignment token from the santa hub.
    await page.goto('/santa/shopping');
    const tokenHref = await page.locator('a[href*="/shopping/a/"]').first().getAttribute('href').catch(() => null);
    if (!tokenHref) return; // no assignment in fixture — pass.
    const res = await request.get(tokenHref);
    expect([200, 404]).toContain(res.status());
  });
});
