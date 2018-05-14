importScripts('https://unpkg.com/workbox-sw@2.1.1')

const workboxSW = new self.WorkboxSW();
workboxSW.precache([]);

workboxSW.router.registerRoute(
    '/',
    workboxSW.strategies.networkFirst()
);