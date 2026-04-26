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

    // Ignore non-GET requests (important for Laravel forms, POST APIs)
    if (event.request.method !== "GET") return;

    // Ignore admin panel if needed (optional)
    if (event.request.url.includes("/admin")) return;

    event.respondWith(
        caches.match(event.request).then(cachedResponse => {

            // Return cached if exists
            if (cachedResponse) {
                return cachedResponse;
            }

            // Else fetch from network
            return fetch(event.request)
                .then(networkResponse => {

                    // Clone response
                    const responseClone = networkResponse.clone();

                    // Store in dynamic cache
                    caches.open(DYNAMIC_CACHE).then(cache => {
                        cache.put(event.request, responseClone);
                    });

                    return networkResponse;
                })
                .catch(() => {

                    // If HTML page → show offline page
                    if (event.request.headers.get("accept").includes("text/html")) {
                        return caches.match("/offline.html");
                    }
                });
        })
    );
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