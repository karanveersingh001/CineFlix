/**
 * Cinflix - Service Worker
 * Offline support, caching, PWA functionality
 */

const CACHE_VERSION = 'cinflix-v1';
const STATIC_CACHE  = `${CACHE_VERSION}-static`;
const API_CACHE     = `${CACHE_VERSION}-api`;

// Assets to cache on install
const STATIC_ASSETS = [
    '/cinflix/',
    '/cinflix/index.php',
    '/cinflix/assets/css/cinflix.css',
    '/cinflix/assets/js/api.js',
    '/cinflix/assets/js/ui.js',
    '/cinflix/assets/js/player.js',
    '/cinflix/assets/js/app.js',
    '/cinflix/manifest.json',
];

// ============================================================
// INSTALL: Cache static assets
// ============================================================
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE).then(cache => {
            return cache.addAll(STATIC_ASSETS).catch(err => {
                console.warn('[SW] Failed to cache some assets:', err);
            });
        }).then(() => self.skipWaiting())
    );
});

// ============================================================
// ACTIVATE: Clean old caches
// ============================================================
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.filter(k => k.startsWith('cinflix-') && k !== STATIC_CACHE && k !== API_CACHE)
                    .map(k => caches.delete(k))
            );
        }).then(() => self.clients.claim())
    );
});

// ============================================================
// FETCH: Network-first for API, cache-first for static
// ============================================================
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET and video streams (too large to cache)
    if (request.method !== 'GET') return;
    if (url.pathname.includes('/Videos/') || url.pathname.includes('/stream')) return;

    // Jellyfin image requests: cache-first, 7 day expiry
    if (url.hostname.includes('karan.ptgn.in') && url.pathname.includes('/Images/')) {
        event.respondWith(cacheFirstWithExpiry(request, STATIC_CACHE, 7 * 24 * 60 * 60));
        return;
    }

    // API calls: network-first with offline fallback
    if (url.pathname.includes('/cinflix/api/')) {
        event.respondWith(networkFirstWithFallback(request));
        return;
    }

    // Static assets: cache-first
    if (
        url.pathname.includes('/cinflix/assets/') ||
        url.pathname.includes('/cinflix/manifest.json')
    ) {
        event.respondWith(cacheFirstWithFallback(request));
        return;
    }

    // PHP pages: network-first
    event.respondWith(networkFirstWithFallback(request));
});

// ---- Strategy: Network first, fallback to cache ----
async function networkFirstWithFallback(request) {
    try {
        const networkRes = await fetch(request);
        if (networkRes.ok) {
            const cache = await caches.open(API_CACHE);
            cache.put(request, networkRes.clone());
        }
        return networkRes;
    } catch {
        const cached = await caches.match(request);
        if (cached) return cached;
        // Return offline page for navigation requests
        if (request.mode === 'navigate') {
            return new Response(offlinePage(), { headers: { 'Content-Type': 'text/html' } });
        }
        return new Response(JSON.stringify({ error: 'Offline', offline: true }), {
            headers: { 'Content-Type': 'application/json' },
            status: 503,
        });
    }
}

// ---- Strategy: Cache first, fallback to network ----
async function cacheFirstWithFallback(request) {
    const cached = await caches.match(request);
    if (cached) return cached;
    try {
        const networkRes = await fetch(request);
        if (networkRes.ok) {
            const cache = await caches.open(STATIC_CACHE);
            cache.put(request, networkRes.clone());
        }
        return networkRes;
    } catch {
        return new Response('Offline', { status: 503 });
    }
}

// ---- Strategy: Cache first with expiry ----
async function cacheFirstWithExpiry(request, cacheName, maxAgeSeconds) {
    const cache  = await caches.open(cacheName);
    const cached = await cache.match(request);

    if (cached) {
        const dateHeader = cached.headers.get('date');
        if (dateHeader) {
            const age = (Date.now() - new Date(dateHeader).getTime()) / 1000;
            if (age < maxAgeSeconds) return cached;
        } else {
            return cached;
        }
    }

    try {
        const networkRes = await fetch(request);
        if (networkRes.ok) cache.put(request, networkRes.clone());
        return networkRes;
    } catch {
        return cached || new Response('', { status: 503 });
    }
}

// ---- Offline HTML page ----
function offlinePage() {
    return `<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Cinflix – Offline</title>
<style>
  body { background:#03030a; color:#e5e7eb; font-family:'DM Sans',sans-serif; display:flex; align-items:center; justify-content:center; min-height:100vh; text-align:center; }
  h1 { font-size:2rem; font-weight:700; margin-bottom:.5rem; }
  p  { color:#6b7280; margin-bottom:1.5rem; }
  button { padding:.75rem 2rem; background:#e80000; color:#fff; border:none; border-radius:.75rem; font-size:1rem; cursor:pointer; }
</style>
</head>
<body>
  <div>
    <p style="font-size:4rem">📡</p>
    <h1>You're Offline</h1>
    <p>Connect to the internet to continue watching.</p>
    <button onclick="location.reload()">Try Again</button>
  </div>
</body>
</html>`;
}

// ============================================================
// BACKGROUND SYNC (for analytics / playback progress)
// ============================================================
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-progress') {
        event.waitUntil(syncProgress());
    }
});

async function syncProgress() {
    // Flush any pending progress reports stored in IndexedDB
    // (extend here for offline-to-online sync)
}
