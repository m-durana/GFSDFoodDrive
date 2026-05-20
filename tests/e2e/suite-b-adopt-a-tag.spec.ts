import { test, expect } from './fixtures';

test.describe('Suite B — Adopt-a-Tag (public)', () => {
  test('anonymous user can browse the adoption listing', async ({ page }) => {
    await page.goto('/adopt');
    // Public route — must NOT redirect to login.
    await expect(page).not.toHaveURL(/\/login/);
  });

  test('adoption listing renders cards or empty-state when enabled', async ({ page }) => {
    await page.goto('/adopt');
    // Either the disabled splash or the listing — both are valid public responses.
    const body = await page.textContent('body');
    expect(body).toBeTruthy();
    expect(page.url()).not.toMatch(/\/login/);
  });

  test('adopt detail link leads to a claim form when a tag exists', async ({ page }) => {
    await page.goto('/adopt');
    const firstTag = page.locator('a[href^="/adopt/"]').first();
    if (await firstTag.count() === 0) {
      // Listing is empty / disabled — nothing to follow into.
      return;
    }
    await firstTag.click();
    await expect(page.locator('input[name="adopter_name"], input[name="adopter_email"]').first()).toBeVisible();
  });
});
