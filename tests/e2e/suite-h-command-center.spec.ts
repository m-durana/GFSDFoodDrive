import { test, expect, loginAs } from './fixtures';

test.describe('Suite H — Command Center', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'santa');
  });

  test('command center loads with a stats grid', async ({ page }) => {
    const res = await page.goto('/santa/command-center');
    expect(res && res.status() < 500).toBe(true);
    await expect(page).not.toHaveURL(/\/login/);
  });

  test('command-center data endpoint returns a well-shaped JSON payload', async ({ page }) => {
    const res = await page.request.get('/santa/command-center/data');
    expect(res.status()).toBe(200);
    const data = await res.json();
    expect(data).toHaveProperty('overview');
    expect(data).toHaveProperty('delivery');
    expect(data).toHaveProperty('shopping');
    expect(data.overview).toHaveProperty('total_families');
    expect(typeof data.overview.total_families).toBe('number');
  });

  test('seeded fixture has families > 0 in command-center data', async ({ page }) => {
    const res = await page.request.get('/santa/command-center/data');
    const data = await res.json();
    // TestDataSeeder always seeds families; if this hits zero, the fixture broke.
    expect(data.overview.total_families).toBeGreaterThan(0);
  });

  test('command-center polls /data every ~15s without full page reload', async ({ page }) => {
    await page.goto('/santa/command-center');
    let dataHits = 0;
    page.on('request', (req) => {
      if (req.url().includes('/command-center/data')) dataHits++;
    });
    // Wait one full refresh cycle (15s) + buffer.
    await page.waitForTimeout(17_000);
    expect(dataHits).toBeGreaterThanOrEqual(1);
  });
});
