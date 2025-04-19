import axios from 'axios';
import TokenManager from './TokenManager';

// Debug helper
const logWithTime = (message, data = null) => {
    const now = new Date();
    const timestamp = `${now.getHours()}:${now.getMinutes()}:${now.getSeconds()}.${now.getMilliseconds()}`;
    console.log(`[${timestamp}] ${message}`, data || '');
};

// Create an axios instance with default config
const api = axios.create({
    baseURL: '/api',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
    withCredentials: true // Important for cookie-based authentication with Sanctum
});

// Add a request interceptor to handle CSRF token and API token
api.interceptors.request.use(async config => {
    // Get the CSRF token from the meta tag
    const csrfToken = document.head.querySelector('meta[name="csrf-token"]');
    
    if (csrfToken) {
        config.headers['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
    }
    
    // Add API token from multiple possible sources
    try {
        // First check if we have a token in storage
        if (TokenManager.hasToken()) {
            const token = TokenManager.getToken();
            if (token) {
                config.headers['Authorization'] = `Bearer ${token}`;
                logWithTime('API: Using stored token for request', 
                    token.substring(0, 10) + '... to ' + config.url);
            }
        } else {
            // If no token in storage, try to generate one
            const isAuthenticated = document.querySelector('meta[name="authenticated"][content="true"]');
            if (isAuthenticated) {
                logWithTime('API: No token found but user is authenticated. Generating token for request to ' + config.url);
                try {
                    const token = await TokenManager.ensureToken();
                    if (token) {
                        config.headers['Authorization'] = `Bearer ${token}`;
                        logWithTime('API: Generated and using new token', token.substring(0, 10) + '...');
                    }
                } catch (tokenError) {
                    logWithTime('API: Failed to generate token', tokenError);
                }
            }
        }
    } catch (error) {
        logWithTime('API: Error adding auth token to request', error);
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