/**
 * Shared helpers for the visual-sweep specs:
 *  - shoot(): navigate, wait, full-page screenshot, append index entry
 *  - flushIndex(): write the combined INDEX.md
 *
 * Index entries are accumulated per-process (each Playwright project runs
 * in its own process) and merged on disk under a project-suffixed JSON
 * file. flushIndex() reads all shards and writes the master INDEX.md.
 */

import { Page, test } from '@playwright/test';
import { appendFileSync, existsSync, mkdirSync, readFileSync, readdirSync, writeFileSync } from 'node:fs';
import { dirname, resolve, join } from 'node:path';

export const SCREENSHOT_ROOT = resolve(process.cwd(), 'tests', 'e2e', 'screenshots');
const INDEX_SHARD_DIR = resolve(SCREENSHOT_ROOT, '_index_shards');
const ISSUE_LOG = resolve(SCREENSHOT_ROOT, 'ISSUES.log');

type IndexEntry = {
  area: string;
  file: string; // relative to SCREENSHOT_ROOT
  caption: string;
  role: string;
  project: string;
  scheme?: 'light' | 'dark';
};

const entries: IndexEntry[] = [];

export async function shoot(
  page: Page,
  opts: {
    area: string;
    name: string; // e.g. "01-dashboard"
    url: string;
    caption: string;
    role: string;
    scheme?: 'light' | 'dark';
    waitFor?: () => Promise<void>;
  }
): Promise<{ ok: boolean; status: number | null; file: string }> {
  const project = test.info().project.name;
  const fileRel = join(opts.area, `${opts.name}__${project}${opts.scheme === 'dark' ? '_dark' : ''}.png`);
  const absPath = join(SCREENSHOT_ROOT, fileRel);
  mkdirSync(dirname(absPath), { recursive: true });

  let status: number | null = null;
  try {
    const resp = await page.goto(opts.url, { waitUntil: 'domcontentloaded', timeout: 30_000 });
    status = resp?.status() ?? null;
    // For non-200, still capture so reviewer sees the error page.
    try {
      await page.waitForLoadState('networkidle', { timeout: 8_000 });
    } catch {
      /* ignore */
    }
    if (opts.waitFor) {
      try {
        await opts.waitFor();
      } catch {
        /* ignore */
      }
    }
  } catch (e) {
    // navigation crash — still try to screenshot whatever's there
  }

  await page.screenshot({ path: absPath, fullPage: true, timeout: 30_000 }).catch(() => {});

  // Log any non-OK status so the user gets a list of broken/404 panels.
  if (status === null || status >= 400) {
    try {
      mkdirSync(SCREENSHOT_ROOT, { recursive: true });
      appendFileSync(
        ISSUE_LOG,
        `${new Date().toISOString()}\t${project}\t${status ?? 'NAV_FAIL'}\t${opts.url}\t${fileRel}\n`
      );
    } catch {
      /* ignore */
    }
  }

  entries.push({
    area: opts.area,
    file: fileRel.replace(/\\/g, '/'),
    caption: opts.caption,
    role: opts.role,
    project,
    scheme: opts.scheme,
  });

  return { ok: status !== null && status < 400, status, file: fileRel };
}

export function flushIndexShard(): void {
  if (entries.length === 0) return;
  mkdirSync(INDEX_SHARD_DIR, { recursive: true });
  const project = test.info().project?.name ?? 'unknown';
  const shardFile = join(INDEX_SHARD_DIR, `${project}-${process.pid}.json`);
  writeFileSync(shardFile, JSON.stringify(entries, null, 2));
}

/**
 * Read all shards and write the master INDEX.md. Safe to call at the end of
 * each project run — last writer wins, and shards from earlier projects are
 * still on disk so they get merged in.
 */
export function flushIndex(): void {
  flushIndexShard();
  if (!existsSync(INDEX_SHARD_DIR)) return;

  const all: IndexEntry[] = [];
  for (const f of readdirSync(INDEX_SHARD_DIR)) {
    if (!f.endsWith('.json')) continue;
    try {
      const shard = JSON.parse(readFileSync(join(INDEX_SHARD_DIR, f), 'utf-8')) as IndexEntry[];
      all.push(...shard);
    } catch {
      /* ignore corrupt shard */
    }
  }

  // De-dupe by file path (a single screenshot path uniquely identifies an entry)
  const seen = new Set<string>();
  const deduped = all.filter((e) => {
    if (seen.has(e.file)) return false;
    seen.add(e.file);
    return true;
  });

  // Group by area
  const byArea = new Map<string, IndexEntry[]>();
  for (const e of deduped) {
    if (!byArea.has(e.area)) byArea.set(e.area, []);
    byArea.get(e.area)!.push(e);
  }

  const lines: string[] = [
    '# Visual Sweep Screenshot Index',
    '',
    `Generated: ${new Date().toISOString()}`,
    `Total screenshots: **${deduped.length}**`,
    '',
    'Browse by area. Each row: filename — role / project / scheme — caption.',
    '',
  ];

  const sortedAreas = [...byArea.keys()].sort();
  for (const area of sortedAreas) {
    lines.push(`## ${area}`);
    lines.push('');
    lines.push('| Screenshot | Role | Project | Scheme | Caption |');
    lines.push('|---|---|---|---|---|');
    const rows = byArea.get(area)!.sort((a, b) => a.file.localeCompare(b.file));
    for (const r of rows) {
      lines.push(
        `| [${r.file}](${r.file}) | ${r.role} | ${r.project} | ${r.scheme ?? 'light'} | ${r.caption.replace(/\|/g, '\\|')} |`
      );
    }
    lines.push('');
  }

  writeFileSync(resolve(SCREENSHOT_ROOT, 'INDEX.md'), lines.join('\n'));
}
