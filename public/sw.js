const CACHE_NAME = 'erp-pwa-v1';
const STATIC_ASSETS = [
    '/',
    '/manifest.json',
    '/css/login.css',
    '/js/global-loader.js'
];

// Install Event
self.addEventListener('install', (event) => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS).catch((err) => {
                console.log('PWA initial caching skipped non-critical files:', err);
            });
        })
    );
});

// Activate Event
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((name) => {
                    if (name !== CACHE_NAME) {
                        return caches.delete(name);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch Event - Network First with Cache Fallback for ERP data freshness
self.addEventListener('fetch', (event) => {
    // Only handle GET requests, ignore POST/PUT/DELETE
    if (event.request.method !== 'GET') {
        return;
    }

    // Skip Chrome extension requests or non-HTTP(S)
    if (!event.request.url.startsWith('http')) {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((networkResponse) => {
                // If valid response, update cache in background for offline use
                if (networkResponse && networkResponse.status === 200 && networkResponse.type === 'basic') {
                    const responseClone = networkResponse.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseClone);
                    });
                }
                return networkResponse;
            })
            .catch(() => {
                // Offline fallback from cache
                return caches.match(event.request);
            })
    );
});
