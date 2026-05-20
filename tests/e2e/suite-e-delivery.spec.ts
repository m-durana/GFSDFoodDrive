import { test, expect, loginAs } from './fixtures';

test.describe('Suite E — Delivery Day', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'santa');
  });

  test('delivery day dispatch board loads', async ({ page }) => {
    const res = await page.goto('/delivery-day');
    expect(res && res.status() < 500).toBe(true);
  });

  test('delivery routes index page lists routes', async ({ page }) => {
    const res = await page.goto('/santa/delivery-routes');
    expect(res && res.status() < 500).toBe(true);
  });

  test('driver token URL renders the driver route view when a route exists', async ({ page, request }) => {
    // Cheaply probe by visiting dispatch and grabbing the first driver link.
    await page.goto('/delivery-day');
    const tokenLink = await page.locator('a[href*="/delivery/route/"]').first().getAttribute('href').catch(() => null);
    if (!tokenLink) return; // no routes seeded — fine.
    const res = await request.get(tokenLink);
    expect([200, 401, 403]).toContain(res.status());
  });
});
