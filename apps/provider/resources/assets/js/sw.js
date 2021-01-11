importScripts('https://unpkg.com/workbox-sw@2.1.1/build/importScripts/workbox-sw.prod.v2.1.1.js')

const workboxSW = new self.WorkboxSW();
workboxSW.precache([]);

workboxSW.router.registerRoute(
    '/',
    workboxSW.strategies.networkFirst()
);