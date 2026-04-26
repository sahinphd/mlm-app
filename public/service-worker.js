// ==========================
// CONFIG
// ==========================
const CACHE_VERSION = "v2_no_cache";
const STATIC_CACHE = "static-" + CACHE_VERSION;
const DYNAMIC_CACHE = "dynamic-" + CACHE_VERSION;

// Empty assets to stop caching
const STATIC_ASSETS = [];

// ==========================
// INSTALL EVENT
// ==========================
self.addEventListener("install", event => {
    console.log("Service Worker: Install (Caching Disabled)");
    self.skipWaiting();
});

// ==========================
// ACTIVATE EVENT
// ==========================
self.addEventListener("activate", event => {
    console.log("Service Worker: Activating and Clearing All Caches...");
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.map(key => {
                    console.log("Service Worker: Deleting Cache:", key);
                    return caches.delete(key);
                })
            );
        })
    );
    self.clients.claim();
});

// ==========================
// FETCH EVENT (REMOVED CACHING)
// ==========================
// Caching is completely disabled. Browser will handle requests normally via network.

// ==========================
// MESSAGE EVENT (Handle Logout)
// ==========================
self.addEventListener("message", event => {
    if (event.data && event.data.type === "LOGOUT") {
        event.waitUntil(
            caches.keys().then(keys => {
                return Promise.all(keys.map(key => caches.delete(key)));
            })
        );
    }
});

// ==========================
// BACKGROUND SYNC (Optional future)
// ==========================
self.addEventListener("sync", event => {
    // Keep for potential future use
});

// ==========================
// PUSH NOTIFICATIONS
// ==========================
self.addEventListener("push", event => {
    const data = event.data ? event.data.json() : {};
    const title = data.title || "MLM App Notification";
    const options = {
        body: data.body || "You have a new update",
        icon: "/logo.png",
        badge: "/logo.png"
    };
    event.waitUntil(self.registration.showNotification(title, options));
});

// ==========================
// NOTIFICATION CLICK
// ==========================
self.addEventListener("notificationclick", event => {
    event.notification.close();
    event.waitUntil(clients.openWindow("/"));
});
