import axios from 'axios';

// Create API client with CSRF handling for Laravel
const api = axios.create({
    baseURL: '/api', // This is correct - all routes are now prefixed with /api
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
    withCredentials: true, // Important for CSRF cookie
});

// Retrieve CSRF token from meta tag
const getCsrfToken = (): string => {
    const tokenElement = document.head.querySelector('meta[name="csrf-token"]');
    return tokenElement ? tokenElement.getAttribute('content') || '' : '';
};

// Intercept requests to add CSRF token and auth token if available
api.interceptors.request.use(config => {
    // Add CSRF token for all requests
    config.headers['X-CSRF-TOKEN'] = getCsrfToken();
    
    // Add bearer token if available in local storage
    const token = localStorage.getItem('api_token');
    if (token) {
        config.headers['Authorization'] = `Bearer ${token}`;
    }
    
    return config;
});

export const AuthService = {
    /**
     * Login with email and password to get API token
     */
    async login(email: string, password: string): Promise<{ token: string, user: any }> {
        try {
            const response = await api.post('/token', {
                email,
                password,
                device_name: 'browser', // Device identifier
            });
            
            // Store the token in local storage
            if (response.data.token) {
                localStorage.setItem('api_token', response.data.token);
                localStorage.setItem('user', JSON.stringify(response.data.user));
            }
            
            return response.data;
        } catch (error) {
            console.error('Login error:', error);
            throw error;
        }
    },
    
    /**
     * Generate API token from web session
     * This is used when user is already authenticated via web session
     */
    async generateToken(): Promise<{ token: string, user: any }> {
        try {
            // First get CSRF cookie
            try {
                await axios.get('/sanctum/csrf-cookie');
                console.log('CSRF cookie obtained successfully');
            } catch (csrfError) {
                console.error('Failed to get CSRF cookie:', csrfError);
            }
            
            // Make the token generation request with specific headers
            const response = await axios.post('/api/token/generate', {
                device_name: 'browser-' + new Date().getTime()
            }, {
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                withCredentials: true // Important for CSRF cookie
            });
            
            console.log('Token generation response:', response.data);
            
            // Store the token in local storage
            if (response.data && response.data.token) {
                localStorage.setItem('api_token', response.data.token);
                localStorage.setItem('user', JSON.stringify(response.data.user));
                console.log('Token stored in localStorage:', response.data.token.substring(0, 10) + '...');
            } else {
                console.error('No token in response:', response.data);
            }
            
            return response.data;
        } catch (error) {
            console.error('Token generation error:', error);
            throw error;
        }
    },
    
    /**
     * Logout and revoke API token
     */
    async logout(): Promise<void> {
        try {
            // Only call the API if we have a token
            const token = localStorage.getItem('api_token');
            if (token) {
                await api.post('/token/revoke');
            }
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            // Always clear local storage, even if API call fails
            localStorage.removeItem('api_token');
            localStorage.removeItem('user');
        }
    },
    
    /**
     * Check if user is authenticated
     */
    isAuthenticated(): boolean {
        return !!localStorage.getItem('api_token');
    },
    
    /**
     * Get current user from local storage
     */
    getCurrentUser(): any {
        const user = localStorage.getItem('user');
        return user ? JSON.parse(user) : null;
    },
    
    /**
     * Get auth token
     */
    getToken(): string | null {
        return localStorage.getItem('api_token');
    }
};

export default api;