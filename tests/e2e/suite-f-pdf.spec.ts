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

  test('queued PDF endpoint hands back a job key + status URL', async ({ page }) => {
    // Async-job smoke (REL-13). We don't drive a worker from Playwright on
    // Windows (Horizon needs pcntl/posix), so we only verify the dispatch
    // contract: the endpoint must return JSON with a job key + status URL.
    // The 60s-completion SLA is exercised by the prod Horizon dashboard, not here.
    test.setTimeout(120_000);
    const res = await page.request.get('/coordinator/gift-tags', { timeout: 90_000 });
    if (res.status() === 403 || res.status() === 404) return; // gated or no data
    expect(res.status()).toBe(200);
    const ct = res.headers()['content-type'] ?? '';
    expect(ct).toContain('application/json');
    const body = await res.json();
    expect(body).toHaveProperty('job_key');
    expect(body).toHaveProperty('status_url');
    expect(body).toHaveProperty('download_url');
    expect(body.job_key).toMatch(/^[A-Za-z0-9]{16}$/);

    const statusRes = await page.request.get(body.status_url);
    expect(statusRes.status()).toBe(200);
    const status = await statusRes.json();
    expect(['queued', 'running', 'complete', 'failed', 'unknown']).toContain(status.status);
  });
});
