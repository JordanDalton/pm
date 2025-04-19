<script setup lang="ts">
import { onMounted, onBeforeUnmount } from 'vue';
import TokenManager from '@/services/TokenManager';

/**
 * This component ensures an API token is available
 * Can be included in any page where API requests are needed
 */

// Log with timestamp for debugging
const logWithTime = (message, data = null) => {
  const now = new Date();
  const timestamp = `${now.getHours()}:${now.getMinutes()}:${now.getSeconds()}.${now.getMilliseconds()}`;
  console.log(`[${timestamp}] ${message}`, data || '');
};

// Handler for token restoration events
const handleTokenRestored = (event) => {
  logWithTime('TokenInitializer: Token restored event received', 
    event.detail?.token ? event.detail.token.substring(0, 10) + '...' : 'No token in event');
};

// Generate token on mount
onMounted(async () => {
  logWithTime('TokenInitializer: Component mounted');
  
  // Add event listener for token restoration
  window.addEventListener('auth-token-restored', handleTokenRestored);
  
  try {
    // Check if we can get token from storage immediately
    const storedToken = localStorage.getItem('api_token');
    if (storedToken) {
      logWithTime('TokenInitializer: Token already exists in localStorage', storedToken.substring(0, 10) + '...');
      return;
    }
    
    // Try to get token from window global
    if (window.__PM_AUTH_TOKEN) {
      logWithTime('TokenInitializer: Found token in window global, restoring', window.__PM_AUTH_TOKEN.substring(0, 10) + '...');
      localStorage.setItem('api_token', window.__PM_AUTH_TOKEN);
      if (window.__PM_AUTH_USER) {
        localStorage.setItem('user', window.__PM_AUTH_USER);
      }
      return;
    }
    
    // Try to get token from sessionStorage
    const sessionToken = sessionStorage.getItem('pm_api_token');
    if (sessionToken) {
      logWithTime('TokenInitializer: Found token in sessionStorage, restoring', sessionToken.substring(0, 10) + '...');
      localStorage.setItem('api_token', sessionToken);
      const sessionUser = sessionStorage.getItem('pm_user');
      if (sessionUser) {
        localStorage.setItem('user', sessionUser);
      }
      return;
    }
    
    // If no token found in any storage, generate a new one
    logWithTime('TokenInitializer: No token found, generating new token...');
    const token = await TokenManager.ensureToken();
    if (token) {
      logWithTime('TokenInitializer: New token generated', token.substring(0, 10) + '...');
    } else {
      logWithTime('TokenInitializer: Failed to generate new token');
    }
  } catch (error) {
    console.error('TokenInitializer error:', error);
  }
  
  // Set up a check to ensure token exists after a delay
  setTimeout(() => {
    const hasToken = !!localStorage.getItem('api_token');
    logWithTime('TokenInitializer: Token check after delay', hasToken ? 'Present' : 'Missing');
    
    if (!hasToken) {
      logWithTime('TokenInitializer: Attempting emergency token recovery');
      // Try to recover from any available source
      if (window.__PM_AUTH_TOKEN) {
        localStorage.setItem('api_token', window.__PM_AUTH_TOKEN);
        if (window.__PM_AUTH_USER) localStorage.setItem('user', window.__PM_AUTH_USER);
        logWithTime('TokenInitializer: Recovered from window global');
      } else if (sessionStorage.getItem('pm_api_token')) {
        localStorage.setItem('api_token', sessionStorage.getItem('pm_api_token')!);
        if (sessionStorage.getItem('pm_user')) {
          localStorage.setItem('user', sessionStorage.getItem('pm_user')!);
        }
        logWithTime('TokenInitializer: Recovered from sessionStorage');
      }
    }
  }, 500);
});

// Clean up
onBeforeUnmount(() => {
  window.removeEventListener('auth-token-restored', handleTokenRestored);
  logWithTime('TokenInitializer: Component unmounted');
});
</script>

<template>
  <!-- This is an invisible component -->
</template>