import api from './api';

export interface User {
    id: number;
    name: string;
    email: string;
}

export default {
    /**
     * Get authenticated user information
     */
    getUser() {
        return api.get('/user');
    },

    /**
     * Generate a token for a user that's already authenticated via web session
     */
    generateToken() {
        return api.post('/token/generate', {
            device_name: `web-${navigator.userAgent}`
        });
    },

    /**
     * Login directly via API (not normally used if using Inertia)
     */
    login(email: string, password: string) {
        return api.post('/token', {
            email,
            password,
            device_name: `web-${navigator.userAgent}`
        });
    },

    /**
     * Revoke all tokens for the authenticated user
     */
    revokeTokens() {
        return api.post('/token/revoke');
    },

    /**
     * Store the authentication token in localStorage
     */
    storeToken(token: string) {
        localStorage.setItem('api_token', token);
    },

    /**
     * Store the user information in localStorage
     */
    storeUser(user: User) {
        localStorage.setItem('user', JSON.stringify(user));
    },

    /**
     * Clear authentication data from localStorage
     */
    clearAuth() {
        localStorage.removeItem('api_token');
        localStorage.removeItem('user');
    },

    /**
     * Check if the user is authenticated (has a token)
     */
    isAuthenticated() {
        return localStorage.getItem('api_token') !== null;
    },

    /**
     * Get the current user from localStorage
     */
    getCurrentUser(): User | null {
        const userStr = localStorage.getItem('user');
        return userStr ? JSON.parse(userStr) : null;
    }
}