import { defineConfig } from 'vitest/config';

export default defineConfig({
    test: {
        environment: 'jsdom',
        globals: false,
        include: ['resources/js/**/__tests__/**/*.test.ts'],
        setupFiles: ['resources/js/packing-sync/__tests__/setup.ts'],
        // Forks pool hangs on Windows under vitest 4.x; threads runs cleanly.
        pool: 'threads',
        // Vitest 4 hoists pool-specific config to the top level. Single-thread
        // keeps fake-indexeddb state predictable across tests in the same file.
        minWorkers: 1,
        maxWorkers: 1,
    },
});
