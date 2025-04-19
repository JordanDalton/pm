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
}, error => {
    console.error('API Request Error:', error);
    return Promise.reject(error);
});

// Add a response interceptor to handle various error responses
api.interceptors.response.use(
    response => response,
    error => {
        // Log the error for debugging
        console.error('API Response Error:', error.response || error.message);
        
        // Handle specific status codes
        if (error.response) {
            switch (error.response.status) {
                case 401: // Unauthorized
                    // Clear stored tokens
                    localStorage.removeItem('api_token');
                    localStorage.removeItem('user');
                    
                    // Redirect to login page if not already there
                    if (window.location.pathname !== '/login') {
                        window.location.href = '/login';
                    }
                    break;
                
                case 403: // Forbidden
                    console.error('Access forbidden:', error.response.data.message || 'You do not have permission to access this resource');
                    break;
                
                case 404: // Not Found
                    console.error('Resource not found:', error.response.data.message || 'The requested resource could not be found');
                    break;
                
                case 422: // Validation error
                    console.error('Validation error:', error.response.data.errors || error.response.data.message);
                    break;
                
                case 500: // Server error
                case 502: // Bad gateway
                case 503: // Service unavailable
                case 504: // Gateway timeout
                    console.error('Server error:', error.response.data.message || 'A server error occurred');
                    break;
            }
        } else if (error.request) {
            // The request was made but no response was received
            console.error('No response received:', error.request);
        }
        
        return Promise.reject(error);
    }
);

export default api;