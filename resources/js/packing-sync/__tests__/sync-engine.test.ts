// REL-07 / P3 — 10 scenarios from REL-07-PLAN.md §5.2.
//
// All tests share a `harness` that:
//   • Provides a programmatic `online` toggle (via the onlineProbe config).
//   • Stubs `fetch` with a programmable script (status + body per call).
//   • Wraps the engine with a fresh QueueStore (fake-indexeddb resets per file).

import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { QueueStore } from '../queue-store';
import { PackingSyncEngine } from '../sync-engine';

type FakeFetchCall = {
    url: string;
    init?: RequestInit;
};

function makeHarness(opts: {
    responses?: Array<{ status: number; body?: unknown }>;
    online?: boolean;
    networkError?: number; // throw network error on calls 0..N-1
} = {}) {
    const responses = [...(opts.responses ?? [])];
    let calls: FakeFetchCall[] = [];
    let online = opts.online ?? true;
    let networkErrorBudget = opts.networkError ?? 0;

    const fakeFetch: typeof fetch = vi.fn(async (input, init) => {
        const url = typeof input === 'string' ? input : (input as URL).toString();
        calls.push({ url, init });
        if (networkErrorBudget > 0) {
            networkErrorBudget--;
            throw new Error('network_error');
        }
        const next = responses.shift() ?? { status: 200, body: { success: true } };
        return new Response(JSON.stringify(next.body ?? {}), {
            status: next.status,
            headers: { 'Content-Type': 'application/json' },
        });
    }) as unknown as typeof fetch;

    const store = new QueueStore();
    const breadcrumbs: Array<{ category: string; message: string; data?: Record<string, unknown> }> = [];
    const engine = new PackingSyncEngine(
        {
            fetchImpl: fakeFetch,
            onlineProbe: () => online,
            backoffBaseMs: 5, // keep tests fast
            backoffMaxMs: 20,
            maxAttempts: 3,
            onBreadcrumb: (category, message, data) => breadcrumbs.push({ category, message, data }),
        },
        store,
    );

    return {
        engine,
        store,
        calls,
        breadcrumbs,
        setOnline: (v: boolean) => {
            online = v;
        },
    };
}

afterEach(async () => {
    // fake-indexeddb keeps state across tests in the same file — wipe between tests.
    const store = new QueueStore();
    await store._reset();
});

