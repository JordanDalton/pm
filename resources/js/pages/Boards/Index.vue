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
        title: 'Boards',
        href: route('boards.index'),
    },
];

// Create a mock data structure with boards grouped by team
const teams = [
    {
        id: 'engineering',
        name: 'Engineering',
        description: 'Technical development and implementation',
        color: 'blue',
        icon: 'CodeIcon',
        boards: [
            {
                id: 'eng-frontend',
                name: 'Frontend Development',
                description: 'UI/UX implementation and frontend features',
                ticketsCount: 24,
                completedTickets: 14,
                priority: 'High',
                lastUpdated: '2025-04-18T14:30:00Z',
                members: [
                    { name: 'Jordan Dalton', avatar: null },
                    { name: 'Alex Smith', avatar: null },
                    { name: 'Taylor Morgan', avatar: null },
                ]
            },
            {
                id: 'eng-backend',
                name: 'Backend Development',
                description: 'API development and database optimization',
                ticketsCount: 32,
                completedTickets: 18,
                priority: 'High',
                lastUpdated: '2025-04-17T11:45:00Z',
                members: [
                    { name: 'Jordan Dalton', avatar: null },
                    { name: 'Alex Smith', avatar: null },
                ]
            },
            {
                id: 'eng-devops',
                name: 'DevOps & Infrastructure',
                description: 'CI/CD pipeline and server management',
                ticketsCount: 18,
                completedTickets: 10,
                priority: 'Medium',
                lastUpdated: '2025-04-16T09:20:00Z',
                members: [
                    { name: 'Taylor Morgan', avatar: null },
                ]
            },
        ]
    },
    {
        id: 'marketing',
        name: 'Marketing',
        description: 'Brand awareness and customer acquisition',
        color: 'purple',
        icon: 'TrendingUpIcon',
        boards: [
            {
                id: 'mkt-campaigns',
                name: 'Marketing Campaigns',
                description: 'Planning and execution of marketing campaigns',
                ticketsCount: 15,
                completedTickets: 8,
                priority: 'Medium',
                lastUpdated: '2025-04-18T10:15:00Z',
                members: [
                    { name: 'Sarah Johnson', avatar: null },
                    { name: 'Michael Brown', avatar: null },
                ]
            },
            {
                id: 'mkt-content',
                name: 'Content Creation',
                description: 'Blog posts, social media, and video content',
                ticketsCount: 22,
                completedTickets: 12,
                priority: 'Medium',
                lastUpdated: '2025-04-15T16:30:00Z',
                members: [
                    { name: 'Sarah Johnson', avatar: null },
                    { name: 'Emily Davis', avatar: null },
                ]
            },
        ]
    },
    {
        id: 'product',
        name: 'Product',
        description: 'Product strategy and roadmap planning',
        color: 'green',
        icon: 'LayersIcon',
        boards: [
            {
                id: 'prd-roadmap',
                name: 'Product Roadmap',
                description: 'Strategic planning and feature prioritization',
                ticketsCount: 18,
                completedTickets: 7,
                priority: 'High',
                lastUpdated: '2025-04-18T09:45:00Z',
                members: [
                    { name: 'Jordan Dalton', avatar: null },
                    { name: 'Alex Smith', avatar: null },
                    { name: 'Sarah Johnson', avatar: null },
                ]
            },
            {
                id: 'prd-research',
                name: 'User Research',
                description: 'Customer interviews and usability testing',
                ticketsCount: 12,
                completedTickets: 9,
                priority: 'Low',
                lastUpdated: '2025-04-14T11:20:00Z',
                members: [
                    { name: 'Emily Davis', avatar: null },
                ]
            },
        ]
    },
    {
        id: 'design',
        name: 'Design',
        description: 'User experience and visual design',
        color: 'pink',
        icon: 'PaletteIcon',
        boards: [
            {
                id: 'dsg-ui',
                name: 'UI Design',
                description: 'Interface design for web and mobile',
                ticketsCount: 20,
                completedTickets: 15,
                priority: 'Medium',
                lastUpdated: '2025-04-17T15:10:00Z',
                members: [
                    { name: 'Emily Davis', avatar: null },
                    { name: 'Michael Brown', avatar: null },
                ]
            },
        ]
    }
];

// Search and filter functionality
const searchQuery = ref('');
const selectedTeam = ref('all');

// Filtered teams based on search and team filter
const filteredTeams = computed(() => {
    let result = [...teams];
    
    if (selectedTeam.value !== 'all') {
        result = result.filter(team => team.id === selectedTeam.value);
    }
    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.map(team => {
            const filteredBoards = team.boards.filter(board => 
                board.name.toLowerCase().includes(query) || 
                board.description.toLowerCase().includes(query)
            );
            return { ...team, boards: filteredBoards };
        }).filter(team => team.boards.length > 0);
    }
    
    return result;
});

// Format date helper
const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    
    // If date is today, show time
    const today = new Date();
    if (date.toDateString() === today.toDateString()) {
        return `Today at ${date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' })}`;
    }
    
    // If date is yesterday, show "Yesterday"
    const yesterday = new Date();
    yesterday.setDate(yesterday.getDate() - 1);
    if (date.toDateString() === yesterday.toDateString()) {
        return 'Yesterday';
    }
    
    // Otherwise show date
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

// Calculate board progress percentage
const getProgressPercentage = (completed: number, total: number) => {
    return Math.round((completed / total) * 100);
};

// Get class for priority badge
const getPriorityClass = (priority: string) => {
    switch (priority) {
        case 'High':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        case 'Medium':
            return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300';
        case 'Low':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300';
    }
};

