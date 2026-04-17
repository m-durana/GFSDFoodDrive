import { test as base, expect, Page } from '@playwright/test';

export type Role = 'santa' | 'coordinator' | 'family' | 'driver';

const CREDENTIALS: Record<Role, { username: string; password: string }> = {
  santa: { username: 'santa_admin', password: 'password' },
  coordinator: { username: 'coord_01', password: 'password' },
  family: { username: 'family_advisor', password: 'password' },
  driver: { username: 'driver_alex', password: 'password' },
};

export async function loginAs(page: Page, role: Role) {
  const creds = CREDENTIALS[role];
  await page.goto('/login');
  await page.fill('input[name="username"]', creds.username);
  await page.fill('input[name="password"]', creds.password);
  await Promise.all([
    page.waitForURL((url) => !url.pathname.includes('/login')),
    page.click('button[type="submit"]'),
  ]);
}

export async function logout(page: Page) {
  await page.evaluate(async () => {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
    await fetch('/logout', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
    });
  });
}

/**
 * Seeds a fresh test database via the /__e2e/reset endpoint (non-prod only).
 * Call from globalSetup or per-suite beforeAll — not before every test (slow).
 */
export async function resetDatabase(baseURL: string) {
  const res = await fetch(`${baseURL}/__e2e/reset`, {
    method: 'POST',
    headers: { 'X-E2E-Token': process.env.E2E_RESET_TOKEN ?? 'e2e-local-token' },
  });
  if (!res.ok) {
    throw new Error(`E2E reset failed: ${res.status} ${await res.text()}`);
  }
}

export const test = base.extend({});
export { expect };
export { CREDENTIALS };
