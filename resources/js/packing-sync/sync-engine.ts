// REL-07: offline packing-scanner sync engine.
//
// Lifecycle:
//   1. UI calls engine.enqueue({ list_id, endpoint, payload }).
//   2. Engine writes to IndexedDB + applies optimistic UI patch (caller's responsibility).
//   3. Background drain loop posts oldest pending → marks synced / conflict / dropped.
//   4. After every successful drain, caller can reconcile (GET /show) to overwrite local mirror.
//
// Pure-TS module — no Alpine, no DOM. Composed into the scanner via window.PackingSync.

import { QueueStore } from './queue-store';
import {
    DEFAULT_CONFIG,
    type ConflictPayload,
    type QueuedAction,
    type SyncEngineConfig,
    type SyncEvent,
} from './types';

type Listener = (event: SyncEvent) => void;

/** UUIDv4 — works in Node 19+ (crypto.randomUUID) and modern browsers. */
function uuid(): string {
    if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
        return crypto.randomUUID();
    }
    // Fallback for older environments (test only).
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
        const r = (Math.random() * 16) | 0;
        return (c === 'x' ? r : (r & 0x3) | 0x8).toString(16);
    });
}

export class PackingSyncEngine {
    private store: QueueStore;
    private listeners = new Set<Listener>();
    private cfg: SyncEngineConfig;
    private drainPromise: Promise<void> | null = null;
    private wakeTimer: ReturnType<typeof setTimeout> | null = null;
    private stopped = false;

    constructor(cfg: Partial<SyncEngineConfig> = {}, store?: QueueStore) {
        this.cfg = { ...DEFAULT_CONFIG, ...cfg };
        this.store = store ?? new QueueStore();
        if (typeof globalThis.addEventListener === 'function') {
            globalThis.addEventListener('online', () => {
                this.emit({ type: 'online_change', online: true });
                this.scheduleDrain(0);
            });
            globalThis.addEventListener('offline', () => {
                this.emit({ type: 'online_change', online: false });
            });
        }
    }

    /** Subscribe to engine events (queue change, conflict, sync_complete). */
    on(fn: Listener): () => void {
        this.listeners.add(fn);
        return () => this.listeners.delete(fn);
    }

    private emit(event: SyncEvent): void {
        for (const fn of this.listeners) {
            try {
                fn(event);
            } catch (_) {
                // listener errors must not break the engine
            }
        }
    }

    private breadcrumb(category: string, message: string, data?: Record<string, unknown>): void {
        this.cfg.onBreadcrumb?.(category, message, data);
    }

    private isOnline(): boolean {
        if (this.cfg.onlineProbe) return this.cfg.onlineProbe();
        if (typeof navigator !== 'undefined' && typeof navigator.onLine === 'boolean') {
            return navigator.onLine;
        }
        return true;
    }

    private fetchFn(): typeof fetch {
        return this.cfg.fetchImpl ?? globalThis.fetch.bind(globalThis);
    }

    /** Queue an action. Returns the persisted row id. */
    async enqueue(input: {
        list_id: number;
        endpoint: string;
        payload?: Record<string, unknown>;
        key?: string;
    }): Promise<QueuedAction> {
        const action: QueuedAction = {
            key: input.key ?? uuid(),
            list_id: input.list_id,
            endpoint: input.endpoint,
            method: 'POST',
            payload: input.payload ?? {},
            status: 'pending',
            attempts: 0,
            last_error: null,
            created_at: Date.now(),
            synced_at: null,
        };
        const id = await this.store.enqueue(action);
        action.id = id;
        await this.emitQueueChange();
        this.breadcrumb('packing-sync', 'enqueue', { key: action.key, endpoint: action.endpoint });
        this.scheduleDrain(0);
        return action;
    }

    async getQueue(): Promise<QueuedAction[]> {
        return this.store.all();
    }

    async pending(): Promise<QueuedAction[]> {
        return this.store.findByStatus('pending');
    }

    /** Force a drain attempt now. Concurrent callers share the in-flight promise. */
    async drain(): Promise<void> {
        if (this.stopped) return;
        if (this.drainPromise) return this.drainPromise;
        if (!this.isOnline()) return;
        this.drainPromise = this.drainLoop().finally(() => {
            this.drainPromise = null;
        });
        return this.drainPromise;
    }

