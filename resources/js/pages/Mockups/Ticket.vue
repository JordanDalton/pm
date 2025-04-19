<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';
import { Dialog, DialogContent, DialogTrigger } from '@/components/ui/dialog';

// Mock data for the ticket
const ticket = {
    id: 'PM-123',
    title: 'Implement AI-powered task prioritization',
    status: 'In Progress',
    priority: 'High',
    assignee: {
        name: 'Jordan Dalton',
        email: 'jordan@example.com',
        avatar: null
    },
    reporter: {
        name: 'Alex Smith',
        email: 'alex@example.com',
        avatar: null
    },
    dueDate: '2025-05-01',
    createdAt: '2025-04-15',
    updatedAt: '2025-04-18',
    description: 'We need to implement an AI-driven task prioritization system that analyzes task dependencies, user workload, and project deadlines to automatically suggest the optimal order for completing tasks.',
    comments: [
        {
            id: 1,
            author: {
                name: 'Alex Smith',
                email: 'alex@example.com',
                avatar: null
            },
            content: 'I\'ve added some initial requirements to the ticket description. Let me know if you need clarification.',
            createdAt: '2025-04-15T14:30:00Z'
        },
        {
            id: 2,
            author: {
                name: 'Jordan Dalton',
                email: 'jordan@example.com',
                avatar: null
            },
            content: 'I\'ve started working on this. I\'m researching some ML models for task prioritization.',
            createdAt: '2025-04-16T09:15:00Z'
        }
    ],
    attachments: [
        {
            id: 1,
            name: 'ai_requirements.pdf',
            size: '2.4 MB',
            uploadedBy: 'Alex Smith',
            createdAt: '2025-04-15T14:35:00Z'
        }
    ],
    subtasks: [
        {
            id: 'PM-124',
            title: 'Research ML models for task prioritization',
            status: 'Done'
        },
        {
            id: 'PM-125',
            title: 'Design API for task priority system',
            status: 'In Progress'
        },
        {
            id: 'PM-126',
            title: 'Implement frontend components',
            status: 'To Do'
        }
    ],
    labels: ['AI', 'Enhancement', 'Backend']
};

// Format date helper
const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

// Breadcrumbs for navigation
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Projects',
        href: '#',
    },
    {
        title: 'Project Management App',
        href: '#',
    },
    {
        title: ticket.id,
        href: '/mockups/ticket',
    },
];
</script>

