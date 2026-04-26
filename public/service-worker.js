// ==========================
// CONFIG
// ==========================
const CACHE_VERSION = "v4_no_cache_stable";

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
// FETCH EVENT
// ==========================
// NO FETCH LISTENER: Browser handles all requests normally via the network.
// This ensures that /login, /admin, and all other pages are NEVER cached by the SW.

// ==========================
// MESSAGE EVENT (Handle Logout)
// ==========================
self.addEventListener("message", event => {
    if (event.data && event.data.type === "LOGOUT") {
        console.log("Service Worker: Clearing all caches on logout...");
        event.waitUntil(
            caches.keys().then(keys => {
                return Promise.all(keys.map(key => caches.delete(key)));
            })
        );
    }
});

// ==========================
// PUSH NOTIFICATIONS
// ==========================
self.addEventListener("push", event => {
    let data = {};
    try {
        data = event.data ? event.data.json() : {};
    } catch (e) {
        data = { title: "New Notification", body: event.data.text() };
    }

    const title = data.title || "MLM App Notification";
    const options = {
        body: data.body || "You have a new update",
        icon: "/logo.png",
        badge: "/logo.png",
        vibrate: [100, 50, 100],
        data: {
            url: data.url || "/"
        }
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

// ==========================
// NOTIFICATION CLICK
// ==========================
self.addEventListener("notificationclick", event => {
    event.notification.close();
    const urlToOpen = event.notification.data.url || "/";

    event.waitUntil(
        clients.matchAll({ type: "window", includeUncontrolled: true }).then(windowClients => {
            for (let i = 0; i < windowClients.length; i++) {
                const client = windowClients[i];
                if (client.url === urlToOpen && "focus" in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(urlToOpen);
            }
        })
    );
});
