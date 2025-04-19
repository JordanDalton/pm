<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogTrigger } from '@/components/ui/dialog';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';
import { Link } from '@inertiajs/vue3';
import { computed, ref, onMounted, watch } from 'vue';
import TicketService, { type Ticket, type TicketFilters } from '@/services/TicketService';
import TokenInitializer from '@/components/TokenInitializer.vue';

// Breadcrumbs for navigation
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Tickets',
        href: '/tickets',
    },
];

// Tickets data
const tickets = ref<Ticket[]>([]);
const isLoading = ref(true);
const error = ref<string | null>(null);

// Filter values
const filterStatus = ref('All');
const filterPriority = ref('All');
const filterAssignee = ref('All');
const searchQuery = ref('');

// Filter options
const statuses = ref<string[]>(['All']);
const priorities = ref<string[]>(['All']);
const assignees = ref<string[]>(['All']);

// Load tickets from API
const loadTickets = async () => {
    isLoading.value = true;
    error.value = null;
    
    try {
        // Prepare filter parameters
        const filters: TicketFilters = {};
        if (filterStatus.value !== 'All') filters.status = filterStatus.value;
        if (filterPriority.value !== 'All') filters.priority = filterPriority.value;
        if (filterAssignee.value !== 'All') filters.assignee = filterAssignee.value;
        if (searchQuery.value) filters.search = searchQuery.value;
        
        const response = await TicketService.getTickets(filters);
        tickets.value = response.data.tickets || [];
        
        // Extract unique options for filters
        if (!filters.status && !filters.priority && !filters.assignee) {
            const uniqueStatuses = [...new Set(tickets.value.map(ticket => ticket.status))];
            const uniquePriorities = [...new Set(tickets.value.map(ticket => ticket.priority))];
            const uniqueAssignees = [...new Set(tickets.value.map(ticket => ticket.assignee))];
            
            statuses.value = ['All', ...uniqueStatuses];
            priorities.value = ['All', ...uniquePriorities];
            assignees.value = ['All', ...uniqueAssignees];
        }
    } catch (err: any) {
        error.value = err.message || 'Failed to load tickets';
        console.error('Error loading tickets:', err);
    } finally {
        isLoading.value = false;
    }
};

// Watch for filter changes to reload tickets
watch([filterStatus, filterPriority, filterAssignee], () => {
    loadTickets();
});

// Debounce search to avoid too many API calls
watch(searchQuery, () => {
    loadTickets();
}, { debounce: 300 });

// Computed property for formatted tickets that handles API response structure
const formattedTickets = computed(() => {
    return tickets.value.map(ticket => ({
        id: ticket.id,
        title: ticket.title,
        status: ticket.status,
        priority: ticket.priority,
        assignee: {
            name: ticket.assignee, // Assuming assignee is a string name in API
            email: '', // We might need to update this based on actual API response
            avatar: null
        },
        dueDate: ticket.due_date,
        createdAt: ticket.created_at,
        labels: ticket.labels || [],
        project: 'Project Management App' // This may need to be updated based on API data
    }));
});

// Filtered tickets - with the implementation of API filters, this function is simpler
const filteredTickets = computed(() => {
    return formattedTickets.value;
});

// Format date helper
const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

// View types
const viewType = ref('list'); // list or board

// Dialog state for creating new tickets
const isCreateDialogOpen = ref(false);
const newTicket = ref({
    title: '',
    description: '',
    status: 'To Do',
    priority: 'Medium',
    assignee: '',
    board_id: 'DEV-1' // Default board ID
});

// Function to create a new ticket
const createTicket = async () => {
    try {
        await TicketService.createTicket(newTicket.value);
        isCreateDialogOpen.value = false;
        // Reset form
        newTicket.value = {
            title: '',
            description: '',
            status: 'To Do',
            priority: 'Medium',
            assignee: '',
            board_id: 'DEV-1'
        };
        // Reload tickets to show the new one
        loadTickets();
    } catch (err: any) {
        console.error('Error creating ticket:', err);
        // You might want to show an error message to the user
    }
};

// Load tickets when component mounts
onMounted(() => {
    loadTickets();
});
</script>