describe('PackingSyncEngine', () => {
    it('#1 enqueue then drain in order', async () => {
        const h = makeHarness({
            responses: [
                { status: 200, body: { ok: 1 } },
                { status: 200, body: { ok: 2 } },
                { status: 200, body: { ok: 3 } },
            ],
        });
        await h.engine.enqueue({ list_id: 1, endpoint: '/api/packing/1/item/1/pack' });
        await h.engine.enqueue({ list_id: 1, endpoint: '/api/packing/1/item/2/pack' });
        await h.engine.enqueue({ list_id: 1, endpoint: '/api/packing/1/item/3/pack' });
        await h.engine.drain();
        expect(h.calls.map((c) => c.url)).toEqual([
            '/api/packing/1/item/1/pack',
            '/api/packing/1/item/2/pack',
            '/api/packing/1/item/3/pack',
        ]);
        const synced = await h.store.findByStatus('synced');
        expect(synced).toHaveLength(3);
    });

    it('#2 enqueue while offline → queue_change fires, no fetch yet', async () => {
        const events: string[] = [];
        const h = makeHarness({ online: false });
        h.engine.on((e) => events.push(e.type));
        await h.engine.enqueue({ list_id: 1, endpoint: '/api/packing/1/item/1/pack' });
        expect(events).toContain('queue_change');
        expect(h.calls).toHaveLength(0);
        expect((await h.store.findByStatus('pending'))).toHaveLength(1);
    });

    it('#3 server returns 200 → entry moves to synced', async () => {
        const h = makeHarness({ responses: [{ status: 200 }] });
        await h.engine.enqueue({ list_id: 1, endpoint: '/api/packing/1/item/1/pack' });
        await h.engine.drain();
        const all = await h.store.all();
        expect(all[0].status).toBe('synced');
        expect(all[0].synced_at).toBeTypeOf('number');
    });

    it('#4 server returns 409 → conflict event fires + status=conflict', async () => {
        const h = makeHarness({
            responses: [{ status: 409, body: { code: 'conflict', message: 'Item substituted', reason: 'sub' } }],
        });
        const events: unknown[] = [];
        h.engine.on((e) => {
            if (e.type === 'conflict') events.push(e);
        });
        await h.engine.enqueue({ list_id: 1, endpoint: '/api/packing/1/item/5/pack' });
        await h.engine.drain();
        expect(events).toHaveLength(1);
        const all = await h.store.all();
        expect(all[0].status).toBe('conflict');
        expect(all[0].last_error).toBe('Item substituted');
    });

    it('#5 network error → retries with exponential backoff, attempts increments', async () => {
        const h = makeHarness({ networkError: 2, responses: [{ status: 200 }] });
        await h.engine.enqueue({ list_id: 1, endpoint: '/api/packing/1/item/1/pack' });
        // Three drain cycles: fail, fail, succeed.
        await h.engine.drain();
        await new Promise((r) => setTimeout(r, 20));
        await h.engine.drain();
        await new Promise((r) => setTimeout(r, 20));
        await h.engine.drain();

        const all = await h.store.all();
        // Final state is synced; attempts counted only for the failures.
        expect(all[0].status).toBe('synced');
        expect(all[0].attempts).toBe(2);
    });

    it('#6 after maxAttempts failures → dropped + breadcrumb emitted', async () => {
        const h = makeHarness({ networkError: 10 });
        await h.engine.enqueue({ list_id: 1, endpoint: '/api/packing/1/item/1/pack' });
        for (let i = 0; i < 5; i++) {
            await h.engine.drain();
            await new Promise((r) => setTimeout(r, 25));
        }
        const all = await h.store.all();
        expect(all[0].status).toBe('dropped');
        expect(h.breadcrumbs.some((b) => b.message.startsWith('dropped'))).toBe(true);
    });

    it('#7 reload-style: a fresh engine instance picks up queued work from IDB', async () => {
        const h1 = makeHarness({ online: false });
        await h1.engine.enqueue({ list_id: 1, endpoint: '/api/packing/1/item/1/pack' });
        h1.engine.stop();
        // New engine on same store backend → finds the pending row + drains.
        const h2 = makeHarness({ responses: [{ status: 200 }] });
        await h2.engine.drain();
        const all = await h2.store.all();
        expect(all.find((a) => a.endpoint === '/api/packing/1/item/1/pack')?.status).toBe('synced');
    });

    it('#8 each enqueue gets a unique idempotency key', async () => {
        const h = makeHarness({ online: false });
        await h.engine.enqueue({ list_id: 1, endpoint: '/api/packing/1/item/7/pack' });
        await h.engine.enqueue({ list_id: 1, endpoint: '/api/packing/1/item/7/pack' });
        const all = await h.store.all();
        const keys = new Set(all.map((a) => a.key));
        expect(keys.size).toBe(2);
    });

    it('#9 snapshot save + load round-trips', async () => {
        const h = makeHarness();
        await h.store.saveSnapshot(42, { items: [{ id: 1, packed: true }] });
        const loaded = await h.store.loadSnapshot(42);
        expect(loaded).toEqual({ items: [{ id: 1, packed: true }] });
    });

    it('#10 going offline mid-drain stops further fetches', async () => {
        const h = makeHarness({
            responses: [{ status: 200 }, { status: 200 }, { status: 200 }],
        });
        await h.engine.enqueue({ list_id: 1, endpoint: '/api/packing/1/item/1/pack' });
        await h.engine.enqueue({ list_id: 1, endpoint: '/api/packing/1/item/2/pack' });
        // Flip offline immediately — drain should bail out at the top of the loop.
        h.setOnline(false);
        await h.engine.drain();
        expect(h.calls).toHaveLength(0);
        expect((await h.store.findByStatus('pending')).length).toBe(2);
    });
});
