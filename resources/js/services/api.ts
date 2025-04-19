import axios from 'axios';

// Create an axios instance with default config
const api = axios.create({
    baseURL: '/api',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
    withCredentials: true
});

// Add a request interceptor to handle CSRF token
api.interceptors.request.use(config => {
    // Get the CSRF token from the meta tag
    const token = document.head.querySelector('meta[name="csrf-token"]');
    
    if (token) {
        config.headers['X-CSRF-TOKEN'] = token.getAttribute('content');
    }
    
    return config;
});

export default api;