<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import { AuthService } from '@/services/auth';
import axios from 'axios';

const hasToken = ref(false);
const tokenInfo = ref('');
const isGenerating = ref(false);
const isChecking = ref(false);
const sessionInfo = ref<any>(null);
const errorMessage = ref('');
const diagnosticInfo = ref<any>(null);

onMounted(async () => {
    // Check if we have an API token
    const token = AuthService.getToken();
    hasToken.value = !!token;
    
    if (token) {
        // Show part of the token for verification
        tokenInfo.value = token.substring(0, 10) + '...' + token.substring(token.length - 10);
    }
    
    // Get session info
    await checkSessionInfo();
});

// Get session diagnostic info
const checkSessionInfo = async () => {
    isChecking.value = true;
    errorMessage.value = '';
    
    try {
        const response = await axios.get('/api/debug/session-info');
        sessionInfo.value = response.data;
    } catch (error) {
        console.error('Failed to get session info:', error);
        errorMessage.value = 'Failed to get session info. Is the debug route enabled?';
    } finally {
        isChecking.value = false;
    }
};

// Generate a new token
const generateToken = async () => {
    isGenerating.value = true;
    errorMessage.value = '';
    
    try {
        // Make direct API call for better debugging
        const response = await axios.post('/api/token/generate', {}, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            withCredentials: true // Important for CSRF cookie
        });
        
        // Store token
        if (response.data.token) {
            localStorage.setItem('api_token', response.data.token);
            localStorage.setItem('user', JSON.stringify(response.data.user));
            
            hasToken.value = true;
            tokenInfo.value = response.data.token.substring(0, 10) + '...' + 
                              response.data.token.substring(response.data.token.length - 10);
            
            diagnosticInfo.value = {
                response: response.data,
                status: response.status,
                headers: response.headers
            };
        }
    } catch (error: any) {
        console.error('Failed to generate token:', error);
        errorMessage.value = error.response?.data?.message || error.message || 'Unknown error generating token';
        diagnosticInfo.value = {
            error: error.message,
            response: error.response?.data,
            status: error.response?.status
        };
    } finally {
        isGenerating.value = false;
    }
};

// Revoke the current token
const revokeToken = async () => {
    errorMessage.value = '';
    
    try {
        await AuthService.logout();
        hasToken.value = false;
        tokenInfo.value = '';
        diagnosticInfo.value = null;
    } catch (error: any) {
        console.error('Failed to revoke token:', error);
        errorMessage.value = error.message || 'Unknown error revoking token';
    }
};
</script>

<template>
    <div class="p-4 bg-muted/50 rounded-lg">
        <h3 class="font-medium mb-2">API Token Status</h3>
        
        <div v-if="errorMessage" class="text-red-600 mb-2 p-2 bg-red-50 rounded">
            {{ errorMessage }}
        </div>
        
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
        
        <!-- Session Info -->
        <div class="border-t pt-2 mt-4">
            <h4 class="font-medium mb-2">Session Diagnostics</h4>
            <Button size="sm" variant="outline" :disabled="isChecking" @click="checkSessionInfo" class="mb-2">
                <span v-if="isChecking">Checking...</span>
                <span v-else>Check Session Status</span>
            </Button>
            
            <div v-if="sessionInfo" class="mt-2">
                <div class="text-sm mb-2">
                    <span class="font-medium">Auth Status:</span>
                    <span class="px-2 py-0.5 rounded-full ml-2" 
                          :class="sessionInfo.authenticated ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                        {{ sessionInfo.authenticated ? 'Authenticated' : 'Not Authenticated' }}
                    </span>
                </div>
                
                <div v-if="sessionInfo.user" class="text-sm mb-2">
                    <div><span class="font-medium">User:</span> {{ sessionInfo.user.name }}</div>
                    <div><span class="font-medium">Email:</span> {{ sessionInfo.user.email }}</div>
                </div>
                
                <div class="text-sm">
                    <div><span class="font-medium">Session Active:</span> {{ sessionInfo.session.has_session ? 'Yes' : 'No' }}</div>
                </div>
            </div>
        </div>
        
        <!-- API Response -->
        <div v-if="diagnosticInfo" class="border-t pt-2 mt-4">
            <h4 class="font-medium mb-2">API Response</h4>
            <div class="text-xs font-mono p-2 bg-muted rounded overflow-auto max-h-40">
                <pre>{{ JSON.stringify(diagnosticInfo, null, 2) }}</pre>
            </div>
        </div>
    </div>
</template>