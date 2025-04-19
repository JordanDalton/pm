<script setup lang="ts">
import { onMounted } from 'vue';
import AuthService from '@/services/AuthService';

// This component initializes the API token for authenticated users
// It should be included in the main app layout

onMounted(async () => {
  try {
    // Only try to generate a token if the user is authenticated in the session
    // but doesn't have an API token yet
    if (!AuthService.isAuthenticated()) {
      const response = await AuthService.generateToken();
      
      if (response.data && response.data.token) {
        // Store the token and user data
        AuthService.storeToken(response.data.token);
        AuthService.storeUser(response.data.user);
        console.log('API token generated and stored successfully');
      }
    }
  } catch (error) {
    console.error('Failed to initialize API token:', error);
    // If there's an error here, it's likely because the user is not authenticated
    // We don't need to do anything special in that case
  }
});
</script>

<template>
  <!-- This is an invisible component that only handles API token initialization -->
</template>