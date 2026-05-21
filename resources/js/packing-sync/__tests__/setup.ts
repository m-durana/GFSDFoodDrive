// fake-indexeddb shims `indexedDB` onto `globalThis` so jsdom tests run as if
// they were in a real browser. Auto-import installs the globals.
import 'fake-indexeddb/auto';