    private async drainLoop(): Promise<void> {
        // One at a time per Decision #2.
        // eslint-disable-next-line no-constant-condition
        while (true) {
            if (this.stopped) break;
            const pending = await this.store.findByStatus('pending');
            if (pending.length === 0) break;
            pending.sort((a, b) => a.created_at - b.created_at);
            const next = pending[0];
            const cont = await this.attemptOne(next);
            if (!cont) break;
        }
        await this.emitQueueChange();
        this.emit({ type: 'sync_complete' });
    }

    /** Returns true if the loop should continue, false if it should pause. */
    private async attemptOne(action: QueuedAction): Promise<boolean> {
        if (action.id === undefined) return false;
        await this.store.update(action.id, { status: 'in_flight' });

        let resp: Response;
        try {
            resp = await this.fetchFn()(action.endpoint, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-Idempotency-Key': action.key,
                },
                body: JSON.stringify(action.payload),
                credentials: 'same-origin',
            });
        } catch (e) {
            // Network error — retry with backoff.
            return this.handleRetryable(action, (e as Error)?.message ?? 'network_error');
        }

        if (resp.status >= 200 && resp.status < 300) {
            await this.store.update(action.id, {
                status: 'synced',
                synced_at: Date.now(),
                last_error: null,
            });
            this.breadcrumb('packing-sync', 'synced', { key: action.key });
            return true;
        }

        if (resp.status === 409) {
            const conflict = (await this.safeJson(resp)) as ConflictPayload | null;
            await this.store.update(action.id, {
                status: 'conflict',
                last_error: conflict?.message ?? 'conflict',
            });
            const refreshed = (await this.store.findByKey(action.key)) ?? action;
            this.emit({
                type: 'conflict',
                action: refreshed,
                conflict: conflict ?? { code: 'conflict', message: 'Conflict (no body)' },
            });
            this.breadcrumb('packing-sync', 'conflict', { key: action.key });
            return true;
        }

        if (resp.status >= 400 && resp.status < 500) {
            // 4xx that isn't a conflict — deterministic rejection, drop.
            await this.store.update(action.id, {
                status: 'dropped',
                last_error: `HTTP ${resp.status}`,
            });
            const refreshed = (await this.store.findByKey(action.key)) ?? action;
            this.emit({ type: 'dropped', action: refreshed });
            this.breadcrumb('packing-sync', 'dropped', { key: action.key, status: resp.status });
            return true;
        }

        // 5xx — server flap, retry with backoff.
        return this.handleRetryable(action, `HTTP ${resp.status}`);
    }

    private async handleRetryable(action: QueuedAction, reason: string): Promise<boolean> {
        if (action.id === undefined) return false;
        const attempts = action.attempts + 1;
        if (attempts >= this.cfg.maxAttempts) {
            await this.store.update(action.id, {
                status: 'dropped',
                attempts,
                last_error: `max_attempts: ${reason}`,
            });
            const refreshed = (await this.store.findByKey(action.key)) ?? action;
            this.emit({ type: 'dropped', action: refreshed });
            this.breadcrumb('packing-sync', 'dropped_max_attempts', { key: action.key });
            return false;
        }
        await this.store.update(action.id, {
            status: 'pending',
            attempts,
            last_error: reason,
        });
        const delay = Math.min(this.cfg.backoffBaseMs * 2 ** (attempts - 1), this.cfg.backoffMaxMs);
        this.scheduleDrain(delay);
        return false;
    }

    private scheduleDrain(delayMs: number): void {
        if (this.wakeTimer) clearTimeout(this.wakeTimer);
        this.wakeTimer = setTimeout(() => {
            this.drain().catch(() => {
                /* swallow — next event will re-trigger */
            });
        }, delayMs);
    }

    private async emitQueueChange(): Promise<void> {
        const queue = await this.store.all();
        this.emit({ type: 'queue_change', queue });
    }

    private async safeJson(resp: Response): Promise<unknown | null> {
        try {
            return await resp.json();
        } catch {
            return null;
        }
    }

    /** Test helper: stop the drain loop and any pending timers. */
    stop(): void {
        this.stopped = true;
        if (this.wakeTimer) {
            clearTimeout(this.wakeTimer);
            this.wakeTimer = null;
        }
    }
}
