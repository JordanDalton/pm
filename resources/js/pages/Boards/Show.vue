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

// Props
const props = defineProps({
    boardId: {
        type: String,
        required: true
    },
    board: {
        type: Object,
        default: null
    },
    aiInsights: {
        type: String,
        default: null
    }
});

// Use either the provided board data or fallback to mock data
const boardData = computed(() => {
    if (props.board) {
        return props.board;
    }
    
    // Mock data for the board as fallback
    return {
        id: props.boardId,
        name: 'Frontend Development',
        description: 'UI/UX implementation and frontend features',
        status: 'active',
        createdAt: '2025-04-15',
        updatedAt: '2025-04-18',
        members: [
            { name: 'Jordan Dalton', email: 'jordan@example.com', avatar: null },
            { name: 'Alex Smith', email: 'alex@example.com', avatar: null },
            { name: 'Taylor Morgan', email: 'taylor@example.com', avatar: null }
        ],
        tasks: []
    };
});

// Transform tasks into kanban-style columns
const columns = computed(() => {
    const columnDefinitions = [
        { id: 'todo', name: 'To Do', status: 'todo' },
        { id: 'in_progress', name: 'In Progress', status: 'in_progress' },
        { id: 'review', name: 'Review', status: 'review' },
        { id: 'done', name: 'Done', status: 'done' }
    ];
    
    // If we have real board data with tasks
    if (props.board && props.board.tasks) {
        return columnDefinitions.map(column => {
            const tasksInColumn = props.board.tasks.filter(task => 
                task.status.toLowerCase().replace(' ', '_') === column.status
            );
            
            return {
                ...column,
                tasks: tasksInColumn.map(task => ({
                    id: task.id,
                    title: task.title,
                    description: task.description,
                    priority: task.priority,
                    status: task.status,
                    assignee: {
                        name: task.user ? task.user.name : 'Unassigned',
                        email: task.user ? task.user.email : '',
                        avatar: null
                    },
                    dueDate: task.due_date,
                    labels: ['Task'] // Placeholder, would need proper labels in the model
                }))
            };
        });
    }
    
    // Fallback mock data if no real data is available
    return [
        {
            id: 'todo',
            name: 'To Do',
            tasks: [
                {
                    id: 'PM-124',
                    title: 'Design user dashboard with analytics',
                    description: 'Create wireframes and UI mockups for the main user dashboard with analytics visualizations.',
                    priority: 'Medium',
                    assignee: {
                        name: 'Alex Smith',
                        email: 'alex@example.com',
                        avatar: null
                    },
                    dueDate: '2025-05-10',
                    labels: ['UI/UX', 'Frontend']
                },
                {
                    id: 'PM-128',
                    title: 'Implement drag-and-drop for task board',
                    description: 'Add drag-and-drop functionality to allow users to move tasks between columns.',
                    priority: 'Medium',
                    assignee: {
                        name: 'Jordan Dalton',
                        email: 'jordan@example.com',
                        avatar: null
                    },
                    dueDate: '2025-05-20',
                    labels: ['Enhancement', 'Frontend', 'UX']
                }
            ]
        },
        {
            id: 'in_progress',
            name: 'In Progress',
            tasks: [
                {
                    id: 'PM-123',
                    title: 'Implement AI-powered task prioritization',
                    description: 'We need to implement an AI-driven task prioritization system that analyzes task dependencies, user workload, and project deadlines to automatically suggest the optimal order for completing tasks.',
                    priority: 'High',
                    assignee: {
                        name: 'Jordan Dalton',
                        email: 'jordan@example.com',
                        avatar: null
                    },
                    dueDate: '2025-05-01',
                    labels: ['AI', 'Enhancement', 'Backend']
                }
            ]
        },
        {
            id: 'review',
            name: 'Review',
            tasks: [
                {
                    id: 'PM-120',
                    title: 'Review login page redesign',
                    description: 'Review the new login page design and provide feedback before implementation.',
                    priority: 'Medium',
                    assignee: {
                        name: 'Taylor Morgan',
                        email: 'taylor@example.com',
                        avatar: null
                    },
                    dueDate: '2025-04-20',
                    labels: ['UI/UX', 'Review']
                }
            ]
        },
        {
            id: 'done',
            name: 'Done',
            tasks: [
                {
                    id: 'PM-126',
                    title: 'Add email notification system',
                    description: 'Implement an email notification system for task assignments and updates.',
                    priority: 'Low',
                    assignee: {
                        name: 'Taylor Morgan',
                        email: 'taylor@example.com',
                        avatar: null
                    },
                    dueDate: '2025-04-25',
                    labels: ['Backend', 'Enhancement']
                }
            ]
        }
    ];
});

