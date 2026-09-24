import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import './ui';
import './product-detail';
import './profile-address';
import './form-validation';
import './unsaved-form';
import { initAdminNotifications } from './admin-notifications';

window.Pusher = Pusher;
const isLoggedIn = document.querySelector('meta[name="app-user-logged-in"]')?.content === '1';
const isAdmin = document.querySelector('meta[name="app-user-is-admin"]')?.content === '1';

if (isLoggedIn && import.meta.env.VITE_REVERB_APP_KEY) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
    });
}

document.addEventListener('DOMContentLoaded', () => {
    if (isAdmin) initAdminNotifications(window.Echo || null);
});
