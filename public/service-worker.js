// ==========================
// CONFIG
// ==========================
const CACHE_VERSION = "v1";
const STATIC_CACHE = "static-" + CACHE_VERSION;
const DYNAMIC_CACHE = "dynamic-" + CACHE_VERSION;

// Files to cache immediately
const STATIC_ASSETS = [
    "/",
    "/offline.html",
    "/manifest.json",
];

// ==========================
// INSTALL EVENT
// ==========================
self.addEventListener("install", event => {
    console.log("Service Worker Installing...");

    event.waitUntil(
        caches.open(STATIC_CACHE).then(cache => {
            return cache.addAll(STATIC_ASSETS);
        })
    );

    self.skipWaiting();
});

// ==========================
// ACTIVATE EVENT
// ==========================
self.addEventListener("activate", event => {
    console.log("Service Worker Activated...");

    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.map(key => {
                    if (key !== STATIC_CACHE && key !== DYNAMIC_CACHE) {
                        console.log("Deleting old cache:", key);
                        return caches.delete(key);
                    }
                })
            );
        })
    );

    self.clients.claim();
});

// ==========================
// FETCH EVENT
// ==========================
self.addEventListener("fetch", event => {
    const request = event.request;
    const url = new URL(request.url);

    // Ignore non-GET requests (important for Laravel forms, POST APIs)
    if (request.method !== "GET") return;

    // Ignore admin panel
    if (url.pathname.startsWith("/admin")) return;

    // Detect if request is for an HTML page
    const isHtml = request.headers.get("accept") && request.headers.get("accept").includes("text/html");

    if (isHtml) {
        // NETWORK FIRST strategy for HTML pages
        // This ensures cookies (sessions) and CSRF tokens are always fresh.
        event.respondWith(
            fetch(request)
                .then(networkResponse => {
                    const responseClone = networkResponse.clone();
                    caches.open(DYNAMIC_CACHE).then(cache => {
                        cache.put(request, responseClone);
                    });
                    return networkResponse;
                })
                .catch(() => {
                    // If network fails, try cache
                    return caches.match(request).then(cachedResponse => {
                        return cachedResponse || caches.match("/offline.html");
                    });
                })
        );
    } else {
        // CACHE FIRST strategy for assets (CSS, JS, Images)
        event.respondWith(
            caches.match(request).then(cachedResponse => {
                if (cachedResponse) {
                    return cachedResponse;
                }

                return fetch(request).then(networkResponse => {
                    const responseClone = networkResponse.clone();
                    caches.open(DYNAMIC_CACHE).then(cache => {
                        cache.put(request, responseClone);
                    });
                    return networkResponse;
                });
            })
        );
    }
});

// ==========================
// BACKGROUND SYNC (Optional future)
// ==========================
self.addEventListener("sync", event => {
    if (event.tag === "sync-data") {
        console.log("Background sync triggered");
        // You can sync MLM transactions here later
    }
});

// ==========================
// PUSH NOTIFICATIONS (Optional)
// ==========================
self.addEventListener("push", event => {
    const data = event.data ? event.data.json() : {};

    const title = data.title || "MLM App Notification";
    const options = {
        body: data.body || "You have a new update",
        icon: "/icons/icon-192.png",
        badge: "/icons/icon-192.png"
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// ==========================
// NOTIFICATION CLICK
// ==========================
self.addEventListener("notificationclick", event => {
    event.notification.close();

    event.waitUntil(
        clients.openWindow("/")
    );
});