// Search filter
const searchQuery = ref('');

// Filtered tasks
const filteredColumns = computed(() => {
    if (!searchQuery.value) {
        return columns.value;
    }
    
    const query = searchQuery.value.toLowerCase();
    
    return columns.value.map(column => {
        const filteredTasks = column.tasks.filter(task => 
            task.title.toLowerCase().includes(query) || 
            (task.id && task.id.toString().toLowerCase().includes(query)) ||
            (task.description && task.description.toLowerCase().includes(query)) ||
            (task.assignee && task.assignee.name && task.assignee.name.toLowerCase().includes(query)) ||
            (task.labels && task.labels.some(label => label.toLowerCase().includes(query)))
        );
        
        return {
            ...column,
            tasks: filteredTasks
        };
    });
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
    {
        title: boardData.value.name,
        href: route('boards.show', boardData.value.id),
    },
];
</script>

<template>
    <Head :title="boardData.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6">
            <!-- Board header section -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ boardData.name }}</h1>
                    <p class="text-muted-foreground">{{ boardData.description }}</p>
                </div>
                <div class="flex gap-2">
                    <Dialog>
                        <DialogTrigger as-child>
                            <Button variant="outline">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path><path d="m15 5 4 4"></path></svg>
                                Edit Board
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <div class="p-4">
                                <h2 class="text-lg font-bold">Edit Board</h2>
                                <p class="text-sm text-muted-foreground">Update this board's details</p>
                                <div class="mt-4">
                                    <!-- Simple form placeholder -->
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Board Name</label>
                                            <input type="text" class="w-full rounded-md border p-2 text-sm" :value="board.name" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Description</label>
                                            <textarea class="w-full rounded-md border p-2 text-sm min-h-[80px]">{{ board.description }}</textarea>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Status</label>
                                            <select class="w-full rounded-md border p-2 text-sm">
                                                <option value="active" :selected="board.status === 'active'">Active</option>
                                                <option value="archived" :selected="board.status === 'archived'">Archived</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mt-6 flex justify-end gap-2">
                                        <Button variant="outline">Cancel</Button>
                                        <Button>Save Changes</Button>
                                    </div>
                                </div>
                            </div>
                        </DialogContent>
                    </Dialog>
                    
                    <Dialog>
                        <DialogTrigger as-child>
                            <Button>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                New Task
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <div class="p-4">
                                <h2 class="text-lg font-bold">Create New Task</h2>
                                <p class="text-sm text-muted-foreground">Fill in the details to create a new task</p>
                                <div class="mt-4">
                                    <!-- Simple form placeholder -->
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Title</label>
                                            <input type="text" class="w-full rounded-md border p-2 text-sm" placeholder="Enter task title" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Description</label>
                                            <textarea class="w-full rounded-md border p-2 text-sm min-h-[100px]" placeholder="Enter task description"></textarea>
                                        </div>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-sm font-medium mb-1">Status</label>
                                                <select class="w-full rounded-md border p-2 text-sm">
                                                    <option v-for="column in board.columns" :key="column.id" :value="column.id">
                                                        {{ column.name }}
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
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-sm font-medium mb-1">Assignee</label>
                                                <select class="w-full rounded-md border p-2 text-sm">
                                                    <option v-for="member in board.members" :key="member.email" :value="member.email">
                                                        {{ member.name }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium mb-1">Due Date</label>
                                                <input type="date" class="w-full rounded-md border p-2 text-sm" />
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Labels</label>
                                            <input type="text" class="w-full rounded-md border p-2 text-sm" placeholder="Separate labels with commas" />
                                        </div>
                                    </div>
                                    <div class="mt-6 flex justify-end gap-2">
                                        <Button variant="outline">Cancel</Button>
                                        <Button>Create Task</Button>
                                    </div>
                                </div>
                            </div>
                        </DialogContent>
                    </Dialog>
                </div>
            </div>

            <!-- Board info card -->
            <Card>
                <CardContent class="p-4">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <div class="text-sm font-medium text-muted-foreground">Total Tasks</div>
                                <div class="mt-1 text-2xl font-bold">
                                    {{ columns.reduce((acc, col) => acc + col.tasks.length, 0) }}
                                </div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-muted-foreground">Completed</div>
                                <div class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400">
                                    {{ columns.find(col => col.id === 'done')?.tasks.length || 0 }}
                                </div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-muted-foreground">In Progress</div>
                                <div class="mt-1 text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                                    {{ columns.find(col => col.id === 'in_progress')?.tasks.length || 0 }}
                                </div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-muted-foreground">Team Members</div>
                                <div class="mt-1 flex -space-x-2">
                                    <Avatar v-for="(member, i) in boardData.members?.slice(0, 4) || []" :key="i" class="h-8 w-8 border-2 border-background">
                                        <AvatarImage v-if="member.avatar" :src="member.avatar" />
                                        <AvatarFallback>{{ useInitials(member.name) }}</AvatarFallback>
                                    </Avatar>
                                    <div v-if="boardData.members?.length > 4" class="h-8 w-8 rounded-full bg-muted flex items-center justify-center text-xs border-2 border-background">
                                        +{{ boardData.members.length - 4 }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Search input -->
                        <div class="w-full md:w-64">
                            <input 
                                type="text" 
                                v-model="searchQuery"
                                class="w-full rounded-md border p-2 text-sm" 
                                placeholder="Search tasks" 
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>
            
            <!-- AI Insights Card - Only show if insights are available -->
            <Card v-if="aiInsights" class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950 dark:to-indigo-950 border border-blue-100 dark:border-blue-900">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600 dark:text-blue-300">
                            <path d="M21 12a9 9 0 1 1-9-9 8.997 8.997 0 0 1 8.485 6"></path>
                            <path d="M14.943 11.943 21 12"></path>
                            <path d="M9.002 16c.855.74 2.053.93 3.122.494 1.07-.434 1.796-1.426 1.879-2.554"></path>
                            <rect x="10" y="8" width="0.01" height="0.01"></rect>
                            <rect x="14" y="8" width="0.01" height="0.01"></rect>
                        </svg>
                        AI Project Assistant
                    </CardTitle>
                    <CardDescription class="text-blue-600 dark:text-blue-400">
                        AI-powered insights and recommendations for this board
                    </CardDescription>
                </CardHeader>
                <CardContent class="pt-0">
                    <div class="markdown-content text-blue-700 dark:text-blue-300 prose prose-sm max-w-none" v-html="aiInsights"></div>
                </CardContent>
            </Card>

            <!-- Kanban board -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div v-for="column in filteredColumns" :key="column.id" class="flex flex-col gap-2">
                    <div class="flex items-center justify-between px-4 py-2 bg-muted rounded-md font-medium">
                        <div class="flex items-center gap-2">
                            <span>{{ column.name }}</span>
                            <span class="px-1.5 py-0.5 text-xs rounded-full bg-muted-foreground/20 text-muted-foreground">
                                {{ column.tasks.length }}
                            </span>
                        </div>
                        <Button variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </Button>
                    </div>
                    
                    <div class="flex flex-col gap-2 min-h-[150px]">
                        <Link 
                            v-for="task in column.tasks" 
                            :key="task.id" 
                            :href="route('tickets.show', task.id)"
                            class="block"
                        >
                            <Card class="shadow-sm hover:shadow transition-shadow">
                                <CardContent class="p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-primary">{{ task.id }}</span>
                                        <span class="px-2 py-1 text-xs rounded-full" :class="getPriorityClass(task.priority)">
                                            {{ task.priority }}
                                        </span>
                                    </div>
                                    <h3 class="font-medium mb-2">{{ task.title }}</h3>
                                    <p class="text-sm text-muted-foreground line-clamp-2 mb-3">{{ task.description }}</p>
                                    
                                    <div class="flex items-center justify-between text-sm text-muted-foreground">
                                        <div class="flex items-center gap-2">
                                            <Avatar class="h-5 w-5">
                                                <AvatarImage v-if="task.assignee.avatar" :src="task.assignee.avatar" />
                                                <AvatarFallback>{{ useInitials(task.assignee.name) }}</AvatarFallback>
                                            </Avatar>
                                            <span class="text-xs">{{ task.assignee.name }}</span>
                                        </div>
                                        <span class="text-xs">{{ formatDate(task.dueDate) }}</span>
                                    </div>
                                    
                                    <div class="mt-3 flex flex-wrap gap-1">
                                        <span v-for="label in task.labels.slice(0, 2)" :key="label" class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                            {{ label }}
                                        </span>
                                        <span v-if="task.labels.length > 2" class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                            +{{ task.labels.length - 2 }}
                                        </span>
                                    </div>
                                </CardContent>
                            </Card>
                        </Link>
                        
                        <div v-if="column.tasks.length === 0" class="p-4 text-center text-sm text-muted-foreground border border-dashed rounded-md">
                            No tasks in this column
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>