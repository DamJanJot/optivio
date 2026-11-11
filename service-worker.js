'use strict';

const APP_ID = 'optivio';
const APP_VERSION = '2026.11.11.1';
const CACHE_PREFIX = APP_ID + '-static-';
const STATIC_CACHE = CACHE_PREFIX + APP_VERSION;
const appUrl = (path) => new URL(path, self.registration.scope).href;
const OFFLINE_URL = appUrl('offline.html');
const STATIC_ASSETS = [
  "offline.html",
  "public/assets/js/pwa-runtime.js",
  "public/assets/css/pwa-runtime.css",
  "public/icons/icon-192.png",
  "public/icons/icon-512.png"
].map(appUrl);

async function precache() {
  const cache = await caches.open(STATIC_CACHE);
  await Promise.allSettled(STATIC_ASSETS.map(async (url) => {
    const response = await fetch(url, { cache: 'reload' });
    if (response.ok) await cache.put(url, response);
  }));
}

self.addEventListener('install', (event) => {
  event.waitUntil(precache());
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys()
      .then((keys) => Promise.all(keys
        .filter((key) => key.startsWith(CACHE_PREFIX) && key !== STATIC_CACHE)
        .map((key) => caches.delete(key))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('message', (event) => {
  if (event.data?.type === 'SKIP_WAITING') self.skipWaiting();
});

function requestWithVersion(request) {
  const headers = new Headers(request.headers);
  headers.set('X-App-Version', APP_VERSION);
  return new Request(request, { headers });
}

function isStaticAsset(url) {
  return /\.(?:css|js|mjs|png|jpe?g|webp|gif|svg|ico|woff2?)(?:$|\?)/i.test(url.pathname + url.search);
}

self.addEventListener('fetch', (event) => {
  const request = event.request;
  const url = new URL(request.url);
  if (url.origin !== self.location.origin) return;

  if (!['GET', 'HEAD', 'OPTIONS'].includes(request.method)) {
    event.respondWith(fetch(requestWithVersion(request)));
    return;
  }

  if (request.mode === 'navigate') {
    event.respondWith(fetch(request).catch(() => caches.match(OFFLINE_URL)));
    return;
  }

  if (url.pathname.endsWith('/pwa-status.php') || url.pathname.endsWith('/pwa-status.json')) {
    event.respondWith(fetch(request, { cache: 'no-store' }));
    return;
  }

  if (!isStaticAsset(url) || request.headers.has('Range')) return;
  event.respondWith(
    caches.match(request).then((cached) => {
      const network = fetch(request).then((response) => {
        if (response.ok && response.type === 'basic') {
          const copy = response.clone();
          caches.open(STATIC_CACHE).then((cache) => cache.put(request, copy));
        }
        return response;
      });
      return cached || network;
    })
  );
});
