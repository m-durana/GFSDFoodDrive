// REL-07 / P4 — page-side entry. Mounts the sync engine on `window.packingSync`
// so existing Alpine.js scanner components can enqueue actions and subscribe
// to events without bundling the engine themselves.
//
// Usage from a Blade view:
//   <script type="module" src="@vite('resources/js/packing-sync/index.ts')"></script>
//   <script>
//     window.packingSync.engine.on(e => {
//       if (e.type === 'queue_change') updateBadge(e.queue.filter(a => a.status === 'pending').length);
//     });
//   </script>

import { PackingSyncEngine } from './sync-engine';
import { QueueStore } from './queue-store';

declare global {
    interface Window {
        packingSync?: {
            engine: PackingSyncEngine;
            store: QueueStore;
        };
    }
}

const store = new QueueStore();
const engine = new PackingSyncEngine(
    {
        onBreadcrumb: (category, message, data) => {
            // Hand off to Sentry if it's loaded; otherwise console.debug.
            const w = window as unknown as { Sentry?: { addBreadcrumb: (b: unknown) => void } };
            if (w.Sentry && typeof w.Sentry.addBreadcrumb === 'function') {
                w.Sentry.addBreadcrumb({ category, message, data, level: 'info' });
            } else if (typeof console !== 'undefined') {
                console.debug(`[${category}] ${message}`, data ?? {});
            }
        },
    },
    store,
);

window.packingSync = { engine, store };

// Kick off a drain on load — if a previous session left pending items, they
// resume immediately when this page is opened.
if (typeof window !== 'undefined') {
    window.addEventListener('load', () => {
        engine.drain().catch(() => {
            /* swallow — drain will retry on next event */
        });
    });
}
