self.addEventListener('push', event => {
    let data = {};
    try { data = event.data ? event.data.json() : {}; } catch (error) {}
    const url = new URL(data.url || '/', self.location.origin);
    event.waitUntil(self.registration.showNotification(data.title || 'Growbiz', {
        body: data.body || 'You have a new update.',
        tag: data.tag || 'growbiz-update',
        data: {url: url.origin === self.location.origin ? url.href : self.location.origin},
    }));
});
self.addEventListener('notificationclick', event => {
    event.notification.close();
    const url = event.notification.data?.url || self.location.origin;
    event.waitUntil(self.clients.openWindow(url));
});
