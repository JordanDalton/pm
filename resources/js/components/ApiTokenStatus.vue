<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import { AuthService } from '@/services/auth';

const hasToken = ref(false);
const tokenInfo = ref('');
const isGenerating = ref(false);

onMounted(() => {
    // Check if we have an API token
    const token = AuthService.getToken();
    hasToken.value = !!token;
    
    if (token) {
        // Show part of the token for verification
        tokenInfo.value = token.substring(0, 10) + '...' + token.substring(token.length - 10);
    }
});

// Generate a new token
const generateToken = async () => {
    isGenerating.value = true;
    
    try {
        const result = await AuthService.generateToken();
        hasToken.value = true;
        tokenInfo.value = result.token.substring(0, 10) + '...' + result.token.substring(result.token.length - 10);
    } catch (error) {
        console.error('Failed to generate token:', error);
    } finally {
        isGenerating.value = false;
    }
};

// Revoke the current token
const revokeToken = async () => {
    try {
        await AuthService.logout();
        hasToken.value = false;
        tokenInfo.value = '';
    } catch (error) {
        console.error('Failed to revoke token:', error);
    }
};
</script>

<template>
    <div class="p-4 bg-muted/50 rounded-lg">
        <h3 class="font-medium mb-2">API Token Status</h3>
        
        <div v-if="hasToken" class="mb-4">
            <p class="text-sm text-muted-foreground mb-2">
                <span class="font-medium text-foreground">Active</span> - Your session has an API token for secure communication
            </p>
            <p class="text-xs font-mono bg-muted p-2 rounded">{{ tokenInfo }}</p>
            
            <Button variant="destructive" size="sm" class="mt-2" @click="revokeToken">
                Revoke Token
            </Button>
        </div>
        
        <div v-else class="mb-4">
            <p class="text-sm text-muted-foreground mb-2">
                <span class="font-medium text-foreground">Missing</span> - No API token found for your session
            </p>
            
            <Button variant="default" size="sm" :disabled="isGenerating" @click="generateToken">
                <span v-if="isGenerating">Generating...</span>
                <span v-else>Generate Token</span>
            </Button>
        </div>
    </div>
</template>