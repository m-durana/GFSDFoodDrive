import AxeBuilder from '@axe-core/playwright';
import { test, expect, loginAs } from './fixtures';

// Lightweight a11y smoke — runs axe against the most-trafficked pages and
// LOGS violations without failing. The app has pre-existing a11y debt
// (tracked separately — see ROADMAP Phase 3.5); these reports surface the
// list so it's visible in CI output without blocking the whole suite.
// Flip AXE_STRICT=1 locally to fail on serious/critical when you want to
// gate a specific page.
async function reportAxeViolations(page: any, scope: string) {
  const results = await new AxeBuilder({ page }).analyze();
  const blocking = results.violations.filter(
    (v) => v.impact === 'serious' || v.impact === 'critical',
  );
  if (blocking.length) {
    console.log(`[a11y:${scope}] ${blocking.length} serious/critical violations:`,
      blocking.map((v) => ({ id: v.id, impact: v.impact, nodes: v.nodes.length })));
  } else {
    console.log(`[a11y:${scope}] clean`);
  }
  if (process.env.AXE_STRICT) {
    expect(blocking, `${scope} has serious/critical a11y violations`).toEqual([]);
  }
}

test.describe('Accessibility smoke', () => {
  test('login page is a11y-clean', async ({ page }) => {
    await page.goto('/login');
    await reportAxeViolations(page, 'login');
  });

  test('public homepage is a11y-clean', async ({ page }) => {
    await page.goto('/');
    await reportAxeViolations(page, 'home');
  });

  test('adopt index is a11y-clean', async ({ page }) => {
    await page.goto('/adopt');
    await reportAxeViolations(page, 'adopt');
  });

  test('santa dashboard is a11y-clean', async ({ page }) => {
    await loginAs(page, 'santa');
    await page.goto('/santa');
    await reportAxeViolations(page, 'santa-dashboard');
  });
});
