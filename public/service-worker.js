// ==========================
// CONFIG
// ==========================
const CACHE_VERSION = "v3_force_network";
const STATIC_CACHE = "static-" + CACHE_VERSION;
const DYNAMIC_CACHE = "dynamic-" + CACHE_VERSION;

// ==========================
// INSTALL EVENT
// ==========================
self.addEventListener("install", event => {
    console.log("Service Worker: Force Uninstall/Network Only");
    self.skipWaiting();
});

// ==========================
// ACTIVATE EVENT
// ==========================
self.addEventListener("activate", event => {
    console.log("Service Worker: Clearing All Caches and Unregistering...");
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.map(key => caches.delete(key))
            );
        }).then(() => {
            // Unregister itself
            return self.registration.unregister();
        }).then(() => {
            return self.clients.matchAll();
        }).then(clients => {
            clients.forEach(client => client.navigate(client.url));
        })
    );
});

// No fetch listener = browser handles everything normally via network