// Get class for team color
const getTeamColorClass = (color: string) => {
    switch (color) {
        case 'blue':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        case 'purple':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300';
        case 'green':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'pink':
            return 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300';
    }
};

// Icons for each team
const getTeamIcon = (iconName: string) => {
    switch (iconName) {
        case 'CodeIcon':
            return `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>`;
        case 'TrendingUpIcon':
            return `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>`;
        case 'LayersIcon':
            return `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>`;
        case 'PaletteIcon':
            return `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="6.5" r=".5"></circle><circle cx="17.5" cy="10.5" r=".5"></circle><circle cx="8.5" cy="7.5" r=".5"></circle><circle cx="6.5" cy="12.5" r=".5"></circle><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"></path></svg>`;
        default:
            return `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect></svg>`;
    }
};
</script>

<template>
    <Head title="Boards" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Project Boards</h1>
                    <p class="text-muted-foreground">Organize and manage all your team's projects</p>
                </div>
                <div class="flex gap-2">
                    <Dialog>
                        <DialogTrigger as-child>
                            <Button>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                New Board
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <div class="p-4">
                                <h2 class="text-lg font-bold">Create New Board</h2>
                                <p class="text-sm text-muted-foreground">Fill in the details to create a new project board</p>
                                <div class="mt-4">
                                    <!-- Simple form placeholder -->
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Board Name</label>
                                            <input type="text" class="w-full rounded-md border p-2 text-sm" placeholder="Enter board name" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Description</label>
                                            <textarea class="w-full rounded-md border p-2 text-sm min-h-[80px]" placeholder="Enter board description"></textarea>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Team</label>
                                            <select class="w-full rounded-md border p-2 text-sm">
                                                <option v-for="team in teams" :key="team.id" :value="team.id">
                                                    {{ team.name }}
                                                </option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Priority</label>
                                            <select class="w-full rounded-md border p-2 text-sm">
                                                <option>Low</option>
                                                <option>Medium</option>
                                                <option>High</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mt-6 flex justify-end gap-2">
                                        <Button variant="outline">Cancel</Button>
                                        <Button>Create Board</Button>
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
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium mb-1">Search</label>
                            <input 
                                type="text" 
                                v-model="searchQuery"
                                class="w-full rounded-md border p-2 text-sm" 
                                placeholder="Search boards by name or description" 
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Team</label>
                            <select v-model="selectedTeam" class="w-full rounded-md border p-2 text-sm">
                                <option value="all">All Teams</option>
                                <option v-for="team in teams" :key="team.id" :value="team.id">
                                    {{ team.name }}
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
                            <p class="text-sm text-blue-600 dark:text-blue-400">The Engineering team is making great progress on their boards. The Frontend Development board has the highest activity this week with 8 completed tasks.</p>
                        </div>
                        <Button variant="outline" class="border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900">
                            See Analysis
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Team Boards -->
            <div v-for="team in filteredTeams" :key="team.id" class="space-y-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 flex items-center justify-center rounded-md" :class="getTeamColorClass(team.color)">
                        <div v-html="getTeamIcon(team.icon)" class="w-5 h-5"></div>
                    </div>
                    <h2 class="text-xl font-semibold">{{ team.name }}</h2>
                    <span class="text-sm text-muted-foreground">{{ team.description }}</span>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <Link 
                        v-for="board in team.boards" 
                        :key="board.id" 
                        :href="route('boards.show', { id: board.id })"
                        class="block"
                    >
                        <Card class="h-full hover:shadow-md transition-shadow">
                            <CardHeader class="pb-2">
                                <div class="flex items-start justify-between">
                                    <CardTitle class="text-lg">{{ board.name }}</CardTitle>
                                    <span class="px-2 py-1 text-xs rounded-full" :class="getPriorityClass(board.priority)">
                                        {{ board.priority }}
                                    </span>
                                </div>
                                <CardDescription>{{ board.description }}</CardDescription>
                            </CardHeader>
                            <CardContent class="pb-2">
                                <!-- Progress bar -->
                                <div class="space-y-1.5">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-muted-foreground">Progress</span>
                                        <span class="font-medium">{{ board.completedTickets }}/{{ board.ticketsCount }} tasks</span>
                                    </div>
                                    <div class="h-2 w-full bg-muted rounded-full overflow-hidden">
                                        <div 
                                            class="h-full bg-primary rounded-full" 
                                            :style="{ width: `${getProgressPercentage(board.completedTickets, board.ticketsCount)}%` }"
                                        ></div>
                                    </div>
                                </div>
                            </CardContent>
                            <CardFooter class="pt-2 flex items-center justify-between">
                                <div class="flex -space-x-2">
                                    <Avatar v-for="(member, i) in board.members.slice(0, 3)" :key="i" class="h-7 w-7 border-2 border-background">
                                        <AvatarImage v-if="member.avatar" :src="member.avatar" />
                                        <AvatarFallback>{{ useInitials(member.name) }}</AvatarFallback>
                                    </Avatar>
                                    <div v-if="board.members.length > 3" class="h-7 w-7 rounded-full bg-muted flex items-center justify-center text-xs border-2 border-background">
                                        +{{ board.members.length - 3 }}
                                    </div>
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    Updated {{ formatDate(board.lastUpdated) }}
                                </div>
                            </CardFooter>
                        </Card>
                    </Link>
                </div>
            </div>

            <!-- Empty state -->
            <div v-if="filteredTeams.length === 0" class="flex flex-col items-center justify-center p-12 border rounded-lg border-dashed">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground mb-4"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                <h3 class="text-lg font-medium mb-1">No boards found</h3>
                <p class="text-sm text-muted-foreground mb-4">Try a different search term or filter</p>
                <Button>Create a new board</Button>
            </div>
        </div>
    </AppLayout>
</template>