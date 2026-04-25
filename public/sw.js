self.addEventListener('install', (event) => {
    console.log('[Service Worker] Install');
});

self.addEventListener('fetch', (event) => {
    // Basic fetch for PWA validation
});

self.addEventListener('push', function(event) {
    if (event.data) {
        const payload = event.data.json();
        const options = {
            body: payload.body,
            icon: '/icon-192x192.png',
            badge: '/icon-192x192.png'
        };
        event.waitUntil(
            self.registration.showNotification(payload.title, options)
        );
    }
});
