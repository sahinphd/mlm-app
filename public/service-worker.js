// ==========================
// INSTALL
// ==========================
self.addEventListener("install", event => {
    self.skipWaiting();
});

// ==========================
// ACTIVATE
// ==========================
self.addEventListener("activate", event => {
    self.clients.claim();
});

// ==========================
// FETCH
// ==========================
// ❌ Do NOT touch requests (prevents 419 error)
self.addEventListener("fetch", event => {
    // Leave everything to browser
});

// ==========================
// PUSH NOTIFICATION
// ==========================
self.addEventListener("push", event => {
    const data = event.data ? event.data.json() : {};

    event.waitUntil(
        self.registration.showNotification(
            data.title || "Notification",
            {
                body: data.body || "New update",
                icon: "/logo.png"
            }
        )
    );
});

// ==========================
// NOTIFICATION CLICK
// ==========================
self.addEventListener("notificationclick", event => {
    event.notification.close();

    event.waitUntil(
        clients.openWindow("/dashboard")
    );
});