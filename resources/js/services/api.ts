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

// Add a request interceptor to handle CSRF token and authentication
api.interceptors.request.use(config => {
    // Get the CSRF token from the meta tag
    const csrfToken = document.head.querySelector('meta[name="csrf-token"]');
    
    if (csrfToken) {
        config.headers['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
    }
    
    // Add bearer token if available
    const token = localStorage.getItem('api_token');
    if (token) {
        config.headers['Authorization'] = `Bearer ${token}`;
    }
    
    return config;
});

// Add a response interceptor to handle unauthorized responses
api.interceptors.response.use(
    response => response,
    error => {
        // Handle 401 responses (unauthorized)
        if (error.response && error.response.status === 401) {
            // Clear stored tokens
            localStorage.removeItem('api_token');
            localStorage.removeItem('user');
            
            // Redirect to login page if not already there
            if (window.location.pathname !== '/login') {
                window.location.href = '/login';
            }
        }
        
        return Promise.reject(error);
    }
);

export default api;