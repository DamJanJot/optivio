self.addEventListener('install', (e) => {
  e.waitUntil(caches.open('myapp').then((c) => c.addAll(['/index.php'])));
});
self.addEventListener('fetch', (e) => {
  e.respondWith(caches.match(e.request).then((r) => r || fetch(e.request)));
});