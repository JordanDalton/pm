import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';
import { AuthService } from './services/auth';

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

// Initialize API token for authenticated users (web session to API token)
// This is wrapped in a timeout to ensure the app is fully loaded
setTimeout(async () => {
    try {
        const isAuthenticated = document.querySelector('meta[name="authenticated"][content="true"]');
        console.log('Auth meta tag present:', !!isAuthenticated);
        
        // First check if API routes are configured correctly
        try {
            const debugResponse = await fetch('/api/debug/public');
            if (debugResponse.ok) {
                console.log('API routes are configured correctly');
            } else {
                console.error('API routes not responding correctly:', debugResponse.status);
            }
        } catch (apiCheckError) {
            console.error('Failed to check API routes:', apiCheckError);
        }
        
        // Only generate a token if user is authenticated and doesn't already have one
        if (isAuthenticated) {
            console.log('User is authenticated via web session, generating token...');
            try {
                const result = await AuthService.generateToken();
                console.log('API token generated successfully:', result);
            } catch (tokenError) {
                console.error('Generate token error details:', tokenError);
            }
        } else {
            console.log('User not authenticated, skipping token generation');
        }
    } catch (error) {
        console.error('Failed to generate API token:', error);
    }
}, 1000);
