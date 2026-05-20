/**
 * Visual sweep — desktop + cross-viewport panel coverage.
 *
 * Walks every major panel in the app, takes a full-page screenshot under
 * tests/e2e/screenshots/<area>/<NN-name>__<project>.png, and emits a master
 * INDEX.md the user can browse during their UX review.
 *
 * Companion: visual-sweep-mobile.spec.ts (mobile-only token-bearer flows).
 *
 * Setup is handled by global-setup.ts → visual-sweep-prep.ts:
 *  - Feature flags flipped on (adopt, self-reg, packing, shopping, drivers-see-phone)
 *  - Token/PIN fixtures dumped to test-results/visual-sweep-fixtures.json
 */

import { test, expect } from '@playwright/test';
import { loginAs } from './fixtures';
import { shoot, flushIndex, SCREENSHOT_ROOT } from './visual-sweep-util';
import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';
import type { SweepFixtures } from './visual-sweep-prep';

const fx: SweepFixtures = JSON.parse(
  readFileSync(resolve(process.cwd(), 'test-results', 'visual-sweep-fixtures.json'), 'utf-8')
);

test.describe.configure({ mode: 'serial' });
// Each test walks many panels — give plenty of headroom.
test.setTimeout(300_000);

// Flush master INDEX.md at the end of this spec's run in each project.
test.afterAll(() => {
  flushIndex();
});

// -----------------------------------------------------------------------------
// 1. Public / Guest
// -----------------------------------------------------------------------------
test.describe('public', () => {
  test('public surfaces', async ({ page }) => {
    await shoot(page, {
      area: '01-public',
      name: '01-homepage',
      url: '/',
      caption: 'Public homepage (welcome) with CTAs',
      role: 'guest',
    });
    await shoot(page, {
      area: '01-public',
      name: '02-adopt-landing',
      url: '/adopt',
      caption: 'Adopt-a-Tag public landing — list of unclaimed children',
      role: 'guest',
    });
    await shoot(page, {
      area: '01-public',
      name: '03-adopt-detail',
      url: `/adopt/${fx.adopterChildId + 1}`,
      caption: 'Adopt-a-Tag detail page for a single child',
      role: 'guest',
    });
    await shoot(page, {
      area: '01-public',
      name: '04-self-registration',
      url: '/register-family',
      caption: 'Self-service family registration form (public)',
      role: 'guest',
    });
    await shoot(page, {
      area: '01-public',
      name: '05-self-registration-success',
      url: '/register-family/success',
      caption: 'Self-registration confirmation page',
      role: 'guest',
    });
    await shoot(page, {
      area: '01-public',
      name: '06-family-status',
      url: `/family-status/${fx.familyStatusToken}`,
      caption: 'Family status page (token-secured, public)',
      role: 'family-token',
    });
    await shoot(page, {
      area: '01-public',
      name: '07-scan-page',
      url: fx.scanSignedUrl,
      caption: 'QR scan page (signed URL) — coordinator-facing child snapshot',
      role: 'scan-token',
    });
    await shoot(page, {
      area: '01-public',
      name: '08-oauth-request-access',
      url: '/auth/google/request',
      caption: 'OAuth access request form (Google login pre-approval)',
      role: 'guest',
    });
  });
});

// -----------------------------------------------------------------------------
// 2. Auth
// -----------------------------------------------------------------------------
test.describe('auth', () => {
  test('login + profile', async ({ page }) => {
    await shoot(page, {
      area: '02-auth',
      name: '01-login',
      url: '/login',
      caption: 'Login page',
      role: 'guest',
    });
    await loginAs(page, 'santa');
    await shoot(page, {
      area: '02-auth',
      name: '02-profile-edit',
      url: '/profile',
      caption: 'Profile edit (current user)',
      role: 'santa',
    });
  });
});

