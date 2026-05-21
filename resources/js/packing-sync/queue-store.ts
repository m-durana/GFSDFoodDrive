// REL-07: thin IndexedDB wrapper for the packing-sync queue.
// Kept dependency-free so unit tests can run under fake-indexeddb without setup.

import type { QueuedAction, QueueStatus } from './types';

const DB_NAME = 'gfsd-packing-sync';
const DB_VERSION = 1;
const STORE_QUEUE = 'packing_queue';
const STORE_SNAPSHOT = 'packing_snapshot';

function promisify<T>(req: IDBRequest<T>): Promise<T> {
    return new Promise((resolve, reject) => {
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => reject(req.error);
    });
}

export class QueueStore {
    private dbPromise: Promise<IDBDatabase> | null = null;

    private open(): Promise<IDBDatabase> {
        if (this.dbPromise) return this.dbPromise;
        this.dbPromise = new Promise((resolve, reject) => {
            const req = indexedDB.open(DB_NAME, DB_VERSION);
            req.onupgradeneeded = () => {
                const db = req.result;
                if (!db.objectStoreNames.contains(STORE_QUEUE)) {
                    const store = db.createObjectStore(STORE_QUEUE, { keyPath: 'id', autoIncrement: true });
                    store.createIndex('by_status', 'status', { unique: false });
                    store.createIndex('by_list', 'list_id', { unique: false });
                    store.createIndex('by_key', 'key', { unique: true });
                }
                if (!db.objectStoreNames.contains(STORE_SNAPSHOT)) {
                    db.createObjectStore(STORE_SNAPSHOT, { keyPath: 'list_id' });
                }
            };
            req.onsuccess = () => resolve(req.result);
            req.onerror = () => reject(req.error);
        });
        return this.dbPromise;
    }

    async enqueue(action: QueuedAction): Promise<number> {
        const db = await this.open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_QUEUE, 'readwrite');
            const store = tx.objectStore(STORE_QUEUE);
            const req = store.add(action);
            req.onsuccess = () => resolve(req.result as number);
            req.onerror = () => reject(req.error);
        });
    }

    async update(id: number, patch: Partial<QueuedAction>): Promise<void> {
        const db = await this.open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_QUEUE, 'readwrite');
            const store = tx.objectStore(STORE_QUEUE);
            const getReq = store.get(id);
            getReq.onsuccess = () => {
                const current = getReq.result as QueuedAction | undefined;
                if (!current) {
                    resolve();
                    return;
                }
                const next = { ...current, ...patch };
                const putReq = store.put(next);
                putReq.onsuccess = () => resolve();
                putReq.onerror = () => reject(putReq.error);
            };
            getReq.onerror = () => reject(getReq.error);
        });
    }

    async findByStatus(status: QueueStatus): Promise<QueuedAction[]> {
        const db = await this.open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_QUEUE, 'readonly');
            const store = tx.objectStore(STORE_QUEUE);
            const index = store.index('by_status');
            const req = index.getAll(status);
            req.onsuccess = () => resolve(req.result as QueuedAction[]);
            req.onerror = () => reject(req.error);
        });
    }

    async findByKey(key: string): Promise<QueuedAction | null> {
        const db = await this.open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_QUEUE, 'readonly');
            const store = tx.objectStore(STORE_QUEUE);
            const index = store.index('by_key');
            const req = index.get(key);
            req.onsuccess = () => resolve((req.result as QueuedAction) ?? null);
            req.onerror = () => reject(req.error);
        });
    }

    async all(): Promise<QueuedAction[]> {
        const db = await this.open();
        return promisify(db.transaction(STORE_QUEUE).objectStore(STORE_QUEUE).getAll() as IDBRequest<QueuedAction[]>);
    }

    async delete(id: number): Promise<void> {
        const db = await this.open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_QUEUE, 'readwrite');
            const req = tx.objectStore(STORE_QUEUE).delete(id);
            req.onsuccess = () => resolve();
            req.onerror = () => reject(req.error);
        });
    }

    /** Drop synced entries older than `olderThanMs` to keep the store small. */
    async prune(olderThanMs: number): Promise<number> {
        const all = await this.all();
        const cutoff = Date.now() - olderThanMs;
        let deleted = 0;
        for (const a of all) {
            if (a.status === 'synced' && (a.synced_at ?? a.created_at) < cutoff) {
                if (a.id !== undefined) await this.delete(a.id);
                deleted++;
            }
        }
        return deleted;
    }

    async saveSnapshot(listId: number, body: unknown): Promise<void> {
        const db = await this.open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_SNAPSHOT, 'readwrite');
            const req = tx.objectStore(STORE_SNAPSHOT).put({ list_id: listId, body, updated_at: Date.now() });
            req.onsuccess = () => resolve();
            req.onerror = () => reject(req.error);
        });
    }

    async loadSnapshot(listId: number): Promise<unknown | null> {
        const db = await this.open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_SNAPSHOT, 'readonly');
            const req = tx.objectStore(STORE_SNAPSHOT).get(listId);
            req.onsuccess = () => resolve((req.result as { body: unknown } | undefined)?.body ?? null);
            req.onerror = () => reject(req.error);
        });
    }

    /** Test-only: wipe everything. */
    async _reset(): Promise<void> {
        const db = await this.open();
        await Promise.all([
            promisify(db.transaction(STORE_QUEUE, 'readwrite').objectStore(STORE_QUEUE).clear()),
            promisify(db.transaction(STORE_SNAPSHOT, 'readwrite').objectStore(STORE_SNAPSHOT).clear()),
        ]);
    }
}
