import { test, expect } from './fixtures';

test.describe('Suite B — Adopt-a-Tag (public)', () => {
  test('anonymous user can browse the adoption listing', async ({ page }) => {
    await page.goto('/adopt');
    // Public route — must NOT redirect to login.
    await expect(page).not.toHaveURL(/\/login/);
  });

  // TODO: full adoption submission with email + phone, then token URL lookup.
  test.skip('adopter completes claim form and receives status URL', async () => {});

  // TODO: 3-day deadline reminder — invoke scheduled job directly + assert mail.
  test.skip('3-day deadline reminder fires for unclaimed adoptions', async () => {});
});
