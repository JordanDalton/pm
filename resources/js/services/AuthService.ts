import api from './api';
import axios from 'axios';

export interface User {
    id: number;
    name: string;
    email: string;
}

// For Sanctum CSRF protection
const csrfCookie = async () => {
    return axios.get('/sanctum/csrf-cookie');
};

export default {
    /**
     * Get authenticated user information
     * With Sanctum, the session cookie will be sent automatically
     */
    async getUser() {
        try {
            // First try to get user using Sanctum auth
            try {
                const response = await axios.get('/api/user');
                console.log('Got user via Sanctum token', response.data);
                return response.data;
            } catch (tokenError) {
                console.log('Could not get user via Sanctum token, falling back to session auth');
                
                // Fallback to session auth if Sanctum fails
                const webResponse = await axios.get('/api/user-basic');
                console.log('Got user via web session', webResponse.data);
                return webResponse.data;
            }
        } catch (error) {
            console.error('Failed to get user:', error);
            return null;
        }
    },

    /**
     * Login with credentials
     * This uses Laravel's session-based authentication
     */
    async login(email: string, password: string, remember: boolean = false) {
        // Get CSRF cookie first
        await csrfCookie();
        
        try {
            // Login via Laravel's session authentication
            const response = await axios.post('/login', {
                email,
                password,
                remember
            });
            
            // If successful, get the user data
            const userData = await this.getUser();
            if (userData) {
                this.storeUser(userData);
                return {
                    success: true,
                    user: userData
                };
            }
            
            return { success: true };
        } catch (error) {
            console.error('Login failed:', error);
            return {
                success: false,
                error
            };
        }
    },

    /**
     * Logout the user
     */
    async logout() {
        try {
            await axios.post('/logout');
            this.clearAuth();
            return { success: true };
        } catch (error) {
            console.error('Logout failed:', error);
            return {
                success: false,
                error
            };
        }
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
        localStorage.removeItem('user');
    },

    /**
     * Check if the user is authenticated
     * With Sanctum, the session cookie is used for authentication
     * so we'll check if we have a user in localStorage as an indicator
     */
    isAuthenticated() {
        return this.getCurrentUser() !== null;
    },

    /**
     * Get the current user from localStorage
     */
    getCurrentUser(): User | null {
        const userStr = localStorage.getItem('user');
        return userStr ? JSON.parse(userStr) : null;
    }
}