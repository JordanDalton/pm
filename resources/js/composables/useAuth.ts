import { ref, computed, onMounted } from 'vue';
import { AuthService } from '../services/auth';
import { usePage } from '@inertiajs/vue3';

// Create a reactive state for authentication
const isAuthenticated = ref(false);
const user = ref<any>(null);
const token = ref<string | null>(null);
const isLoading = ref(true);

/**
 * Vue composable for authentication
 */
export function useAuth() {
    // Initialize auth state
    onMounted(async () => {
        isLoading.value = true;
        
        try {
            // First check if we have stored auth data
            const storedToken = AuthService.getToken();
            const storedUser = AuthService.getCurrentUser();
            
            if (storedToken && storedUser) {
                // We already have a token
                token.value = storedToken;
                user.value = storedUser;
                isAuthenticated.value = true;
            } else {
                // Check if we're authenticated via web session
                const page = usePage();
                // @ts-ignore - auth is defined on the page props
                const auth = page.props.auth;
                
                if (auth && auth.user) {
                    // We're authenticated via web session, get an API token
                    try {
                        const response = await AuthService.generateToken();
                        token.value = response.token;
                        user.value = response.user;
                        isAuthenticated.value = true;
                    } catch (error) {
                        console.error('Failed to generate API token:', error);
                        isAuthenticated.value = false;
                    }
                } else {
                    isAuthenticated.value = false;
                }
            }
        } catch (error) {
            console.error('Auth initialization error:', error);
            isAuthenticated.value = false;
        } finally {
            isLoading.value = false;
        }
    });
    
    /**
     * Login with email and password
     */
    const login = async (email: string, password: string) => {
        isLoading.value = true;
        
        try {
            const response = await AuthService.login(email, password);
            token.value = response.token;
            user.value = response.user;
            isAuthenticated.value = true;
            return true;
        } catch (error) {
            console.error('Login failed:', error);
            isAuthenticated.value = false;
            return false;
        } finally {
            isLoading.value = false;
        }
    };
    
    /**
     * Logout user
     */
    const logout = async () => {
        isLoading.value = true;
        
        try {
            await AuthService.logout();
            token.value = null;
            user.value = null;
            isAuthenticated.value = false;
        } catch (error) {
            console.error('Logout failed:', error);
        } finally {
            isLoading.value = false;
        }
    };
    
    /**
     * Get current authentication state
     */
    const getAuthState = computed(() => ({
        isAuthenticated: isAuthenticated.value,
        user: user.value,
        token: token.value,
        isLoading: isLoading.value,
    }));
    
    return {
        // State
        isAuthenticated,
        user,
        token,
        isLoading,
        
        // Computed
        authState: getAuthState,
        
        // Methods
        login,
        logout,
    };
}

// Export a singleton instance
export default useAuth;