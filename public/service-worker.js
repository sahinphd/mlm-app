// ==========================
// VERSION
// ==========================
const SW_VERSION = "v4_stable_no_auth_cache";

// ==========================
// INSTALL
// ==========================
self.addEventListener("install", event => {
    console.log("SW Installed:", SW_VERSION);
    self.skipWaiting();
});

// ==========================
// ACTIVATE
// ==========================
self.addEventListener("activate", event => {
    console.log("SW Activated:", SW_VERSION);

    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.map(key => caches.delete(key)))
        )
    );

    self.clients.claim();
});

// ==========================
// FETCH (SAFE MODE)
// ==========================
// 🔥 IMPORTANT: NO caching → prevents Laravel 419 error
self.addEventListener("fetch", event => {

    const url = new URL(event.request.url);

    // ❌ DO NOT TOUCH AUTH / SESSION / API
    if (
        event.request.method !== "GET" ||
        url.pathname.startsWith("/login") ||
        url.pathname.startsWith("/logout") ||
        url.pathname.startsWith("/register") ||
        url.pathname.startsWith("/password") ||
        url.pathname.startsWith("/sanctum") ||
        url.pathname.startsWith("/api") ||
        url.pathname.startsWith("/dashboard") ||
        url.pathname.startsWith("/user")
    ) {
        return; // browser handles it
    }

    // ❌ DO NOT INTERCEPT HTML PAGES (IMPORTANT)
    if (event.request.headers.get("accept")?.includes("text/html")) {
        return;
    }

    // ✅ Only allow normal network for static files (no cache)
    event.respondWith(fetch(event.request).catch(() => {
        return new Response("", { status: 503 });
    }));
});

// ==========================
// CLEAR CACHE ON DEMAND
// ==========================
self.addEventListener("message", event => {

    if (event.data === "CLEAR_CACHE") {
        event.waitUntil(
            caches.keys().then(keys => {
                return Promise.all(keys.map(key => caches.delete(key)));
            })
        );
    }

    if (event.data === "SKIP_WAITING") {
        self.skipWaiting();
    }
});

// ==========================
// PUSH NOTIFICATIONS
// ==========================
self.addEventListener("push", event => {

    const data = event.data ? event.data.json() : {};

    const title = data.title || "Duare Dokandar";
    const options = {
        body: data.body || "You have a new update",
        icon: "/logo.png",
        badge: "/logo.png",
        data: {
            url: data.url || "/dashboard"
        }
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

    const targetUrl = event.notification.data?.url || "/dashboard";

    event.waitUntil(
        clients.matchAll({ type: "window", includeUncontrolled: true })
            .then(clientList => {
                for (let client of clientList) {
                    if (client.url === targetUrl && "focus" in client) {
                        return client.focus();
                    }
                }
                return clients.openWindow(targetUrl);
            })
    );
});