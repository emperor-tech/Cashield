import './bootstrap';

// Import Vue and components first
import { createApp } from 'vue';
import UserAchievements from './components/UserAchievements.vue';
import NotificationPreferences from './components/NotificationPreferences.vue';
import ProfileTabs from './components/ProfileTabs.vue';
import PanicButton from './components/PanicButton.vue';

// Then import other dependencies
import Echo from 'laravel-echo';
// Use ES Module import for Pusher
import Pusher from 'pusher-js';
window.Pusher = Pusher;

// Set up Echo with Reverb configuration
window.Echo = new Echo({
    broadcaster: 'reverb', // Use reverb broadcaster
    key: import.meta.env.VITE_REVERB_APP_KEY, // Use the Reverb app key from .env
    wsHost: import.meta.env.VITE_REVERB_HOST, // Use the Reverb host from .env
    wsPort: import.meta.env.VITE_REVERB_PORT, // Use the Reverb port from .env
    wssPort: import.meta.env.VITE_REVERB_PORT, // Use the Reverb port for WSS
    forceTLS: false,
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
});

// Listen for a test event on the test channel using the window.Echo instance
window.Echo.channel('test-channel')
    .listen('TestEvent', (e) => {
        console.log('Received event:', e);
    });

import './reports';

// Mount Vue components
window.mountVueComponents = () => {
    console.log('Mounting Vue components...');
    
    // Mount PanicButton component
    const panicButtonEl = document.getElementById('panic-button');
    if (panicButtonEl) {
        try {
            const app = createApp(PanicButton);
            app.mount(panicButtonEl);
            console.log('PanicButton component mounted successfully');
        } catch (error) {
            console.error('Error mounting PanicButton component:', error);
        }
    }
    
    // Mount ProfileTabs component
    const profileTabsEl = document.getElementById('profile-tabs');
    console.log('ProfileTabs element:', profileTabsEl);
    
    if (profileTabsEl) {
        try {
            // Fetch the HTML content for the forms
            const profileForm = document.getElementById('profile-form-content')?.innerHTML || '';
            const notificationsForm = document.getElementById('notifications-form-content')?.innerHTML || '';
            const passwordForm = document.getElementById('password-form-content')?.innerHTML || '';
            
            // Get user data from the meta tag
            const userDataMeta = document.querySelector('meta[name="user-data"]');
            const userData = userDataMeta ? JSON.parse(userDataMeta.content) : {};
            
            console.log('User data for ProfileTabs:', userData);
            
            const app = createApp(ProfileTabs, { 
                user: userData,
                profileForm: profileForm,
                notificationsForm: notificationsForm,
                passwordForm: passwordForm
            });
            
            app.component('UserAchievements', UserAchievements);
            app.component('NotificationPreferences', NotificationPreferences);
            
            app.mount(profileTabsEl);
            console.log('ProfileTabs component mounted successfully');
        } catch (error) {
            console.error('Error mounting ProfileTabs component:', error);
        }
    }
    
    // Mount standalone UserAchievements component (if not in ProfileTabs)
    const userAchievementsEl = document.getElementById('user-achievements');
    console.log('Standalone UserAchievements element:', userAchievementsEl);
    
    if (userAchievementsEl && !document.getElementById('profile-tabs')) {
        try {
            const userData = JSON.parse(userAchievementsEl.dataset.user || '{}');
            console.log('User data for standalone UserAchievements:', userData);
            
            const app = createApp(UserAchievements, { user: userData });
            app.mount(userAchievementsEl);
            console.log('Standalone UserAchievements component mounted successfully');
        } catch (error) {
            console.error('Error mounting standalone UserAchievements component:', error);
        }
    }
    
    // Mount standalone NotificationPreferences component (if not in ProfileTabs)
    const notificationPreferencesEl = document.getElementById('notification-preferences');
    console.log('Standalone NotificationPreferences element:', notificationPreferencesEl);
    
    if (notificationPreferencesEl && !document.getElementById('profile-tabs')) {
        try {
            const app = createApp(NotificationPreferences);
            app.mount(notificationPreferencesEl);
            console.log('Standalone NotificationPreferences component mounted successfully');
        } catch (error) {
            console.error('Error mounting standalone NotificationPreferences component:', error);
        }
    }
};

// Initialize Vue components when DOM is loaded
document.addEventListener('DOMContentLoaded', window.mountVueComponents);
console.log('DOM content loaded event listener added');

window.showToast = function(message, type = 'success') {
    const toastArea = document.getElementById('toast-area');
    if (!toastArea) return;
    const toast = document.createElement('div');
    toast.className = `mb-2 px-4 py-2 rounded shadow text-white font-semibold transition bg-${type === 'success' ? 'green' : type === 'error' ? 'red' : 'blue'}-600`;
    toast.innerText = message;
    toastArea.appendChild(toast);
    setTimeout(() => { toast.remove(); }, 3500);
};
