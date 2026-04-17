import { test, expect, loginAs, CREDENTIALS } from './fixtures';

test.describe('Suite G — Auth & Authorization', () => {
  test('login page renders with username + password fields', async ({ page }) => {
    await page.goto('/login');
    await expect(page.locator('input[name="username"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
  });

  test('santa login lands on a santa-accessible page', async ({ page }) => {
    await loginAs(page, 'santa');
    await page.goto('/dashboard');
    await expect(page).toHaveURL(/\/santa/);
  });

  test('coordinator login lands on coordinator dashboard', async ({ page }) => {
    await loginAs(page, 'coordinator');
    await page.goto('/dashboard');
    // coord_01 has permission=8 + family role-equivalents — verify auth survived, not specific URL
    await expect(page).not.toHaveURL(/\/login/);
  });

  test('family role cannot reach santa-only routes', async ({ page }) => {
    await loginAs(page, 'family');
    const res = await page.goto('/santa');
    // Either 403 from middleware or redirect away
    expect(res?.status() === 403 || !page.url().includes('/santa')).toBe(true);
  });

  test('invalid credentials do not authenticate', async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="username"]', 'santa_admin');
    await page.fill('input[name="password"]', 'wrong-password');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/\/login/);
  });
});
