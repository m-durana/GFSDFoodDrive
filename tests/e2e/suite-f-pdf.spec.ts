import { test, expect, loginAs } from './fixtures';

test.describe('Suite F — PDF Generation', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'santa');
  });

  // TODO: trigger a real gift-tag PDF batch and assert downloaded bytes are
  // a non-empty %PDF- file. Requires a known route + button selector.
  test.skip('gift tag PDF downloads as a valid non-empty file', async () => {});

  test.skip('family summary PDF generates', async () => {});
  test.skip('delivery day sheet PDF generates', async () => {});

  // Async-job SLA test — once Phase 1.3 (Horizon) lands, assert that a queued
  // batch of 100 tags completes within 60s.
  test.skip('queued PDF batch of 100 tags completes within 60s', async () => {});
});
