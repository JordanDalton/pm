/**
 * Persistent Auth Plugin
 * 
 * This plugin ensures that the authentication token persists across page navigations
 * by adding event listeners to store and restore tokens during page visits.
 */

import { router } from '@inertiajs/vue3';

// Debug helper
const logWithTime = (message, data = null) => {
    const now = new Date();
    const timestamp = `${now.getHours()}:${now.getMinutes()}:${now.getSeconds()}.${now.getMilliseconds()}`;
    console.log(`[${timestamp}] ${message}`, data || '');
};

// Store auth data before page visits
const storeAuthData = () => {
    // Only store if we have tokens in localStorage
    const token = localStorage.getItem('api_token');
    const user = localStorage.getItem('user');
    
    logWithTime('NAVIGATION: Checking for tokens to store...');
    
    if (token && user) {
        // Create a custom event to notify when the token is being stored
        window.dispatchEvent(new CustomEvent('auth-token-storing'));
        
        // Store directly in a global variable
        window.__PM_AUTH_TOKEN = token;
        window.__PM_AUTH_USER = user;
        
        // Also backup to sessionStorage as a fallback
        sessionStorage.setItem('pm_api_token', token);
        sessionStorage.setItem('pm_user', user);
        
        logWithTime('NAVIGATION: Auth data stored for navigation', token.substring(0, 10) + '...');
    } else {
        logWithTime('NAVIGATION: No token found to store');
    }
};

// Restore auth data after page visits
const restoreAuthData = () => {
    // Try to get from global variables first
    let token = window.__PM_AUTH_TOKEN;
    let user = window.__PM_AUTH_USER;
    
    // If not in globals, try sessionStorage
    if (!token || !user) {
        token = sessionStorage.getItem('pm_api_token');
        user = sessionStorage.getItem('pm_user');
    }
    
    logWithTime('NAVIGATION: Checking for tokens to restore...');
    
    if (token && user) {
        // Restore to localStorage
        localStorage.setItem('api_token', token);
        localStorage.setItem('user', user);
        
        // Fire an event for components to handle
        window.dispatchEvent(new CustomEvent('auth-token-restored', {
            detail: { token }
        }));
        
        logWithTime('NAVIGATION: Auth data restored after navigation', token.substring(0, 10) + '...');
        
        // Clear temporary storage
        window.__PM_AUTH_TOKEN = null;
        window.__PM_AUTH_USER = null;
        sessionStorage.removeItem('pm_api_token');
        sessionStorage.removeItem('pm_user');
    } else {
        logWithTime('NAVIGATION: No saved token found to restore');
    }
};

// Direct check for token presence
const checkTokenPresence = () => {
    const token = localStorage.getItem('api_token');
    const user = localStorage.getItem('user');
    
    logWithTime('DIRECT CHECK: Token presence in localStorage', token ? 'Present' : 'Missing');
    
    return !!token && !!user;
};

// Initialize persistence
export const initAuthPersistence = () => {
    // Make available on window for debugging
    window.__checkTokenPresence = checkTokenPresence;
    
    // Listen for navigation events
    router.on('before', (event) => {
        logWithTime(`NAVIGATION: Before navigating to ${event.detail.visit.url.href}`);
        storeAuthData();
    });
    
    router.on('start', (event) => {
        logWithTime(`NAVIGATION: Starting navigation to ${event.detail.visit.url.href}`);
    });
    
    router.on('progress', (event) => {
        logWithTime(`NAVIGATION: Navigation progress ${event.detail.progress}`);
    });
    
    router.on('success', (event) => {
        logWithTime(`NAVIGATION: Successful navigation to ${event.detail.page.url}`);
        // Delay restoration slightly to ensure DOM is ready
        setTimeout(() => {
            restoreAuthData();
        }, 50);
    });
    
    router.on('error', (errors) => {
        logWithTime('NAVIGATION: Navigation error', errors);
    });
    
    router.on('invalid', (event) => {
        logWithTime(`NAVIGATION: Invalid response during navigation`);
    });
    
    router.on('exception', (event) => {
        logWithTime(`NAVIGATION: Exception during navigation`, event);
    });
    
    router.on('finish', (event) => {
        logWithTime(`NAVIGATION: Navigation finished`);
        // Double-check token presence after a short delay
        setTimeout(() => {
            if (!checkTokenPresence()) {
                logWithTime('NAVIGATION: Token missing after navigation, attempting to restore...');
                restoreAuthData();
            }
        }, 200);
    });
    
    // Check if we have data to restore on initial load
    setTimeout(() => {
        restoreAuthData();
        
        // Set up a periodic check to ensure token is present
        setInterval(() => {
            if (!checkTokenPresence()) {
                logWithTime('PERIODIC CHECK: Token missing, attempting to restore...');
                restoreAuthData();
            }
        }, 1000);
    }, 500);
    
    logWithTime('Auth persistence initialized');
    
    // Provide debug methods
    return {
        checkTokenPresence,
        restoreAuthData,
        storeAuthData
    };
};

// Add type definition for window
declare global {
    interface Window {
        __PM_AUTH_TOKEN: string | null;
        __PM_AUTH_USER: string | null;
        __checkTokenPresence: () => boolean;
    }
}

export default {
    initAuthPersistence
};