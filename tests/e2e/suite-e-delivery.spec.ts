import { test, expect, loginAs } from './fixtures';

test.describe('Suite E — Delivery Day', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'santa');
  });

  test('delivery day dispatch board loads', async ({ page }) => {
    const res = await page.goto('/delivery-day');
    expect(res && res.status() < 500).toBe(true);
  });

  // TODO: ORS routing — mock the external API.
  test.skip('ORS routing generates a route (mocked external call)', async () => {});

  // TODO: driver accepts route, shares location, confirms stops.
  test.skip('driver accepts route and confirms stops', async () => {});

  // TODO: status transitions pending → en-route → delivered.
  test.skip('delivery status transitions are persisted', async () => {});
});
