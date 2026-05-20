import { test, expect, loginAs } from './fixtures';

test.describe('Suite H — Command Center', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'santa');
  });

  test('command center loads with a stats grid', async ({ page }) => {
    const res = await page.goto('/santa/command-center');
    expect(res && res.status() < 500).toBe(true);
    await expect(page).not.toHaveURL(/\/login/);
  });

  // TODO: assert specific stat counts against seeded fixture data.
  test.skip('stats grid reflects seeded counts', async () => {});

  // TODO: operations snapshot reflects real delivery state.
  test.skip('operations snapshot reflects delivery state', async () => {});

  // TODO: auto-refresh polling behavior during live event window.
  test.skip('auto-refresh updates stats without full reload', async () => {});
});
