// ==========================
// CONFIG
// ==========================
const CACHE_VERSION = "v1";
const STATIC_CACHE = "static-" + CACHE_VERSION;
const DYNAMIC_CACHE = "dynamic-" + CACHE_VERSION;

// Files to cache immediately
const STATIC_ASSETS = [
    
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

    // ONLY handle http and https schemes (ignores chrome-extension, data, etc.)
    if (!url.protocol.startsWith("http")) return;

    // Ignore non-GET requests (important for Laravel forms, POST APIs)
    if (request.method !== "GET") return;

    // Bypassing cache for specific routes that need fresh sessions/CSRF
    if (
        url.pathname.startsWith("/admin") ||
        url.pathname.startsWith("/login") ||
        url.pathname.startsWith("/register") ||
        url.pathname.startsWith("/logout") ||
        url.pathname.startsWith("/password") ||
        url.pathname.startsWith("/api")
    ) {
        return;
    }

    // Detect if request is for an HTML page
    const isHtml = request.headers.get("accept") && request.headers.get("accept").includes("text/html");

    if (isHtml) {
        // NETWORK FIRST strategy for HTML pages
        event.respondWith(
            fetch(request)
                .then(networkResponse => {
                    // Only cache successful GET responses that are not redirects and don't have sensitive headers
                    if (networkResponse && networkResponse.ok && networkResponse.status === 200) {
                        const cacheControl = networkResponse.headers.get("Cache-Control");
                        const hasSetCookie = networkResponse.headers.has("Set-Cookie");
                        
                        // Don't cache if no-store is present or if it sets cookies
                        if (!hasSetCookie && (!cacheControl || !cacheControl.includes("no-store"))) {
                            const responseClone = networkResponse.clone();
                            caches.open(DYNAMIC_CACHE).then(cache => {
                                cache.put(request, responseClone).catch(err => console.warn("Cache put failed:", err));
                            });
                        }
                    }
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
                    if (networkResponse && networkResponse.ok) {
                        const responseClone = networkResponse.clone();
                        caches.open(DYNAMIC_CACHE).then(cache => {
                            cache.put(request, responseClone).catch(err => console.warn("Cache put failed:", err));
                        });
                    }
                    return networkResponse;
                }).catch(err => {
                    // Return a 404 response if both network and cache fail for an asset
                    return new Response("Asset not found", { status: 404, statusText: "Not Found" });
                });
            })
        );
    }
});

// ==========================
// MESSAGE EVENT (Handle Logout)
// ==========================
self.addEventListener("message", event => {
    if (event.data && event.data.type === "LOGOUT") {
        console.log("Service Worker: Clearing Dynamic Cache...");
        event.waitUntil(
            caches.delete(DYNAMIC_CACHE).then(() => {
                console.log("Service Worker: Dynamic Cache Cleared.");
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