<template>
    <Head title="Tickets" />
    <TokenInitializer />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Tickets</h1>
                    <p class="text-muted-foreground">Manage and track your project tickets</p>
                </div>
                <div class="flex gap-2">
                    <!-- View toggle buttons -->
                    <div class="flex rounded-md border">
                        <button 
                            class="px-3 py-1.5 text-sm transition-colors" 
                            :class="viewType === 'list' ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'"
                            @click="viewType = 'list'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                        </button>
                        <button 
                            class="px-3 py-1.5 text-sm transition-colors"
                            :class="viewType === 'board' ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'"
                            @click="viewType = 'board'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        </button>
                    </div>
                    
                    <Dialog v-model:open="isCreateDialogOpen">
                        <DialogTrigger as-child>
                            <Button>Create Ticket</Button>
                        </DialogTrigger>
                        <DialogContent class="sm:max-w-[550px]">
                            <div class="p-6">
                                <h2 class="text-lg font-semibold mb-4">Create New Ticket</h2>
                                <form @submit.prevent="createTicket" class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="title">Title</label>
                                        <input 
                                            id="title" 
                                            v-model="newTicket.title" 
                                            class="w-full p-2 border rounded-md" 
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="description">Description</label>
                                        <textarea 
                                            id="description" 
                                            v-model="newTicket.description" 
                                            class="w-full p-2 border rounded-md"
                                            rows="3"
                                            required
                                        ></textarea>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="status">Status</label>
                                            <select id="status" v-model="newTicket.status" class="w-full p-2 border rounded-md">
                                                <option v-for="status in statuses.filter(s => s !== 'All')" :key="status">{{ status }}</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="priority">Priority</label>
                                            <select id="priority" v-model="newTicket.priority" class="w-full p-2 border rounded-md">
                                                <option v-for="priority in priorities.filter(p => p !== 'All')" :key="priority">{{ priority }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="assignee">Assignee</label>
                                        <select id="assignee" v-model="newTicket.assignee" class="w-full p-2 border rounded-md">
                                            <option value="">Unassigned</option>
                                            <option v-for="assignee in assignees.filter(a => a !== 'All')" :key="assignee">{{ assignee }}</option>
                                        </select>
                                    </div>
                                    <div class="flex justify-end gap-2">
                                        <Button @click="isCreateDialogOpen = false" variant="outline">Cancel</Button>
                                        <Button type="submit">Create Ticket</Button>
                                    </div>
                                </form>
                            </div>
                        </DialogContent>
                    </Dialog>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="flex flex-wrap gap-4">
                <div class="w-full md:w-auto">
                    <label class="block text-sm font-medium mb-1" for="filter-status">Status</label>
                    <select 
                        id="filter-status" 
                        v-model="filterStatus" 
                        class="w-full md:w-40 p-2 border rounded-md"
                    >
                        <option v-for="status in statuses" :key="status">{{ status }}</option>
                    </select>
                </div>
                <div class="w-full md:w-auto">
                    <label class="block text-sm font-medium mb-1" for="filter-priority">Priority</label>
                    <select 
                        id="filter-priority" 
                        v-model="filterPriority" 
                        class="w-full md:w-40 p-2 border rounded-md"
                    >
                        <option v-for="priority in priorities" :key="priority">{{ priority }}</option>
                    </select>
                </div>
                <div class="w-full md:w-auto">
                    <label class="block text-sm font-medium mb-1" for="filter-assignee">Assignee</label>
                    <select 
                        id="filter-assignee" 
                        v-model="filterAssignee" 
                        class="w-full md:w-40 p-2 border rounded-md"
                    >
                        <option v-for="assignee in assignees" :key="assignee">{{ assignee }}</option>
                    </select>
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium mb-1" for="search">Search</label>
                    <input 
                        id="search" 
                        v-model="searchQuery" 
                        class="w-full p-2 border rounded-md" 
                        placeholder="Search tickets by ID or title..."
                    />
                </div>
            </div>
            
            <!-- Loading state -->
            <div v-if="isLoading" class="py-8 text-center">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                <p class="mt-2 text-muted-foreground">Loading tickets...</p>
            </div>
            
            <!-- Error state -->
            <div v-else-if="error" class="py-8 text-center">
                <div class="text-destructive mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <p>{{ error }}</p>
                </div>
                <Button @click="loadTickets" variant="outline" size="sm">Try Again</Button>
            </div>
            
            <!-- Empty state -->
            <div v-else-if="filteredTickets.length === 0" class="py-8 text-center border rounded-lg">
                <div class="text-muted-foreground">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-2"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9"></path><path d="M13 2v7h7"></path></svg>
                    <p class="mb-2">No tickets found</p>
                    <p class="text-sm mb-4">Try adjusting your filters or create a new ticket</p>
                </div>
                <DialogTrigger>
                    <Button @click="isCreateDialogOpen = true">Create a Ticket</Button>
                </DialogTrigger>
            </div>
            
            <!-- List view -->
            <div v-else-if="viewType === 'list'" class="space-y-4">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="border-b">
                            <th class="pb-2 text-left font-medium text-sm w-24">ID</th>
                            <th class="pb-2 text-left font-medium text-sm">Title</th>
                            <th class="pb-2 text-left font-medium text-sm hidden sm:table-cell w-28">Status</th>
                            <th class="pb-2 text-left font-medium text-sm hidden md:table-cell w-28">Priority</th>
                            <th class="pb-2 text-left font-medium text-sm hidden md:table-cell w-32">Assignee</th>
                            <th class="pb-2 text-left font-medium text-sm hidden lg:table-cell w-32">Due Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="ticket in filteredTickets" :key="ticket.id" class="border-b hover:bg-muted/50">
                            <td class="py-3">
                                <Link :href="route('tickets.show', ticket.id)" class="hover:underline">
                                    {{ ticket.id }}
                                </Link>
                            </td>
                            <td class="py-3">
                                <Link :href="route('tickets.show', ticket.id)" class="hover:underline font-medium">
                                    {{ ticket.title }}
                                </Link>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    <span 
                                        v-for="label in ticket.labels" 
                                        :key="label" 
                                        class="px-1.5 py-0.5 rounded-sm text-xs bg-primary/10 text-primary"
                                    >
                                        {{ label }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-3 hidden sm:table-cell">
                                <span 
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                    :class="{
                                        'bg-green-100 text-green-800': ticket.status === 'Done',
                                        'bg-blue-100 text-blue-800': ticket.status === 'In Progress',
                                        'bg-yellow-100 text-yellow-800': ticket.status === 'QA/Testing' || ticket.status === 'Review',
                                        'bg-gray-100 text-gray-800': ticket.status === 'To Do',
                                        'bg-purple-100 text-purple-800': ticket.status === 'Backlog'
                                    }"
                                >
                                    {{ ticket.status }}
                                </span>
                            </td>
                            <td class="py-3 hidden md:table-cell">
                                <span 
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                    :class="{
                                        'bg-red-100 text-red-800': ticket.priority === 'High',
                                        'bg-yellow-100 text-yellow-800': ticket.priority === 'Medium',
                                        'bg-green-100 text-green-800': ticket.priority === 'Low'
                                    }"
                                >
                                    {{ ticket.priority }}
                                </span>
                            </td>
                            <td class="py-3 hidden md:table-cell">
                                <div class="flex items-center">
                                    <Avatar class="h-6 w-6 mr-2">
                                        <AvatarImage :src="ticket.assignee.avatar" />
                                        <AvatarFallback>{{ useInitials(ticket.assignee.name) }}</AvatarFallback>
                                    </Avatar>
                                    <span class="text-sm">{{ ticket.assignee.name }}</span>
                                </div>
                            </td>
                            <td class="py-3 hidden lg:table-cell text-sm">
                                {{ ticket.dueDate ? formatDate(ticket.dueDate) : '-' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Board view -->
            <div v-else class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div v-for="status in ['To Do', 'In Progress', 'Review', 'Done']" :key="status" class="flex flex-col">
                    <div class="mb-3 pb-2 border-b flex justify-between items-center">
                        <h3 class="font-medium">{{ status }}</h3>
                        <span class="text-xs bg-muted rounded px-2 py-0.5">
                            {{ filteredTickets.filter(t => t.status === status).length }}
                        </span>
                    </div>
                    <div class="space-y-3 flex-1">
                        <div 
                            v-for="ticket in filteredTickets.filter(t => t.status === status)" 
                            :key="ticket.id" 
                            class="p-3 border rounded-md bg-card hover:shadow-sm"
                        >
                            <Link :href="route('tickets.show', ticket.id)" class="block">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="text-xs font-medium text-muted-foreground">{{ ticket.id }}</span>
                                    <span 
                                        class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="{
                                            'bg-red-100 text-red-800': ticket.priority === 'High',
                                            'bg-yellow-100 text-yellow-800': ticket.priority === 'Medium',
                                            'bg-green-100 text-green-800': ticket.priority === 'Low'
                                        }"
                                    >
                                        {{ ticket.priority }}
                                    </span>
                                </div>
                                <h4 class="font-medium mb-2">{{ ticket.title }}</h4>
                                <div class="flex flex-wrap gap-1 mb-3">
                                    <span 
                                        v-for="label in ticket.labels" 
                                        :key="label" 
                                        class="px-1.5 py-0.5 rounded-sm text-xs bg-primary/10 text-primary"
                                    >
                                        {{ label }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center">
                                        <Avatar class="h-6 w-6">
                                            <AvatarImage :src="ticket.assignee.avatar" />
                                            <AvatarFallback>{{ useInitials(ticket.assignee.name) }}</AvatarFallback>
                                        </Avatar>
                                    </div>
                                    <span class="text-xs text-muted-foreground">
                                        {{ ticket.dueDate ? formatDate(ticket.dueDate) : 'No due date' }}
                                    </span>
                                </div>
                            </Link>
                        </div>
                        <div 
                            v-if="filteredTickets.filter(t => t.status === status).length === 0" 
                            class="p-3 border border-dashed rounded-md text-center text-muted-foreground text-sm"
                        >
                            No tickets
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- AI Assistant Insights -->
            <Card>
                <CardHeader>
                    <CardTitle>AI Assistant Insights</CardTitle>
                    <CardDescription>Based on your ticket data, here are some insights</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="p-4 bg-primary/5 rounded-lg">
                        <p class="mb-2"><strong>Key observations:</strong></p>
                        <ul class="space-y-1 list-disc list-inside">
                            <li>3 high priority tickets require attention</li>
                            <li>2 tickets are approaching their due dates</li>
                            <li>The Frontend Development board has the most tickets in progress</li>
                            <li>Consider redistributing work as Jordan Dalton has the most assigned high priority tickets</li>
                        </ul>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>