'use strict';

// Migration worker for the former /public/ registration. The real Optivio
// worker now lives one level higher and owns the whole application scope.
const LEGACY_CACHE_NAMES = ['myapp'];
const LEGACY_CACHE_PREFIXES = ['optivio-public-static-'];

self.addEventListener('install', () => {
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    const keys = await caches.keys();
    await Promise.all(keys
      .filter((key) => LEGACY_CACHE_NAMES.includes(key)
        || LEGACY_CACHE_PREFIXES.some((prefix) => key.startsWith(prefix)))
      .map((key) => caches.delete(key)));
    await self.registration.unregister();
    const windows = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
    await Promise.all(windows.map((client) => client.navigate(client.url)));
  })());
});
