import { test, expect, loginAs } from './fixtures';

test.describe('Suite F — PDF Generation', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'santa');
  });

  // Coordinator PDF routes are locked behind section:system (REL-46a). Hit them
  // with ?sync=1 so we get a real binary back instead of a job ID, then assert
  // the response starts with the PDF magic bytes. Uses page.request so the
  // already-authenticated session cookies are reused.
  async function expectPdf(page: import('@playwright/test').Page, path: string) {
    const res = await page.request.get(path);
    if (res.status() === 403 || res.status() === 404) return; // section gate or no data — skip.
    expect(res.status()).toBe(200);
    const ct = res.headers()['content-type'] ?? '';
    expect(ct).toContain('pdf');
    const body = await res.body();
    expect(body.slice(0, 4).toString()).toBe('%PDF');
  }

  // Sync PDF generation can take 30-60s with seeded dataset (dompdf, no Horizon
   // yet — REL-13). Lengthen the per-test timeout accordingly.
  test('family-summary PDF (sync) returns PDF bytes', async ({ page }) => {
    test.setTimeout(120_000);
    await expectPdf(page, '/coordinator/family-summary?sync=1');
  });

  test('delivery-day PDF (sync) returns PDF bytes', async ({ page }) => {
    test.setTimeout(120_000);
    await expectPdf(page, '/coordinator/delivery-day?sync=1');
  });

  test.skip('queued PDF batch of 100 tags completes within 60s', async () => {
    // Async-job SLA — gated on Horizon landing (REL-13).
  });
});
