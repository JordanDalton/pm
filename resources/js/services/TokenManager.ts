/**
 * Token Manager
 * 
 * This service handles token generation, validation, and storage.
 */

import axios from 'axios';
import { AuthService } from './auth';

// Debug helper
const logWithTime = (message, data = null) => {
    const now = new Date();
    const timestamp = `${now.getHours()}:${now.getMinutes()}:${now.getSeconds()}.${now.getMilliseconds()}`;
    console.log(`[${timestamp}] ${message}`, data || '');
};

// Ensure the auth data is stored in global variables for persistence
const storeAuthGlobally = (token: string, user: any): void => {
    // Store in window globals for persistence across page loads
    window.__PM_AUTH_TOKEN = token;
    window.__PM_AUTH_USER = typeof user === 'string' ? user : JSON.stringify(user);
    
    // Also backup to sessionStorage
    sessionStorage.setItem('pm_api_token', token);
    sessionStorage.setItem('pm_user', typeof user === 'string' ? user : JSON.stringify(user));
    
    logWithTime('TokenManager: Auth data stored globally', token.substring(0, 10) + '...');
};

export const TokenManager = {
    /**
     * Check if a token exists in any storage location
     */
    hasToken(): boolean {
        // Check localStorage first
        if (localStorage.getItem('api_token')) {
            return true;
        }
        
        // Check global variables
        if (window.__PM_AUTH_TOKEN) {
            // Restore from global variables
            localStorage.setItem('api_token', window.__PM_AUTH_TOKEN);
            if (window.__PM_AUTH_USER) {
                localStorage.setItem('user', window.__PM_AUTH_USER);
            }
            return true;
        }
        
        // Check sessionStorage
        if (sessionStorage.getItem('pm_api_token')) {
            // Restore from sessionStorage
            localStorage.setItem('api_token', sessionStorage.getItem('pm_api_token')!);
            if (sessionStorage.getItem('pm_user')) {
                localStorage.setItem('user', sessionStorage.getItem('pm_user')!);
            }
            return true;
        }
        
        return false;
    },
    
    /**
     * Get the token from the most reliable source
     */
    getToken(): string | null {
        // First check localStorage
        const localToken = localStorage.getItem('api_token');
        if (localToken) {
            // Ensure it's also stored globally for persistence
            const user = localStorage.getItem('user');
            if (user) {
                storeAuthGlobally(localToken, user);
            }
            return localToken;
        }
        
        // Then check global variables
        if (window.__PM_AUTH_TOKEN) {
            localStorage.setItem('api_token', window.__PM_AUTH_TOKEN);
            if (window.__PM_AUTH_USER) {
                localStorage.setItem('user', window.__PM_AUTH_USER);
            }
            return window.__PM_AUTH_TOKEN;
        }
        
        // Finally check sessionStorage
        const sessionToken = sessionStorage.getItem('pm_api_token');
        if (sessionToken) {
            localStorage.setItem('api_token', sessionToken);
            const user = sessionStorage.getItem('pm_user');
            if (user) {
                localStorage.setItem('user', user);
            }
            return sessionToken;
        }
        
        return null;
    },
    
    /**
     * Check if token is valid by making a test API request
     */
    async validateToken(): Promise<boolean> {
        const token = this.getToken();
        
        if (!token) {
            return false;
        }
        
        try {
            const response = await axios.get('/api/user', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            return response.status === 200;
        } catch (error) {
            console.error('Token validation failed:', error);
            return false;
        }
    },
    
    /**
     * Ensure a valid token exists (generate if needed)
     */
    async ensureToken(): Promise<string | null> {
        logWithTime('TokenManager: Ensuring token exists...');
        
        // First check if we already have a token in any storage
        if (this.hasToken()) {
            const token = this.getToken();
            logWithTime('TokenManager: Found existing token', token!.substring(0, 10) + '...');
            
            // Validate token if it exists
            try {
                const isValid = await this.validateToken();
                
                if (isValid) {
                    logWithTime('TokenManager: Token is valid');
                    return token;
                }
                
                logWithTime('TokenManager: Token is invalid, will generate new one');
                // Clear invalid token
                this.clearToken();
            } catch (error) {
                logWithTime('TokenManager: Error validating token', error);
                // We'll continue to generate a new token
            }
        }
        
        // Generate new token
        try {
            const isAuthenticated = document.querySelector('meta[name="authenticated"][content="true"]');
            
            if (isAuthenticated) {
                logWithTime('TokenManager: User is authenticated, generating token...');
                const result = await AuthService.generateToken();
                
                if (result?.token) {
                    logWithTime('TokenManager: New token generated', result.token.substring(0, 10) + '...');
                    // Ensure token is stored in global variables
                    storeAuthGlobally(result.token, result.user);
                    return result.token;
                } else {
                    logWithTime('TokenManager: Failed to generate token - no token in response');
                }
            } else {
                logWithTime('TokenManager: User not authenticated, cannot generate token');
            }
        } catch (error) {
            logWithTime('TokenManager: Token generation failed', error);
        }
        
        return null;
    },
    
    /**
     * Manually set a token and user data
     */
    setToken(token: string, user: any): void {
        localStorage.setItem('api_token', token);
        localStorage.setItem('user', typeof user === 'string' ? user : JSON.stringify(user));
        
        // Store globally for persistence
        storeAuthGlobally(token, user);
        
        logWithTime('TokenManager: Token manually set', token.substring(0, 10) + '...');
    },
    
    /**
     * Clear token and user data from all storage
     */
    clearToken(): void {
        localStorage.removeItem('api_token');
        localStorage.removeItem('user');
        sessionStorage.removeItem('pm_api_token');
        sessionStorage.removeItem('pm_user');
        window.__PM_AUTH_TOKEN = null;
        window.__PM_AUTH_USER = null;
        
        logWithTime('TokenManager: Token cleared from all storage');
    }
};

// Add type definition for window
declare global {
    interface Window {
        __PM_AUTH_TOKEN: string | null;
        __PM_AUTH_USER: string | null;
    }
}

export default TokenManager;