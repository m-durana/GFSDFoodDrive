// REL-08: Minimal install-shell service worker.
// Caches the install shell (homepage HTML + built JS/CSS) so the app can
// open offline well enough to render the login/landing page. Real offline
// data sync (warehouse scanner) is REL-07 and lives outside this worker.

const CACHE = 'gfsd-shell-v1';
const SHELL = [
  '/',
  '/manifest.webmanifest',
  '/images/logo-default.png',
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE).then((c) => c.addAll(SHELL)).catch(() => {})
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)))
    )
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  const url = new URL(event.request.url);
  // Only handle same-origin GETs; bypass POST/PUT/etc so CSRF + auth work normally.
  if (event.request.method !== 'GET' || url.origin !== self.location.origin) return;

  // Cache-first for hashed Vite assets (immutable), network-first otherwise.
  if (url.pathname.startsWith('/build/assets/')) {
    event.respondWith(
      caches.match(event.request).then((hit) => hit || fetch(event.request).then((res) => {
        const copy = res.clone();
        caches.open(CACHE).then((c) => c.put(event.request, copy));
        return res;
      }))
    );
    return;
  }

  // Network-first for everything else; fall back to cache when offline.
  event.respondWith(
    fetch(event.request).catch(() => caches.match(event.request).then((hit) => hit || caches.match('/')))
  );
});