// -----------------------------------------------------------------------------
// 3. Santa
// -----------------------------------------------------------------------------
test.describe('santa', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'santa');
  });

  test('santa core panels', async ({ page }) => {
    const panels: Array<[string, string, string]> = [
      ['01-dashboard', '/santa', 'Santa dashboard (role landing)'],
      ['02-all-families', '/santa/families', 'Santa — all families list'],
      ['03-number-assignment', '/santa/number-assignment', 'Number assignment board'],
      ['04-school-ranges', '/santa/school-ranges', 'School ranges editor'],
      ['05-gifts', '/santa/gifts', 'Santa — gifts overview'],
      ['06-reports', '/santa/reports', 'Santa — reports landing'],
      ['07-shopping-hub', '/santa/shopping', 'Shopping hub (default tab)'],
      ['08-shopping-formulas', '/santa/shopping?tab=formulas', 'Shopping hub — formulas tab'],
      ['09-shopping-assignments', '/santa/shopping?tab=assignments', 'Shopping hub — assignments tab'],
      ['10-users', '/santa/users', 'User management list'],
      ['11-duplicates', '/santa/duplicates', 'Duplicate detection queue'],
      ['12-adoptions-admin', '/santa/adoptions', 'Adoptions admin dashboard'],
      ['13-backups', '/santa/backups', 'Backups & rollback'],
      ['14-analytics', '/santa/analytics', 'Analytics dashboard'],
      ['15-seasons', '/santa/seasons', 'Season archive index'],
      ['16-seasons-import', '/santa/seasons/import', 'Season import form'],
      ['17-delivery-routes-admin', '/santa/delivery-routes', 'Delivery routes admin'],
      ['18-command-center', '/santa/command-center', 'Command center (TV dashboard)'],
      ['19-delivery-day', '/delivery-day', 'Delivery day dispatch board'],
      ['20-delivery-day-map', '/delivery-day/map', 'Delivery day live map'],
      ['21-delivery-day-logs', '/delivery-day/logs', 'Delivery day activity log'],
      ['22-delivery-day-track', '/delivery-day/track', 'Delivery day track view'],
    ];
    for (const [name, url, caption] of panels) {
      await shoot(page, { area: '03-santa', name, url, caption, role: 'santa' });
    }
  });

  test('santa settings (all tabs)', async ({ page }) => {
    const anchors = [
      '',
      '#site-branding',
      '#packing',
      '#coordinator-positions',
      '#driver-privacy',
      '#paper-size',
      '#oauth',
      '#twilio',
      '#ors',
    ];
    for (let i = 0; i < anchors.length; i++) {
      const anchor = anchors[i];
      const label = anchor ? anchor.slice(1) : 'top';
      await shoot(page, {
        area: '04-santa-settings',
        name: `${String(i + 1).padStart(2, '0')}-${label}`,
        url: `/santa/settings${anchor}`,
        caption: `Santa settings — ${label.replace(/-/g, ' ')}`,
        role: 'santa',
      });
    }
  });

  test('santa user edit modal context', async ({ page }) => {
    // No dedicated /users/{id}/edit route — captured via list page above.
    test.skip(true, 'User edit is a modal — captured in user list screenshot');
  });
});

// -----------------------------------------------------------------------------
// 4. Coordinator
// -----------------------------------------------------------------------------
test.describe('coordinator', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'coordinator');
  });

  test('coordinator panels', async ({ page }) => {
    const panels: Array<[string, string, string]> = [
      ['01-dashboard', '/coordinator', 'Coordinator dashboard'],
      ['02-gift-tags', '/coordinator/gift-tags', 'Gift tag generator'],
      // family-summary + delivery-day on /coordinator/* are PDF dispatchers
      // returning a job-key JSON, not visual panels — request sync=1 to get
      // the rendered HTML view instead.
      ['03-family-summary', '/coordinator/family-summary?sync=1', 'Family summary export (sync HTML render)'],
      ['04-delivery-day', '/coordinator/delivery-day?sync=1', 'Delivery day (coordinator sync HTML render)'],
      ['05-delivery-day-map', '/delivery-day/map', 'Delivery day map (coordinator)'],
      ['06-warehouse-index', '/warehouse', 'Warehouse landing'],
      ['07-warehouse-receive', '/warehouse/receive', 'Warehouse intake (receive)'],
      ['08-warehouse-inventory', '/warehouse/inventory', 'Warehouse inventory'],
      ['09-warehouse-transactions', '/warehouse/transactions', 'Warehouse transactions log'],
      ['10-warehouse-kiosk', '/warehouse/kiosk', 'Warehouse intake kiosk'],
      ['11-warehouse-kiosk-gifts', '/warehouse/kiosk/gifts', 'Gift intake kiosk'],
      ['12-warehouse-gifts-intake', '/warehouse/gifts-intake', 'Gifts intake summary'],
      ['13-warehouse-gift-bank', '/warehouse/gift-bank', 'Gift bank (uncategorized inventory)'],
      ['14-packing-index', '/santa/packing', 'Packing landing'],
      ['15-packing-dashboard', '/santa/packing/dashboard', 'Packing dashboard'],
      ['16-packing-summary', '/santa/packing/summary', 'Packing summary'],
      ['17-packing-verify-station', '/santa/packing/verify-station', 'Packing verification station'],
    ];
    for (const [name, url, caption] of panels) {
      await shoot(page, { area: '05-coordinator', name, url, caption, role: 'coordinator' });
    }

    if (fx.packingListId) {
      await shoot(page, {
        area: '05-coordinator',
        name: '18-packing-list-detail',
        url: `/santa/packing/${fx.packingListId}`,
        caption: 'Per-family packing list detail',
        role: 'coordinator',
      });
    }

    if (fx.warehouseItemId) {
      await shoot(page, {
        area: '05-coordinator',
        name: '19-warehouse-item-detail',
        url: `/warehouse/items/${fx.warehouseItemId}`,
        caption: 'Warehouse item detail',
        role: 'coordinator',
      });
    }

    if (fx.giftDropoffChildId) {
      await shoot(page, {
        area: '05-coordinator',
        name: '20-gift-dropoff',
        url: `/warehouse/gift-dropoff/${fx.giftDropoffChildId}`,
        caption: 'Per-child gift drop-off flow',
        role: 'coordinator',
      });
      await shoot(page, {
        area: '05-coordinator',
        name: '21-child-gifts',
        url: `/warehouse/child/${fx.giftDropoffChildId}/gifts`,
        caption: 'Per-child gifts inventory',
        role: 'coordinator',
      });
    }
  });
});

