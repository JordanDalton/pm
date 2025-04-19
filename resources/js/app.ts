import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';
import { useAuth } from './composables/useAuth';
import { AuthService } from './services/auth';
import { initAuthPersistence } from './plugins/persistentAuth';

// Extend ImportMeta interface for Vite...
declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
        readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
    }
}

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// Initialize auth persistence to keep tokens during navigation
initAuthPersistence();

// Initialize API token for authenticated users (web session to API token)
// This is wrapped in a timeout to ensure the app is fully loaded
setTimeout(async () => {
    try {
        // Check auth meta tag
        const isAuthenticated = document.querySelector('meta[name="authenticated"][content="true"]');
        console.log('Auth meta tag present:', !!isAuthenticated);
        
        // If user is authenticated and we're not on the login page
        if (isAuthenticated && window.location.pathname !== '/login') {
            console.log('User is authenticated via web session, initializing auth...');
            
            // Don't initialize auth if token already exists
            if (!localStorage.getItem('api_token')) {
                try {
                    // Generate and store token using AuthService directly
                    const result = await AuthService.generateToken();
                    console.log('API token generated successfully by app initializer');
                    
                    // Test the token with a simple API call
                    try {
                        const response = await fetch('/api/user', {
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'Authorization': `Bearer ${localStorage.getItem('api_token')}`
                            },
                            credentials: 'include'
                        });
                        
                        if (response.ok) {
                            console.log('API authentication working correctly');
                        } else {
                            console.error('API authentication failed:', response.status);
                        }
                    } catch (apiError) {
                        console.error('API test error:', apiError);
                    }
                } catch (tokenError) {
                    console.error('Generate token error details:', tokenError);
                }
            } else {
                console.log('Token already exists in localStorage');
            }
        } else {
            console.log('User not authenticated or on login page, skipping token generation');
        }
    } catch (error) {
        console.error('Failed to generate API token:', error);
    }
}, 1000);
