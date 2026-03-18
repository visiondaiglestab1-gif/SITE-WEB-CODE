// sw.js - Service Worker pour les notifications push
self.addEventListener('push', function(event) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }
    
    let data = {};
    if (event.data) {
        data = event.data.json();
    }
    
    const options = {
        body: data.body || 'Nouvelle notification',
        icon: '/images/logo.png',
        badge: '/images/logo.png',
        vibrate: [200, 100, 200],
        data: {
            url: data.url || '/'
        },
        actions: [
            {action: 'open', title: 'Ouvrir'},
            {action: 'close', title: 'Fermer'}
        ]
    };
    
    event.waitUntil(
        self.registration.showNotification(data.title || 'VISION D\'AIGLES', options)
    );
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    
    if (event.action === 'open') {
        clients.openWindow(event.notification.data.url);
    } else if (event.action === 'close') {
        return;
    } else {
        clients.openWindow(event.notification.data.url);
    }
});