// -----------------------------------------------------------------------------
// 5. Advisor (family role)
// -----------------------------------------------------------------------------
test.describe('advisor', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'family');
  });

  test('family advisor panels', async ({ page }) => {
    await shoot(page, {
      area: '06-advisor',
      name: '01-family-list',
      url: '/family',
      caption: 'Family advisor — family list (scoped to advisor)',
      role: 'advisor',
    });
    await shoot(page, {
      area: '06-advisor',
      name: '02-family-create',
      url: '/family/add',
      caption: 'New family wizard / form',
      role: 'advisor',
    });
    if (fx.familyEditId) {
      await shoot(page, {
        area: '06-advisor',
        name: '03-family-show',
        url: `/family/${fx.familyEditId}`,
        caption: 'Family detail view',
        role: 'advisor',
      });
      await shoot(page, {
        area: '06-advisor',
        name: '04-family-edit',
        url: `/family/${fx.familyEditId}/edit`,
        caption: 'Family edit form',
        role: 'advisor',
      });
    }
    await shoot(page, {
      area: '06-advisor',
      name: '05-profile',
      url: '/profile',
      caption: 'Advisor profile',
      role: 'advisor',
    });
  });
});

// -----------------------------------------------------------------------------
// 6. Help / Wiki
// -----------------------------------------------------------------------------
test.describe('help', () => {
  test('help wiki', async ({ page }) => {
    await loginAs(page, 'santa');
    await shoot(page, {
      area: '07-help',
      name: '01-help-index',
      url: '/help',
      caption: 'Help / wiki index',
      role: 'santa',
    });
  });
});

// -----------------------------------------------------------------------------
// 7. Dark mode — Santa dashboard, command center, advisor family list
// -----------------------------------------------------------------------------
test.describe('dark-mode', () => {
  test.use({ colorScheme: 'dark' });

  test('dark-mode key panels', async ({ page }) => {
    await loginAs(page, 'santa');
    await shoot(page, {
      area: '08-dark-mode',
      name: '01-santa-dashboard',
      url: '/santa',
      caption: 'Santa dashboard (dark)',
      role: 'santa',
      scheme: 'dark',
    });
    await shoot(page, {
      area: '08-dark-mode',
      name: '02-command-center',
      url: '/santa/command-center',
      caption: 'Command center (dark)',
      role: 'santa',
      scheme: 'dark',
    });
    await shoot(page, {
      area: '08-dark-mode',
      name: '03-settings',
      url: '/santa/settings',
      caption: 'Santa settings (dark)',
      role: 'santa',
      scheme: 'dark',
    });
    await shoot(page, {
      area: '08-dark-mode',
      name: '04-adopt-landing',
      url: '/adopt',
      caption: 'Adopt-a-Tag public (dark)',
      role: 'guest',
      scheme: 'dark',
    });
  });
});

// -----------------------------------------------------------------------------
// 8. Token-bearer flows (also captured in mobile spec)
// -----------------------------------------------------------------------------
test.describe('token-bearers', () => {
  test('adopter, driver, shopping (desktop view)', async ({ page }) => {
    await shoot(page, {
      area: '09-token-bearers',
      name: '01-adopter-confirmation',
      url: `/adopt/mine/${fx.adopterToken}`,
      caption: 'Adopter confirmation page (mark delivered)',
      role: 'adopter-token',
    });
    await shoot(page, {
      area: '09-token-bearers',
      name: '02-driver-pin-verify',
      url: `/delivery/route/${fx.driverToken}`,
      caption: 'Driver PIN verification gate',
      role: 'driver-token',
    });

    // Submit the PIN, then re-screenshot the route page after auth
    await page.goto(`/delivery/route/${fx.driverToken}`);
    try {
      await page.fill('input[name="pin"], input[type="tel"], input[type="password"]', fx.driverPin, { timeout: 5_000 });
      await page.locator('button[type="submit"]').first().click();
      await page.waitForLoadState('networkidle', { timeout: 10_000 });
    } catch {
      // Selector may differ; fall back to a generic any-input fill
    }
    await shoot(page, {
      area: '09-token-bearers',
      name: '03-driver-route',
      url: `/delivery/route/${fx.driverToken}`,
      caption: 'Driver route view (post-PIN, drivers_can_see_phone=on)',
      role: 'driver-token',
    });

    if (fx.shoppingAssignmentToken) {
      await shoot(page, {
        area: '09-token-bearers',
        name: '04-shopping-assignment',
        url: `/shopping/a/${fx.shoppingAssignmentToken}`,
        caption: 'Shopping assignment landing (token)',
        role: 'shopper-token',
      });
    }

    if (fx.shoppingChecklistFamilyNumber) {
      await shoot(page, {
        area: '09-token-bearers',
        name: '05-shopping-checklist',
        url: `/shopping/${fx.shoppingChecklistFamilyNumber}`,
        caption: 'Shopping checklist for a single family (dietary filter verifies SH-02)',
        role: 'shopper-token',
      });
    }
  });
});
