// ==========================
// CONFIG
// ==========================
const CACHE_VERSION = "v3_safe";
const STATIC_CACHE = "static-" + CACHE_VERSION;

// ==========================
// INSTALL
// ==========================
self.addEventListener("install", event => {
    console.log("SW Installed (Safe Mode)");
    self.skipWaiting();
});

// ==========================
// ACTIVATE
// ==========================
self.addEventListener("activate", event => {
    console.log("SW Activated - Clearing old caches");

    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.map(key => caches.delete(key)))
        )
    );

    self.clients.claim();
});

// ==========================
// FETCH (CRITICAL FIX)
// ==========================
self.addEventListener("fetch", event => {

    const url = new URL(event.request.url);

    // ❌ NEVER TOUCH THESE ROUTES (IMPORTANT)
    if (
        event.request.method !== "GET" ||
        url.pathname.includes("/login") ||
        url.pathname.includes("/logout") ||
        url.pathname.includes("/register") ||
        url.pathname.includes("/password") ||
        url.pathname.includes("/sanctum") ||
        url.pathname.includes("/api")
    ) {
        return; // Let browser handle normally
    }

    // ✅ Only cache safe GET pages (optional)
    event.respondWith(
        fetch(event.request)
            .then(response => {
                return response;
            })
            .catch(() => {
                return new Response("Offline", { status: 503 });
            })
    );
});

// ==========================
// FORCE HARD RELOAD (VERY IMPORTANT)
// ==========================
self.addEventListener("message", event => {
    if (event.data === "SKIP_WAITING") {
        self.skipWaiting();
    }

    if (event.data === "CLEAR_CACHE") {
        caches.keys().then(keys => {
            keys.forEach(key => caches.delete(key));
        });
    }
});

// ==========================
// PUSH
// ==========================
self.addEventListener("push", event => {
    const data = event.data ? event.data.json() : {};

    event.waitUntil(
        self.registration.showNotification(data.title || "Notification", {
            body: data.body || "",
            icon: "/logo.png"
        })
    );
});

// ==========================
// CLICK
// ==========================
self.addEventListener("notificationclick", event => {
    event.notification.close();
    event.waitUntil(clients.openWindow("/dashboard"));
});