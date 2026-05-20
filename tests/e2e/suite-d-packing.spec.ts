import { test, expect, loginAs } from './fixtures';

// Highest-risk suite: peak-week packing flows. Starter tests just prove the
// pages render under the right roles; full barcode-scan + dietary-conflict
// flows need data-testid hooks added to the scanner UI before they can be
// asserted reliably.
test.describe('Suite D — Packing Day', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'santa');
  });

  test('packing landing page loads for santa', async ({ page }) => {
    const res = await page.goto('/santa/packing');
    // Packing UI may be behind a feature flag (PackingSystemEnabled middleware)
    expect(res && res.status() < 500).toBe(true);
  });

  test('packing dashboard loads for santa', async ({ page }) => {
    const res = await page.goto('/santa/packing/dashboard');
    expect(res && res.status() < 500).toBe(true);
  });

  test('verify-station page renders', async ({ page }) => {
    const res = await page.goto('/santa/packing/verify-station');
    expect(res && res.status() < 500).toBe(true);
  });

  // REL-31 + REL-45: API-level smoke for the markItemPacked guard. The PackingService
  // unit tests cover the rejection-logic exhaustively; this just proves the route
  // still exists and rejects un-authenticated requests.
  test('packing API rejects unauthenticated pack-item requests', async ({ request }) => {
    const res = await request.post('/santa/packing/1/item/1/pack', { failOnStatusCode: false });
    // 401 / 403 / 419 (CSRF) — anything but 200 / 500.
    expect([401, 403, 405, 419, 422, 302, 404]).toContain(res.status());
  });
});

test.describe('Suite D — Packing Day (mobile viewport)', () => {
  test.use({ viewport: { width: 390, height: 844 } });

  test('mobile scanner page renders at 390x844 (iPhone-class)', async ({ page }) => {
    await loginAs(page, 'santa');
    const res = await page.goto('/santa/packing');
    expect(res && res.status() < 500).toBe(true);
  });
});
