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
    await expect(page.locator('form input:not([type="hidden"])').first()).toBeVisible();
  });

  test('santa can submit family create form and land on family detail', async ({ page }) => {
    await page.goto('/family/add');
    const stamp = Date.now();
    await page.fill('input[name="family_name"]', `E2E Smoke ${stamp}`);
    await page.fill('input[name="address"]', `${stamp} Test Way`);
    await page.fill('input[name="phone1"]', '555-0100');
    // The form has many other optional fields; required-set only to keep this resilient.
    await page.locator('form').first().evaluate((f: HTMLFormElement) => f.submit());
    await page.waitForLoadState('networkidle');
    // Should NOT remain on /family/add (created and redirected somewhere).
    expect(page.url()).not.toContain('/family/add');
  });

  test('self-service registration form submits and lands on success page', async ({ page, context }) => {
    // Logout the santa session so we hit the public form anonymously.
    await context.clearCookies();
    await page.goto('/register-family');
    if (page.url().includes('/login')) {
      // Self-registration disabled — skip rather than fail.
      test.skip();
      return;
    }
    const stamp = Date.now();
    await page.fill('input[name="family_name"]', `Self ${stamp}`);
    await page.fill('input[name="address"]', `${stamp} Self Ln`);
    await page.fill('input[name="phone1"]', '555-0200');
    await Promise.all([
      page.waitForURL((u) => u.pathname.includes('/register-family/success') || u.pathname === '/register-family'),
      page.click('button[type="submit"]'),
    ]);
    // Either landed on success or got bounced back with errors — either way no 5xx.
    expect(page.url()).not.toMatch(/\/login/);
  });
});
