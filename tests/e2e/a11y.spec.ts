import AxeBuilder from '@axe-core/playwright';
import { test, expect, loginAs } from './fixtures';

// Lightweight a11y smoke — runs axe against the most-trafficked pages.
// Fails ONLY on serious + critical violations; everything else is reported but
// non-blocking, so the suite is usable mid-iteration.
async function expectNoSeriousAxeViolations(page: any, scope: string) {
  const results = await new AxeBuilder({ page }).analyze();
  const blocking = results.violations.filter(
    (v) => v.impact === 'serious' || v.impact === 'critical',
  );
  if (blocking.length) {
    console.log(`[a11y:${scope}] ${blocking.length} blocking violations`,
      blocking.map((v) => ({ id: v.id, impact: v.impact, nodes: v.nodes.length })));
  }
  expect(blocking, `${scope} has serious/critical a11y violations`).toEqual([]);
}

test.describe('Accessibility smoke', () => {
  test('login page is a11y-clean', async ({ page }) => {
    await page.goto('/login');
    await expectNoSeriousAxeViolations(page, 'login');
  });

  test('public homepage is a11y-clean', async ({ page }) => {
    await page.goto('/');
    await expectNoSeriousAxeViolations(page, 'home');
  });

  test('adopt index is a11y-clean', async ({ page }) => {
    await page.goto('/adopt');
    await expectNoSeriousAxeViolations(page, 'adopt');
  });

  test('santa dashboard is a11y-clean', async ({ page }) => {
    await loginAs(page, 'santa');
    await page.goto('/santa');
    await expectNoSeriousAxeViolations(page, 'santa-dashboard');
  });
});
