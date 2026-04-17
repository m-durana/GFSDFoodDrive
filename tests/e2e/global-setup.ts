import { FullConfig } from '@playwright/test';
import { resetDatabase } from './fixtures';

export default async function globalSetup(config: FullConfig) {
  const baseURL =
    config.projects[0]?.use?.baseURL ??
    process.env.E2E_BASE_URL ??
    'http://127.0.0.1:8000';
  await resetDatabase(baseURL);
}
