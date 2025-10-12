importScripts('https://www.gstatic.com/firebasejs/8.6.1/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.6.1/firebase-messaging.js');

firebase.initializeApp({
    apiKey: 'AIzaSyBPOMqKKdqSdpPj9tz_afIEJvaqgRXyV_Q',
    authDomain: 'galvanic-crow-406709.firebaseapp.com',
    projectId: 'galvanic-crow-406709',
    storageBucket: 'galvanic-crow-406709.appspot.com',
    messagingSenderId: '178025172773',
    appId: '1:178025172773:web:0178b8a88037c6d3bf94cc',
    measurementId: 'G-G271MLZDYP'
});

const messaging = firebase.messaging();

messaging.setBackgroundMessageHandler(function (payload) {
    const notificationTitle = payload.notification?.title || 'New notification';
    const notificationOptions = {
        body: payload.notification?.body || '',
        icon: payload.notification?.icon || '/images/icon-192x192.png'
    };

    return self.registration.showNotification(notificationTitle, notificationOptions);
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    const target = event.notification?.click_action || '/';
    event.waitUntil(clients.openWindow(target));
});
