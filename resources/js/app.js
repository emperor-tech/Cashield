import './bootstrap';

import Echo from 'laravel-echo';
window.Pusher = require('pusher-js');

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'local',
    wsHost: window.location.hostname,
    wsPort: 6001,
    forceTLS: false,
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
});

Echo.channel('test-channel')
    .listen('TestEvent', (e) => {
        console.log('Received event:', e);
    });

import './reports';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();
