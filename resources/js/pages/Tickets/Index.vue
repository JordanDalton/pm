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
import { computed, ref } from 'vue';

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

// Mock data for tickets
const tickets = [
    {
        id: 'PM-123',
        title: 'Implement AI-powered task prioritization',
        status: 'In Progress',
        priority: 'High',
        assignee: {
            name: 'Jordan Dalton',
            email: 'jordan@example.com',
            avatar: null
        },
        dueDate: '2025-05-01',
        createdAt: '2025-04-15',
        labels: ['AI', 'Enhancement', 'Backend'],
        project: 'Project Management App'
    },
    {
        id: 'PM-124',
        title: 'Design user dashboard with analytics',
        status: 'To Do',
        priority: 'Medium',
        assignee: {
            name: 'Alex Smith',
            email: 'alex@example.com',
            avatar: null
        },
        dueDate: '2025-05-10',
        createdAt: '2025-04-16',
        labels: ['UI/UX', 'Frontend'],
        project: 'Project Management App'
    },
    {
        id: 'PM-125',
        title: 'Implement team collaboration features',
        status: 'To Do',
        priority: 'Medium',
        assignee: {
            name: 'Jordan Dalton',
            email: 'jordan@example.com',
            avatar: null
        },
        dueDate: '2025-05-15',
        createdAt: '2025-04-16',
        labels: ['Feature', 'Backend'],
        project: 'Project Management App'
    },
    {
        id: 'PM-126',
        title: 'Add email notification system',
        status: 'Done',
        priority: 'Low',
        assignee: {
            name: 'Taylor Morgan',
            email: 'taylor@example.com',
            avatar: null
        },
        dueDate: '2025-04-25',
        createdAt: '2025-04-10',
        labels: ['Backend', 'Enhancement'],
        project: 'Project Management App'
    },
    {
        id: 'PM-127',
        title: 'Fix search functionality in project view',
        status: 'In Progress',
        priority: 'High',
        assignee: {
            name: 'Alex Smith',
            email: 'alex@example.com',
            avatar: null
        },
        dueDate: '2025-04-22',
        createdAt: '2025-04-18',
        labels: ['Bug', 'Frontend'],
        project: 'Project Management App'
    },
    {
        id: 'PM-128',
        title: 'Implement drag-and-drop for task board',
        status: 'To Do',
        priority: 'Medium',
        assignee: {
            name: 'Jordan Dalton',
            email: 'jordan@example.com',
            avatar: null
        },
        dueDate: '2025-05-20',
        createdAt: '2025-04-17',
        labels: ['Enhancement', 'Frontend', 'UX'],
        project: 'Project Management App'
    },
    {
        id: 'PM-129',
        title: 'Create API documentation for developers',
        status: 'To Do',
        priority: 'Low',
        assignee: {
            name: 'Taylor Morgan',
            email: 'taylor@example.com',
            avatar: null
        },
        dueDate: '2025-06-01',
        createdAt: '2025-04-18',
        labels: ['Documentation', 'API'],
        project: 'Project Management App'
    },
    {
        id: 'PM-130',
        title: 'Implement user authentication with SSO',
        status: 'In Progress',
        priority: 'High',
        assignee: {
            name: 'Jordan Dalton',
            email: 'jordan@example.com',
            avatar: null
        },
        dueDate: '2025-04-30',
        createdAt: '2025-04-12',
        labels: ['Security', 'Backend'],
        project: 'Project Management App'
    }
];

// Filter values
const filterStatus = ref('All');
const filterPriority = ref('All');
const filterAssignee = ref('All');
const searchQuery = ref('');

// Unique options for filters
const statuses = ['All', ...new Set(tickets.map(ticket => ticket.status))];
const priorities = ['All', ...new Set(tickets.map(ticket => ticket.priority))];
const assignees = ['All', ...new Set(tickets.map(ticket => ticket.assignee.name))];

// Filtered tickets
const filteredTickets = computed(() => {
    return tickets.filter(ticket => {
        const matchesStatus = filterStatus.value === 'All' || ticket.status === filterStatus.value;
        const matchesPriority = filterPriority.value === 'All' || ticket.priority === filterPriority.value;
        const matchesAssignee = filterAssignee.value === 'All' || ticket.assignee.name === filterAssignee.value;
        const matchesSearch = searchQuery.value === '' || 
            ticket.title.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
            ticket.id.toLowerCase().includes(searchQuery.value.toLowerCase());
        
        return matchesStatus && matchesPriority && matchesAssignee && matchesSearch;
    });
});

// Format date helper
const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

// View types
const viewType = ref('list'); // list or board
</script>

