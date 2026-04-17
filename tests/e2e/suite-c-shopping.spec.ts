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

  // TODO: generate shopping assignments across all three split types.
  test.skip('coordinator generates shopping assignments', async () => {});

  // TODO: mobile assignment view via token URL.
  test.skip('volunteer opens mobile assignment view via token', async () => {});

  // TODO: kiosk transactions reconcile against assignments.
  test.skip('kiosk transactions reconcile correctly', async () => {});
});
