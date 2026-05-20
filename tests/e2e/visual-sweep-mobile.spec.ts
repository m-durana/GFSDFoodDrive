/**
 * Visual sweep — mobile-only flows.
 *
 * The desktop spec already runs under all three projects (desktop-chrome,
 * mobile-chrome, mobile-safari), so it captures every panel on mobile too.
 * This file adds extra mobile-focused captures for flows that *only* make
 * sense on a phone (PWA shopping checklist, driver route, adopter
 * confirmation, mobile scanner, family-status), plus dark-mode mobile.
 *
 * Skipped on desktop-chrome — desktop sweep already covers those views.
 */

import { test } from '@playwright/test';
import { shoot, flushIndex } from './visual-sweep-util';
import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';
import type { SweepFixtures } from './visual-sweep-prep';

const fx: SweepFixtures = JSON.parse(
  readFileSync(resolve(process.cwd(), 'test-results', 'visual-sweep-fixtures.json'), 'utf-8')
);

test.beforeEach(({}, testInfo) => {
  if (testInfo.project.name === 'desktop-chrome') {
    test.skip(true, 'Mobile-only spec — desktop sweep covers these surfaces');
  }
});

test.setTimeout(300_000);

test.afterAll(() => {
  flushIndex();
});

test.describe('mobile token-bearer flows', () => {
  test('PWA-style mobile flows', async ({ page }) => {
    await shoot(page, {
      area: '10-mobile',
      name: '01-adopt-public',
      url: '/adopt',
      caption: 'Adopt-a-Tag listing on a phone',
      role: 'guest',
    });

    await shoot(page, {
      area: '10-mobile',
      name: '02-adopter-confirmation',
      url: `/adopt/mine/${fx.adopterToken}`,
      caption: 'Adopter confirmation on a phone (mark delivered button)',
      role: 'adopter-token',
    });

    await shoot(page, {
      area: '10-mobile',
      name: '03-family-status',
      url: `/family-status/${fx.familyStatusToken}`,
      caption: 'Family status page on a phone',
      role: 'family-token',
    });

    await shoot(page, {
      area: '10-mobile',
      name: '04-driver-pin',
      url: `/delivery/route/${fx.driverToken}`,
      caption: 'Driver PIN gate on a phone',
      role: 'driver-token',
    });

    // Submit the PIN, then capture authed route
    await page.goto(`/delivery/route/${fx.driverToken}`);
    try {
      await page.fill('input[name="pin"], input[type="tel"], input[type="password"]', fx.driverPin, { timeout: 5_000 });
      await page.locator('button[type="submit"]').first().click();
      await page.waitForLoadState('networkidle', { timeout: 10_000 });
    } catch {
      /* selector drift — capture whatever's there */
    }

    await shoot(page, {
      area: '10-mobile',
      name: '05-driver-route',
      url: `/delivery/route/${fx.driverToken}`,
      caption: 'Driver route on a phone (post-PIN, drivers_can_see_phone=on)',
      role: 'driver-token',
    });

    if (fx.shoppingChecklistFamilyNumber) {
      await shoot(page, {
        area: '10-mobile',
        name: '06-shopping-checklist',
        url: `/shopping/${fx.shoppingChecklistFamilyNumber}`,
        caption: 'Shopping checklist on a phone (PWA flow)',
        role: 'shopper-token',
      });
    }

    if (fx.shoppingAssignmentToken) {
      await shoot(page, {
        area: '10-mobile',
        name: '07-shopping-assignment',
        url: `/shopping/a/${fx.shoppingAssignmentToken}`,
        caption: 'Shopping assignment on a phone',
        role: 'shopper-token',
      });
    }

    await shoot(page, {
      area: '10-mobile',
      name: '08-scan-page',
      url: fx.scanSignedUrl,
      caption: 'QR scan page on a phone',
      role: 'scan-token',
    });
  });

  test('mobile dark mode', async ({ browser }) => {
    const context = await browser.newContext({ colorScheme: 'dark' });
    const page = await context.newPage();
    try {
      await shoot(page, {
        area: '11-mobile-dark',
        name: '01-adopt',
        url: '/adopt',
        caption: 'Adopt-a-Tag on phone (dark)',
        role: 'guest',
        scheme: 'dark',
      });
      await shoot(page, {
        area: '11-mobile-dark',
        name: '02-driver-pin',
        url: `/delivery/route/${fx.driverToken}`,
        caption: 'Driver PIN on phone (dark)',
        role: 'driver-token',
        scheme: 'dark',
      });
      if (fx.shoppingChecklistFamilyNumber) {
        await shoot(page, {
          area: '11-mobile-dark',
          name: '03-shopping-checklist',
          url: `/shopping/${fx.shoppingChecklistFamilyNumber}`,
          caption: 'Shopping checklist on phone (dark)',
          role: 'shopper-token',
          scheme: 'dark',
        });
      }
    } finally {
      await context.close();
    }
  });
});