<template>
    <Head title="Tickets" />

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
                    
                    <Dialog>
                        <DialogTrigger as-child>
                            <Button>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                New Ticket
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <div class="p-4">
                                <h2 class="text-lg font-bold">Create New Ticket</h2>
                                <p class="text-sm text-muted-foreground">Fill in the details to create a new ticket</p>
                                <div class="mt-4">
                                    <!-- Simple form placeholder -->
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Title</label>
                                            <input type="text" class="w-full rounded-md border p-2 text-sm" placeholder="Enter ticket title" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Description</label>
                                            <textarea class="w-full rounded-md border p-2 text-sm min-h-[100px]" placeholder="Enter ticket description"></textarea>
                                        </div>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-sm font-medium mb-1">Priority</label>
                                                <select class="w-full rounded-md border p-2 text-sm">
                                                    <option>Low</option>
                                                    <option>Medium</option>
                                                    <option>High</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium mb-1">Assignee</label>
                                                <select class="w-full rounded-md border p-2 text-sm">
                                                    <option v-for="assignee in assignees.filter(a => a !== 'All')" :key="assignee">
                                                        {{ assignee }}
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-6 flex justify-end gap-2">
                                        <Button variant="outline">Cancel</Button>
                                        <Button>Create Ticket</Button>
                                    </div>
                                </div>
                            </div>
                        </DialogContent>
                    </Dialog>
                </div>
            </div>

            <!-- Filters -->
            <Card>
                <CardContent class="p-4">
                    <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Search</label>
                            <input 
                                type="text" 
                                v-model="searchQuery"
                                class="w-full rounded-md border p-2 text-sm" 
                                placeholder="Search by title or ID" 
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Status</label>
                            <select v-model="filterStatus" class="w-full rounded-md border p-2 text-sm">
                                <option v-for="status in statuses" :key="status" :value="status">
                                    {{ status }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Priority</label>
                            <select v-model="filterPriority" class="w-full rounded-md border p-2 text-sm">
                                <option v-for="priority in priorities" :key="priority" :value="priority">
                                    {{ priority }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Assignee</label>
                            <select v-model="filterAssignee" class="w-full rounded-md border p-2 text-sm">
                                <option v-for="assignee in assignees" :key="assignee" :value="assignee">
                                    {{ assignee }}
                                </option>
                            </select>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- AI Assistant Card -->
            <Card class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950 dark:to-indigo-950 border border-blue-100 dark:border-blue-900">
                <CardContent class="p-4">
                    <div class="flex items-center space-x-4">
                        <div class="rounded-full bg-blue-100 dark:bg-blue-900 p-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600 dark:text-blue-300"><path d="M21 12a9 9 0 1 1-9-9 8.997 8.997 0 0 1 8.484 6"></path><path d="M14.942 11.942 21 12"></path><path d="M9.002 16c.855.74 2.053.93 3.122.494 1.07-.434 1.796-1.426 1.879-2.554"></path><rect x="10" y="8" width="0.01" height="0.01"></rect><rect x="14" y="8" width="0.01" height="0.01"></rect></svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-medium text-blue-700 dark:text-blue-300">AI Assistant Insights</h3>
                            <p class="text-sm text-blue-600 dark:text-blue-400">According to task analysis, you should prioritize the authentication tasks since they're blocking other work. There are 3 high-priority tickets that should be completed this week.</p>
                        </div>
                        <Button variant="outline" class="border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900">
                            See Analysis
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- List View of tickets -->
            <div v-if="viewType === 'list'">
                <Card>
                    <CardHeader class="py-4 px-6">
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-lg">All Tickets</CardTitle>
                            <div class="text-sm text-muted-foreground">
                                Showing {{ filteredTickets.length }} of {{ tickets.length }} tickets
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div class="overflow-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b bg-muted/50">
                                        <th class="text-left p-4 text-sm font-medium text-muted-foreground">ID</th>
                                        <th class="text-left p-4 text-sm font-medium text-muted-foreground">Title</th>
                                        <th class="text-left p-4 text-sm font-medium text-muted-foreground">Status</th>
                                        <th class="text-left p-4 text-sm font-medium text-muted-foreground">Priority</th>
                                        <th class="text-left p-4 text-sm font-medium text-muted-foreground">Assignee</th>
                                        <th class="text-left p-4 text-sm font-medium text-muted-foreground">Due Date</th>
                                        <th class="text-left p-4 text-sm font-medium text-muted-foreground">Labels</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="ticket in filteredTickets" :key="ticket.id" class="border-b hover:bg-muted/50 transition-colors">
                                        <td class="p-4 align-middle text-sm font-medium text-primary">
                                            <Link :href="route('tickets.show', ticket.id)" class="hover:underline">
                                                {{ ticket.id }}
                                            </Link>
                                        </td>
                                        <td class="p-4 align-middle text-sm">
                                            <Link :href="route('tickets.show', ticket.id)" class="hover:underline">
                                                {{ ticket.title }}
                                            </Link>
                                        </td>
                                        <td class="p-4 align-middle">
                                            <span class="px-2 py-1 text-xs rounded-full" 
                                                :class="{
                                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': ticket.status === 'Done',
                                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300': ticket.status === 'In Progress',
                                                    'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300': ticket.status === 'To Do'
                                                }">
                                                {{ ticket.status }}
                                            </span>
                                        </td>
                                        <td class="p-4 align-middle">
                                            <span class="px-2 py-1 text-xs rounded-full" 
                                                :class="{
                                                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300': ticket.priority === 'High',
                                                    'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300': ticket.priority === 'Medium',
                                                    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300': ticket.priority === 'Low'
                                                }">
                                                {{ ticket.priority }}
                                            </span>
                                        </td>
                                        <td class="p-4 align-middle">
                                            <div class="flex items-center gap-2">
                                                <Avatar class="h-6 w-6">
                                                    <AvatarImage v-if="ticket.assignee.avatar" :src="ticket.assignee.avatar" />
                                                    <AvatarFallback>{{ useInitials(ticket.assignee.name) }}</AvatarFallback>
                                                </Avatar>
                                                <span class="text-sm">{{ ticket.assignee.name }}</span>
                                            </div>
                                        </td>
                                        <td class="p-4 align-middle text-sm">
                                            {{ formatDate(ticket.dueDate) }}
                                        </td>
                                        <td class="p-4 align-middle">
                                            <div class="flex flex-wrap gap-1">
                                                <span v-for="label in ticket.labels.slice(0, 2)" :key="label" class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                                    {{ label }}
                                                </span>
                                                <span v-if="ticket.labels.length > 2" class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                                    +{{ ticket.labels.length - 2 }}
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Board View of tickets -->
            <div v-else-if="viewType === 'board'" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div v-for="status in ['To Do', 'In Progress', 'Done']" :key="status" class="flex flex-col gap-2">
                    <div class="px-4 py-2 bg-muted rounded-md font-medium">
                        {{ status }}
                        <span class="ml-2 text-xs text-muted-foreground">
                            ({{ filteredTickets.filter(t => t.status === status).length }})
                        </span>
                    </div>
                    
                    <div class="flex flex-col gap-2 min-h-[200px]">
                        <Card v-for="ticket in filteredTickets.filter(t => t.status === status)" :key="ticket.id" class="shadow-sm hover:shadow transition-shadow">
                            <CardContent class="p-4">
                                <Link :href="route('tickets.show', ticket.id)" class="hover:underline">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-primary">{{ ticket.id }}</span>
                                        <span class="px-2 py-1 text-xs rounded-full" 
                                            :class="{
                                                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300': ticket.priority === 'High',
                                                'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300': ticket.priority === 'Medium',
                                                'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300': ticket.priority === 'Low'
                                            }">
                                            {{ ticket.priority }}
                                        </span>
                                    </div>
                                    <h3 class="font-medium mb-2">{{ ticket.title }}</h3>
                                </Link>
                                
                                <div class="flex items-center justify-between text-sm text-muted-foreground">
                                    <div class="flex items-center gap-2">
                                        <Avatar class="h-5 w-5">
                                            <AvatarImage v-if="ticket.assignee.avatar" :src="ticket.assignee.avatar" />
                                            <AvatarFallback>{{ useInitials(ticket.assignee.name) }}</AvatarFallback>
                                        </Avatar>
                                        <span class="text-xs">{{ ticket.assignee.name }}</span>
                                    </div>
                                    <span class="text-xs">{{ formatDate(ticket.dueDate) }}</span>
                                </div>
                                
                                <div class="mt-3 flex flex-wrap gap-1">
                                    <span v-for="label in ticket.labels.slice(0, 2)" :key="label" class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                        {{ label }}
                                    </span>
                                    <span v-if="ticket.labels.length > 2" class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                        +{{ ticket.labels.length - 2 }}
                                    </span>
                                </div>
                            </CardContent>
                        </Card>
                        
                        <div v-if="filteredTickets.filter(t => t.status === status).length === 0" class="p-4 text-center text-sm text-muted-foreground border border-dashed rounded-md">
                            No tickets in this status
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>