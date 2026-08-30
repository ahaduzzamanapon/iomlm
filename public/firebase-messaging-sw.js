// Firebase Messaging Service Worker for IOM ERP Push Notifications
importScripts('https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.22.0/firebase-messaging-compat.js');

// Handle background messages
self.addEventListener('push', function(event) {
    if (event.data) {
        try {
            const payload = event.data.json();
            const title = payload.notification?.title || payload.data?.title || 'IOM ERP Notification';
            const options = {
                body: payload.notification?.body || payload.data?.body || '',
                icon: payload.notification?.icon || '/images/logo.png',
                image: payload.notification?.image || payload.data?.image_url || null,
                data: {
                    url: payload.notification?.click_action || payload.data?.action_url || '/'
                }
            };

            event.waitUntil(
                self.registration.showNotification(title, options)
            );
        } catch (e) {
            console.error('Error parsing FCM push payload:', e);
        }
    }
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    const targetUrl = event.notification.data?.url || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function(clientList) {
            for (let i = 0; i < clientList.length; i++) {
                let client = clientList[i];
                if (client.url === targetUrl && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});
