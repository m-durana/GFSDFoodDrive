import { FullConfig } from '@playwright/test';
import { resetDatabase } from './fixtures';
import { prepareVisualSweepFixtures } from './visual-sweep-prep';

export default async function globalSetup(config: FullConfig) {
  const baseURL =
    config.projects[0]?.use?.baseURL ??
    process.env.E2E_BASE_URL ??
    'http://127.0.0.1:8000';
  await resetDatabase(baseURL);

  // Visual-sweep fixtures: flip feature flags + grab tokens/PINs needed
  // by the visual-sweep specs. Cheap and always runnable, so do it
  // unconditionally; non-sweep specs just ignore the cache file.
  try {
    prepareVisualSweepFixtures(true);
  } catch (e) {
    console.warn('[global-setup] visual-sweep prep failed (non-fatal):', e);
  }
}
