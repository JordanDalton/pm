<script setup lang="ts">
import { SidebarProvider } from '@/components/ui/sidebar';
import { usePage } from '@inertiajs/vue3';
import { SharedData } from '@/types';
import { onMounted } from 'vue';
import { useAuth } from '@/composables/useAuth';

interface Props {
    variant?: 'header' | 'sidebar';
}

defineProps<Props>();

const isOpen = usePage<SharedData>().props.sidebarOpen;

// Initialize auth composable
const auth = useAuth();

// Initialize auth on mount (ensures we have API token)
onMounted(async () => {
    // Auth state will be automatically initialized by the composable
    console.log('AppShell mounted, auth state:', auth.isAuthenticated.value ? 'authenticated' : 'not authenticated');
});
</script>

<template>
    <div v-if="variant === 'header'" class="flex min-h-screen w-full flex-col">
        <slot />
    </div>
    <SidebarProvider v-else :default-open="isOpen">
        <slot />
    </SidebarProvider>
</template>
