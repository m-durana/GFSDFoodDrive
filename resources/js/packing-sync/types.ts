// REL-07: shared types for the offline packing-scanner sync engine.

export type QueueStatus = 'pending' | 'in_flight' | 'synced' | 'conflict' | 'dropped';

export type QueuedAction = {
    id?: number;
    key: string;
    list_id: number;
    endpoint: string;
    method: 'POST';
    payload: Record<string, unknown>;
    status: QueueStatus;
    attempts: number;
    last_error: string | null;
    created_at: number;
    synced_at: number | null;
};

export type ConflictPayload = {
    code: 'conflict' | string;
    reason?: string;
    message: string;
    suggested_action?: {
        type: string;
        endpoint: string;
        payload?: Record<string, unknown>;
    };
};

export type SyncEvent =
    | { type: 'queue_change'; queue: QueuedAction[] }
    | { type: 'online_change'; online: boolean }
    | { type: 'conflict'; action: QueuedAction; conflict: ConflictPayload }
    | { type: 'sync_complete' }
    | { type: 'dropped'; action: QueuedAction };

export type SyncEngineConfig = {
    /** Drain one action at a time per Decision #2 in REL-07-PLAN. */
    maxConcurrent: number;
    /** Max attempts before a queue entry transitions to `dropped`. */
    maxAttempts: number;
    /** Exponential backoff base (ms). */
    backoffBaseMs: number;
    /** Cap on backoff (ms). */
    backoffMaxMs: number;
    /** ms after which synced entries are pruned from IndexedDB. */
    syncedRetentionMs: number;
    /** Optional Sentry-style breadcrumb hook for observability. */
    onBreadcrumb?: (category: string, message: string, data?: Record<string, unknown>) => void;
    /** Override the fetch implementation (tests inject a fake). */
    fetchImpl?: typeof fetch;
    /** Override `navigator.onLine` reads (tests inject). */
    onlineProbe?: () => boolean;
};

export const DEFAULT_CONFIG: SyncEngineConfig = {
    maxConcurrent: 1,
    maxAttempts: 10,
    backoffBaseMs: 1000,
    backoffMaxMs: 30_000,
    syncedRetentionMs: 60 * 60 * 1000, // 1h, per Decision #1
};