<template>
    <Head :title="ticket.id + ': ' + ticket.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <!-- Main ticket content -->
            <div class="md:col-span-2">
                <Card>
                    <CardHeader>
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                    <span class="font-medium text-primary">{{ ticket.id }}</span>
                                    <span>·</span>
                                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">{{ ticket.status }}</span>
                                    <span>·</span>
                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">{{ ticket.priority }}</span>
                                </div>
                                <CardTitle class="mt-2 text-xl font-bold">{{ ticket.title }}</CardTitle>
                            </div>
                            <div class="flex gap-2">
                                <Button variant="outline" size="sm">Edit</Button>
                                <Dialog>
                                    <DialogTrigger as-child>
                                        <Button variant="destructive" size="sm">Delete</Button>
                                    </DialogTrigger>
                                    <DialogContent>
                                        <div class="p-4 text-center">
                                            <h2 class="text-lg font-bold">Delete Ticket</h2>
                                            <p class="mt-2">Are you sure you want to delete this ticket? This action cannot be undone.</p>
                                            <div class="flex justify-center gap-2 mt-4">
                                                <Button variant="outline">Cancel</Button>
                                                <Button variant="destructive">Delete</Button>
                                            </div>
                                        </div>
                                    </DialogContent>
                                </Dialog>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="mb-6">
                            <h3 class="mb-2 text-sm font-medium text-muted-foreground">Description</h3>
                            <div class="p-4 rounded-md bg-muted">
                                <p>{{ ticket.description }}</p>
                            </div>
                        </div>

                        <!-- Subtasks section -->
                        <div class="mb-6">
                            <h3 class="mb-2 text-sm font-medium text-muted-foreground">Subtasks</h3>
                            <ul class="space-y-2">
                                <li v-for="subtask in ticket.subtasks" :key="subtask.id" class="flex items-center justify-between p-3 rounded-md border">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-muted-foreground">{{ subtask.id }}</span>
                                        <span>{{ subtask.title }}</span>
                                    </div>
                                    <span class="px-2 py-1 text-xs rounded-full" 
                                        :class="{
                                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': subtask.status === 'Done',
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300': subtask.status === 'In Progress',
                                            'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300': subtask.status === 'To Do'
                                        }">
                                        {{ subtask.status }}
                                    </span>
                                </li>
                            </ul>
                            <Button variant="outline" size="sm" class="mt-2">Add Subtask</Button>
                        </div>

                        <!-- Attachments section -->
                        <div class="mb-6">
                            <h3 class="mb-2 text-sm font-medium text-muted-foreground">Attachments</h3>
                            <ul class="space-y-2">
                                <li v-for="attachment in ticket.attachments" :key="attachment.id" class="flex items-center justify-between p-3 rounded-md border">
                                    <div class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                        <span>{{ attachment.name }}</span>
                                        <span class="text-xs text-muted-foreground">({{ attachment.size }})</span>
                                    </div>
                                    <span class="text-xs text-muted-foreground">{{ formatDate(attachment.createdAt) }}</span>
                                </li>
                            </ul>
                            <Button variant="outline" size="sm" class="mt-2">Add Attachment</Button>
                        </div>

                        <!-- Comments section -->
                        <div>
                            <h3 class="mb-2 text-sm font-medium text-muted-foreground">Comments</h3>
                            <ul class="space-y-4">
                                <li v-for="comment in ticket.comments" :key="comment.id" class="border-b pb-4 last:border-none last:pb-0">
                                    <div class="flex items-start gap-3">
                                        <Avatar class="h-8 w-8">
                                            <AvatarImage v-if="comment.author.avatar" :src="comment.author.avatar" />
                                            <AvatarFallback>{{ useInitials(comment.author.name) }}</AvatarFallback>
                                        </Avatar>
                                        <div class="flex-1">
                                            <div class="flex justify-between">
                                                <div class="font-medium">{{ comment.author.name }}</div>
                                                <div class="text-xs text-muted-foreground">{{ formatDate(comment.createdAt) }}</div>
                                            </div>
                                            <p class="mt-1 text-sm">{{ comment.content }}</p>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                            <div class="mt-4">
                                <textarea class="w-full min-h-[80px] resize-none rounded-md border border-input bg-background p-3 text-sm" placeholder="Add a comment..."></textarea>
                                <div class="mt-2 flex justify-end">
                                    <Button>Comment</Button>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Ticket details sidebar -->
            <div>
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg">Details</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Status</dt>
                                <dd class="mt-1">
                                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">{{ ticket.status }}</span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Priority</dt>
                                <dd class="mt-1">
                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">{{ ticket.priority }}</span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Assignee</dt>
                                <dd class="mt-1 flex items-center gap-2">
                                    <Avatar class="h-6 w-6">
                                        <AvatarImage v-if="ticket.assignee.avatar" :src="ticket.assignee.avatar" />
                                        <AvatarFallback>{{ useInitials(ticket.assignee.name) }}</AvatarFallback>
                                    </Avatar>
                                    <span>{{ ticket.assignee.name }}</span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Reporter</dt>
                                <dd class="mt-1 flex items-center gap-2">
                                    <Avatar class="h-6 w-6">
                                        <AvatarImage v-if="ticket.reporter.avatar" :src="ticket.reporter.avatar" />
                                        <AvatarFallback>{{ useInitials(ticket.reporter.name) }}</AvatarFallback>
                                    </Avatar>
                                    <span>{{ ticket.reporter.name }}</span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Due Date</dt>
                                <dd class="mt-1">{{ formatDate(ticket.dueDate) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Created</dt>
                                <dd class="mt-1">{{ formatDate(ticket.createdAt) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Updated</dt>
                                <dd class="mt-1">{{ formatDate(ticket.updatedAt) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Labels</dt>
                                <dd class="mt-1 flex flex-wrap gap-1">
                                    <span v-for="label in ticket.labels" :key="label" class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">{{ label }}</span>
                                </dd>
                            </div>
                        </dl>
                    </CardContent>
                    <CardFooter>
                        <div class="flex flex-col gap-2 w-full">
                            <Button variant="outline" class="w-full justify-start">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><path d="M12 5l0 14"></path><path d="M18 13l-6 6"></path><path d="M6 13l6 6"></path></svg>
                                Move
                            </Button>
                            <Button variant="outline" class="w-full justify-start">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><path d="M19 7v10c0 2-2 4-4 4H9c-2 0-4-2-4-4V7c0-2 2-4 4-4h2.5l2 2H15c2 0 4 2 4 4Z"></path></svg>
                                Create Subtask
                            </Button>
                            <Button variant="outline" class="w-full justify-start">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><path d="M21 3v5"></path><path d="M3 3v5"></path><path d="M21 16v5"></path><path d="M3 16v5"></path><path d="M8 3h8"></path><path d="M8 21h8"></path><path d="M3 8h18"></path><path d="M3 16h18"></path></svg>
                                Link Issue
                            </Button>
                            <Button variant="default" class="w-full mt-4">
                                Ask AI Assistant
                            </Button>
                        </div>
                    </CardFooter>
                </Card>

                <!-- AI suggestions component -->
                <Card class="mt-4">
                    <CardHeader>
                        <CardTitle class="text-lg">AI Suggestions</CardTitle>
                        <CardDescription>AI-powered insights for this task</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3 text-sm">
                            <div class="p-3 rounded-md bg-blue-50 dark:bg-blue-950 border border-blue-200 dark:border-blue-800">
                                <div class="font-medium text-blue-700 dark:text-blue-300 flex items-center gap-1 mb-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 16v-4"></path><path d="M12 8h.01"></path></svg>
                                    Dependency Alert
                                </div>
                                <p class="text-blue-700 dark:text-blue-300">This task may depend on PM-119 (Database Schema Updates) which is still in progress.</p>
                            </div>
                            <div class="p-3 rounded-md bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-800">
                                <div class="font-medium text-green-700 dark:text-green-300 flex items-center gap-1 mb-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v4"></path><path d="M12 18v4"></path><path d="M4.93 4.93l2.83 2.83"></path><path d="M16.24 16.24l2.83 2.83"></path><path d="M2 12h4"></path><path d="M18 12h4"></path><path d="M4.93 19.07l2.83-2.83"></path><path d="M16.24 7.76l2.83-2.83"></path></svg>
                                    Suggested Resource
                                </div>
                                <p class="text-green-700 dark:text-green-300">Similar functionality was implemented by Alex Smith in project "Task Manager Pro" (see PR #142).</p>
                            </div>
                            <div class="p-3 rounded-md bg-amber-50 dark:bg-amber-950 border border-amber-200 dark:border-amber-800">
                                <div class="font-medium text-amber-700 dark:text-amber-300 flex items-center gap-1 mb-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z"></path></svg>
                                    Time Estimate
                                </div>
                                <p class="text-amber-700 dark:text-amber-300">Based on similar tasks, this might take 3-5 days for implementation and testing.</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>