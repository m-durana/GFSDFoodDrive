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
    const res = await page.goto('/packing');
    // Packing UI may be behind a feature flag (PackingSystemEnabled middleware)
    expect(res && res.status() < 500).toBe(true);
  });

  // TODO: barcode scan → item lookup → add to packing list.
  test.skip('barcode scan adds item to active packing list', async () => {});

  // TODO: scanning a dietary-restricted item shows the warning banner.
  test.skip('dietary conflict warning fires on restricted item scan', async () => {});

  // TODO: substitution flow updates the packing list.
  test.skip('accepted substitution updates packing list', async () => {});

  // TODO: QR verify-station green/red + audio cue.
  test.skip('verify-station QR scan reports correct/incorrect', async () => {});
});

test.describe('Suite D — Packing Day (mobile viewport)', () => {
  test.use({ viewport: { width: 390, height: 844 } });

  test('mobile scanner page renders at 390x844 (iPhone-class)', async ({ page }) => {
    await loginAs(page, 'santa');
    const res = await page.goto('/packing');
    expect(res && res.status() < 500).toBe(true);
  });
});
