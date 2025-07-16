import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Configure axios for Laravel Sanctum SPA authentication
window.axios.defaults.withCredentials = true;

// Set CSRF token from meta tag
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

// Global fetch interceptor for session authentication
const originalFetch = window.fetch;
window.fetch = function(url, options = {}) {
    // Only add credentials for API requests to our backend
    if (typeof url === 'string' && (url.startsWith('/api/') || url.startsWith('/login') || url.startsWith('/logout') || url.startsWith('/sanctum/'))) {
        options.credentials = 'include';
    }

    return originalFetch(url, options);
};
