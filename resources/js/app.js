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

window.showToast = function(message, type = 'success') {
    const toastArea = document.getElementById('toast-area');
    if (!toastArea) return;
    const toast = document.createElement('div');
    toast.className = `mb-2 px-4 py-2 rounded shadow text-white font-semibold transition bg-${type === 'success' ? 'green' : type === 'error' ? 'red' : 'blue'}-600`;
    toast.innerText = message;
    toastArea.appendChild(toast);
    setTimeout(() => { toast.remove(); }, 3500);
};
