<script setup lang="ts">
import { onMounted } from 'vue';
import AuthService from '@/services/AuthService';

// This component fetches the authenticated user data and stores it
// It should be included in the main app layout

onMounted(async () => {
  try {
    // Fetch user data if not already authenticated
    if (!AuthService.isAuthenticated()) {
      const userData = await AuthService.getUser();
      if (userData && userData.user) {
        AuthService.storeUser(userData.user);
        console.log('User data fetched and stored successfully');
      }
    }
  } catch (error) {
    console.error('Failed to fetch user data:', error);
    // If there's an error here, it's likely because the user is not authenticated
    // We don't need to do anything special in that case
  }
});
</script>

<template>
  <!-- This is an invisible component that only handles user data initialization -->
</template>