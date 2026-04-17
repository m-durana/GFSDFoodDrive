import { test, expect, loginAs } from './fixtures';

test.describe('Suite A — Family Intake', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'santa');
  });

  test('family list page loads', async ({ page }) => {
    await page.goto('/family');
    await expect(page).not.toHaveURL(/\/login/);
  });

  test('family create form is reachable', async ({ page }) => {
    await page.goto('/family/add');
    await expect(page).not.toHaveURL(/\/login/);
    // Form should expose at least one input — exact selector is intentionally loose
    // so the test does not break on harmless template changes.
    await expect(page.locator('form input').first()).toBeVisible();
  });

  // TODO: full create-with-children + duplicate-detection flow once form selectors stabilize.
  test.skip('duplicate detection warns when creating a near-duplicate family', async () => {});

  // TODO: self-service public form → admin approval flow.
  test.skip('self-service registration creates a pending family record', async () => {});

  // TODO: number assignment flow within school range.
  test.skip('number assignment respects school ranges', async () => {});
});
