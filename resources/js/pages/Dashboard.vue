<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import AuthService from '@/services/AuthService';

// Determine if running in development mode
const isDevelopment = process.env.NODE_ENV === 'development';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

// AI chat interface
const messages = ref([
    {
        role: 'assistant',
        content: 'Hello! I\'m your AI assistant. How can I help you manage your projects today?',
        timestamp: new Date().toISOString()
    }
]);

const exampleMessages = [
    'Show me high priority tickets in the Frontend Development board',
    'Create a new task for implementing user authentication',
    'Summarize the progress on the Marketing Campaign',
    'What tasks are assigned to Jordan?',
    'Schedule a meeting with the design team for tomorrow',
    'Generate a project status report for this week'
];

const userInput = ref('');
const isProcessing = ref(false);
const chatContainerRef = ref(null);

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
};

// We'll define sendMessage after enhancedSendMessage is created
let sendMessage;

const scrollToBottom = () => {
    setTimeout(() => {
        if (chatContainerRef.value) {
            chatContainerRef.value.scrollTop = chatContainerRef.value.scrollHeight;
        }
    }, 100);
};

const handleExampleClick = (example) => {
    userInput.value = example;
    sendMessage();
};

// Mock data for dashboard summary
const summaryData = {
    tasks: {
        total: 53,
        completed: 25,
        overdue: 3
    },
    recentActivity: [
        {
            type: 'ticket_created',
            user: 'Alex Smith',
            details: 'Created ticket PM-131',
            title: 'Optimize database queries for performance',
            timestamp: '2025-04-19T10:15:00Z'
        },
        {
            type: 'ticket_updated',
            user: 'Jordan Dalton',
            details: 'Changed status of PM-123 from "To Do" to "In Progress"',
            title: 'Implement AI-powered task prioritization',
            timestamp: '2025-04-19T09:30:00Z'
        },
        {
            type: 'comment_added',
            user: 'Taylor Morgan',
            details: 'Commented on PM-126',
            title: 'Add email notification system',
            timestamp: '2025-04-18T16:45:00Z'
        }
    ],
    upcomingDeadlines: [
        {
            id: 'PM-127',
            title: 'Fix search functionality in project view',
            dueDate: '2025-04-22',
            priority: 'High',
            board: 'Frontend Development'
        },
        {
            id: 'PM-130',
            title: 'Implement user authentication with SSO',
            dueDate: '2025-04-30',
            priority: 'High',
            board: 'Backend Development'
        }
    ]
};

// Format date for activity feed
const formatActivityDate = (dateString) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffHours = Math.floor((now - date) / (1000 * 60 * 60));
    
    if (diffHours < 1) {
        return 'Just now';
    } else if (diffHours < 24) {
        return `${diffHours} ${diffHours === 1 ? 'hour' : 'hours'} ago`;
    } else {
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
    }
};

// Get task progress percentage
const taskProgressPercentage = computed(() => {
    return Math.round((summaryData.tasks.completed / summaryData.tasks.total) * 100);
});

// Real-time updates simulation
const hasNotifications = ref(false);
const notificationCount = ref(0);
const recentNotifications = ref([]);
const showNotificationPanel = ref(false);

// Polling for real-time updates (simulated)
let pollingInterval = null;

// Live preview overlay
const showPreview = ref(false);
const previewType = ref('');
const previewContent = ref(null);

// AI action confirmation panel
const showActionConfirmation = ref(false);
const pendingAction = ref(null);

// Ticket edit modal
const showTicketEditModal = ref(false);
const editableTicket = ref(null);

// Available statuses and assignees for ticket form
const allStatuses = ['Backlog', 'To Do', 'In Progress', 'QA/Testing', 'Done'];
const assignees = ['All', 'Jordan Dalton', 'Alex Smith', 'Taylor Morgan', 'Emily Davis', 'Michael Brown'];

// Dashboard customization
const showCustomizePanel = ref(false);
const availableWidgets = [
  { id: 'tasks-overview', title: 'Tasks Overview', enabled: true, column: 1, order: 1 },
  { id: 'team-boards', title: 'Team Boards', enabled: true, column: 1, order: 2 },
  { id: 'recent-activity', title: 'Recent Activity', enabled: true, column: 1, order: 3 },
  { id: 'upcoming-deadlines', title: 'Upcoming Deadlines', enabled: true, column: 1, order: 4 },
  { id: 'my-tasks', title: 'My Tasks', enabled: false, column: 1, order: 5 },
  { id: 'time-tracking', title: 'Time Tracking', enabled: false, column: 1, order: 6 },
  { id: 'team-workload', title: 'Team Workload', enabled: false, column: 1, order: 7 },
  { id: 'project-milestones', title: 'Project Milestones', enabled: false, column: 1, order: 8 },
  { id: 'quick-actions', title: 'Quick Actions', enabled: false, column: 1, order: 9 }
];

const userWidgets = ref(JSON.parse(JSON.stringify(availableWidgets)));
const dashboardLayout = ref({
  columnCount: 2,
  showAI: true
});

// Save user dashboard preferences
const saveDashboardPreferences = () => {
  // In a real app, this would be saved to user preferences in the database
  localStorage.setItem('dashboard_widgets', JSON.stringify(userWidgets));
  localStorage.setItem('dashboard_layout', JSON.stringify(dashboardLayout));
  showCustomizePanel.value = false;
  
  // Show a notification to confirm save
  recentNotifications.value.unshift({
    type: 'task_update',
    title: 'Dashboard Customized',
    message: 'Your dashboard preferences have been saved',
    time: new Date().toISOString()
  });
  notificationCount.value += 1;
  hasNotifications.value = true;
};

// Load user preferences on mount
onMounted(() => {
  scrollToBottom();
  
  // Load saved dashboard preferences if they exist
  const savedWidgets = localStorage.getItem('dashboard_widgets');
  const savedLayout = localStorage.getItem('dashboard_layout');
  
  if (savedWidgets) {
    Object.assign(userWidgets, JSON.parse(savedWidgets));
  }
  
  if (savedLayout) {
    Object.assign(dashboardLayout, JSON.parse(savedLayout));
  }
  
  // Set up polling for real-time updates
  pollingInterval = setInterval(() => {
    // 20% chance of new notification every 20 seconds (for demo purposes)
    if (Math.random() < 0.2) {
      simulateNewNotification();
    }
  }, 20000);
});

// Demo for real-time notification
const simulateNewNotification = () => {
    const notificationTypes = [
        {
            type: 'task_update',
            title: 'Task Status Updated',
            message: 'PM-127 moved to "QA/Testing" by Alex Smith',
            time: new Date().toISOString()
        },
        {
            type: 'mention',
            title: 'You were mentioned',
            message: 'Jordan mentioned you in a comment on PM-123',
            time: new Date().toISOString()
        },
        {
            type: 'deadline',
            title: 'Upcoming Deadline',
            message: 'PM-130 is due in 2 days',
            time: new Date().toISOString()
        },
        {
            type: 'comment',
            title: 'New Comment',
            message: 'Taylor left a comment on PM-126',
            time: new Date().toISOString() 
        }
    ];
    
    // Randomly select a notification type
    const randomNotification = notificationTypes[Math.floor(Math.random() * notificationTypes.length)];
    
    // Add to notifications
    recentNotifications.value.unshift(randomNotification);
    if (recentNotifications.value.length > 5) {
        recentNotifications.value.pop();
    }
    
    notificationCount.value += 1;
    hasNotifications.value = true;
    
    // Update summaryData to reflect changes (for demo purposes)
    if (randomNotification.type === 'task_update') {
        // Find the ticket in our data and update its status
        const ticketIndex = summaryData.recentActivity.findIndex(item => 
            item.details && item.details.includes('PM-127')
        );
        
        if (ticketIndex !== -1) {
            summaryData.recentActivity.unshift({
                type: 'ticket_updated',
                user: 'Alex Smith',
                details: 'Changed status of PM-127 to "QA/Testing"',
                title: 'Fix search functionality in project view',
                timestamp: new Date().toISOString()
            });
            
            if (summaryData.recentActivity.length > 3) {
                summaryData.recentActivity.pop();
            }
        }
    }
};

// Preview functionality for AI interactions
const showContentPreview = (type, content) => {
    previewType.value = type;
    previewContent.value = content;
    showPreview.value = true;
};

// Handle AI actions that require confirmation
const handleActionConfirmation = (action) => {
    pendingAction.value = action;
    showActionConfirmation.value = true;
};

// Enhanced AI response that includes callbacks for actions
const enhancedSendMessage = () => {
    if (!userInput.value.trim() || isProcessing.value) return;
    
    // Add user message
    messages.value.push({
        role: 'user',
        content: userInput.value,
        timestamp: new Date().toISOString()
    });
    
    // Clear input and show processing
    const userQuery = userInput.value;
    userInput.value = '';
    isProcessing.value = true;
    
    // Simulate AI response with interactive elements
    setTimeout(() => {
        let response;
        let shouldShowPreview = false;
        let previewData = null;
        let requiresConfirmation = false;
        let actionData = null;
        
        if (userQuery.toLowerCase().includes('high priority')) {
            response = "I found 3 high priority tickets in the Frontend Development board:\n\n1. PM-123: Implement AI-powered task prioritization\n2. PM-127: Fix search functionality in project view\n3. PM-130: Implement user authentication with SSO\n\nWould you like me to show details for any of these tickets?";
            
            shouldShowPreview = true;
            previewData = {
                type: 'ticket-list',
                content: [
                    {
                        id: 'PM-123',
                        title: 'Implement AI-powered task prioritization',
                        status: 'In Progress',
                        priority: 'High',
                        assignee: 'Jordan Dalton'
                    },
                    {
                        id: 'PM-127',
                        title: 'Fix search functionality in project view',
                        status: 'QA/Testing',
                        priority: 'High',
                        assignee: 'Alex Smith'
                    },
                    {
                        id: 'PM-130',
                        title: 'Implement user authentication with SSO',
                        status: 'In Progress',
                        priority: 'High',
                        assignee: 'Jordan Dalton'
                    }
                ]
            };
        } else if (userQuery.toLowerCase().includes('create') && userQuery.toLowerCase().includes('task')) {
            response = "I've created a new task for implementing user authentication:\n\nPM-133: Implement user authentication\nStatus: To Do\nPriority: High\nAssignee: Unassigned\n\nWould you like to assign this to someone on the team?";
            
            requiresConfirmation = true;
            actionData = {
                type: 'create-task',
                title: 'Implement user authentication',
                id: 'PM-133',
                status: 'To Do',
                priority: 'High'
            };
        } else if (userQuery.toLowerCase().includes('marketing campaign')) {
            response = "The Marketing Campaign board is 53% complete (8/15 tasks finished). Recent completions include social media content calendar and brand guidelines update. There are 3 tasks currently in progress, with 4 remaining in the To Do status.";
            
            shouldShowPreview = true;
            previewData = {
                type: 'board-summary',
                content: {
                    name: 'Marketing Campaign',
                    progress: 53,
                    completed: 8,
                    total: 15,
                    byStatus: [
                        { status: 'Done', count: 8 },
                        { status: 'In Progress', count: 3 },
                        { status: 'To Do', count: 4 }
                    ]
                }
            };
        } else if (userQuery.toLowerCase().includes('jordan')) {
            response = "Jordan Dalton has 3 tasks assigned:\n\n1. [In Progress] PM-123: Implement AI-powered task prioritization\n2. [Backlog] PM-125: Implement team collaboration features\n3. [In Progress] PM-130: Implement user authentication with SSO\n\nJordan also has 2 tasks completed in the last 7 days.";
            
            shouldShowPreview = true;
            previewData = {
                type: 'user-tasks',
                content: {
                    user: 'Jordan Dalton',
                    assigned: [
                        {
                            id: 'PM-123',
                            title: 'Implement AI-powered task prioritization',
                            status: 'In Progress'
                        },
                        {
                            id: 'PM-125',
                            title: 'Implement team collaboration features',
                            status: 'Backlog'
                        },
                        {
                            id: 'PM-130',
                            title: 'Implement user authentication with SSO',
                            status: 'In Progress'
                        }
                    ],
                    recentlyCompleted: 2
                }
            };
        } else if (userQuery.toLowerCase().includes('meeting') || userQuery.toLowerCase().includes('schedule')) {
            response = "I've scheduled a design team meeting for tomorrow at 10:00 AM. I've invited all 3 members of the design team and added it to your calendar. Would you like to add an agenda or specific topics to discuss?";
            
            requiresConfirmation = true;
            actionData = {
                type: 'schedule-meeting',
                title: 'Design Team Meeting',
                time: '2025-04-20T10:00:00Z',
                attendees: ['Emily Davis', 'Michael Brown', 'Jordan Dalton']
            };
        } else if (userQuery.toLowerCase().includes('status') || userQuery.toLowerCase().includes('report')) {
            response = "I've generated a project status report for this week. Here's a summary:\n\n- 12 tasks completed\n- 15 tasks in progress\n- 8 new tasks created\n- Overall project completion: 47%\n\nTop contributors: Jordan (7 tasks), Alex (5 tasks), Taylor (4 tasks)\n\nWould you like me to send this report to the team or export it as a PDF?";
            
            shouldShowPreview = true;
            previewData = {
                type: 'report',
                content: {
                    period: 'This Week',
                    completion: 47,
                    tasks: {
                        completed: 12,
                        inProgress: 15,
                        created: 8
                    },
                    contributors: [
                        { name: 'Jordan Dalton', count: 7 },
                        { name: 'Alex Smith', count: 5 },
                        { name: 'Taylor Morgan', count: 4 }
                    ]
                }
            };
        } else {
            response = "I'll help you with that. Let me analyze the information across your projects and get back to you with the most relevant details.";
        }
        
        // Add AI response
        messages.value.push({
            role: 'assistant',
            content: response,
            timestamp: new Date().toISOString()
        });
        
        isProcessing.value = false;
        
        // Handle interactive elements
        if (shouldShowPreview) {
            setTimeout(() => {
                showContentPreview(previewData.type, previewData.content);
            }, 500);
        }
        
        if (requiresConfirmation) {
            setTimeout(() => {
                handleActionConfirmation(actionData);
            }, 1000);
        }
        
        // Scroll to bottom
        scrollToBottom();
    }, 1500);
};

// Now assign enhancedSendMessage to sendMessage
sendMessage = enhancedSendMessage;

// Simulate periodic notifications
onMounted(() => {
    scrollToBottom();
    
    // Set up polling for real-time updates
    pollingInterval = setInterval(() => {
        // 20% chance of new notification every 20 seconds (for demo purposes)
        if (Math.random() < 0.2) {
            simulateNewNotification();
        }
    }, 20000);
});

// Clear polling interval on component unmount
onUnmounted(() => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold tracking-tight">Project Dashboard</h1>
                
                <div class="flex items-center gap-2">
                    <!-- Customize Dashboard Button -->
                    <TooltipProvider>
                        <Tooltip>
                            <TooltipTrigger asChild>
                                <Button 
                                    variant="ghost" 
                                    size="sm" 
                                    class="h-10 w-10 p-0 relative" 
                                    @click="showCustomizePanel = true"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>Customize Dashboard</p>
                            </TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                
                    <!-- Notification icon with badge -->
                    <TooltipProvider>
                        <Tooltip>
                            <TooltipTrigger asChild>
                                <Button 
                                    variant="ghost" 
                                    size="sm" 
                                    class="h-10 w-10 p-0 relative" 
                                    @click="showNotificationPanel = !showNotificationPanel"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path></svg>
                                    <span v-if="hasNotifications" class="absolute top-0 right-0 h-4 w-4 rounded-full bg-red-500 text-[10px] font-bold flex items-center justify-center text-white">
                                        {{ notificationCount > 9 ? '9+' : notificationCount }}
                                    </span>
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>Notifications</p>
                            </TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                    
                    <!-- Notification panel -->
                    <div 
                        v-if="showNotificationPanel" 
                        class="absolute right-0 top-12 w-80 z-50 bg-background rounded-md border shadow-md overflow-hidden"
                    >
                        <div class="p-3 border-b flex items-center justify-between">
                            <div class="font-semibold">Notifications</div>
                            <div>
                                <Button 
                                    variant="ghost" 
                                    size="sm" 
                                    class="h-6 w-6 p-0"
                                    @click="notificationCount = 0; hasNotifications = false;"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
                                </Button>
                            </div>
                        </div>
                        <div class="max-h-96 overflow-y-auto">
                            <div v-if="recentNotifications.length === 0" class="p-4 text-center text-sm text-muted-foreground">
                                No new notifications
                            </div>
                            <div v-else class="p-1">
                                <div 
                                    v-for="(notif, i) in recentNotifications" 
                                    :key="i" 
                                    class="p-2 hover:bg-muted rounded-md mb-1 cursor-pointer"
                                >
                                    <div class="font-medium text-sm flex items-center gap-2">
                                        <svg v-if="notif.type === 'task_update'" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"></path><path d="M15 8h2v2"></path><path d="M9 8H7V6"></path><path d="m11.5 12.5 2-2"></path></svg>
                                        <svg v-if="notif.type === 'mention'" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-500"><circle cx="12" cy="12" r="10"></circle><path d="M8 14s1.5 2 4 2 4-2 4-2"></path><line x1="9" y1="9" x2="9.01" y2="9"></line><line x1="15" y1="9" x2="15.01" y2="9"></line></svg>
                                        <svg v-if="notif.type === 'deadline'" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-orange-500"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                        <svg v-if="notif.type === 'comment'" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-purple-500"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                                        {{ notif.title }}
                                    </div>
                                    <div class="text-xs text-muted-foreground mt-1">{{ notif.message }}</div>
                                    <div class="text-xs text-muted-foreground mt-1">{{ formatDate(notif.time) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="p-2 border-t">
                            <Button variant="outline" size="sm" class="w-full text-xs">View All Notifications</Button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div :class="`grid gap-6 ${dashboardLayout.columnCount === 3 ? 'md:grid-cols-3' : dashboardLayout.columnCount === 1 ? '' : 'md:grid-cols-2'}`">
                <!-- API Token Status Card (for developers) -->
                <Card v-if="isDevelopment" class="mb-4">
                    <CardHeader>
                        <CardTitle>Developer Tools</CardTitle>
                        <CardDescription>API authentication status for development purposes</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-sm font-medium mb-1">API Token Status</h3>
                                <div class="flex items-center gap-2">
                                    <div class="h-3 w-3 rounded-full" :class="AuthService.isAuthenticated() ? 'bg-green-500' : 'bg-red-500'"></div>
                                    <span>{{ AuthService.isAuthenticated() ? 'Authenticated' : 'Not authenticated' }}</span>
                                </div>
                            </div>
                            <div v-if="AuthService.isAuthenticated()">
                                <h3 class="text-sm font-medium mb-1">User</h3>
                                <div class="text-sm">{{ AuthService.getCurrentUser()?.name || 'Unknown' }}</div>
                            </div>
                            <div class="flex gap-2">
                                <Button size="sm" @click="async () => {
                                    const userData = await AuthService.getUser();
                                    if (userData && userData.user) {
                                        AuthService.storeUser(userData.user);
                                    }
                                }">
                                    Fetch User Data
                                </Button>
                                <Button size="sm" variant="destructive" @click="AuthService.clearAuth()">
                                    Clear User Data
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                
                <!-- Main Content Cards -->
                <div :class="`${dashboardLayout.showAI ? (dashboardLayout.columnCount === 3 ? 'col-span-2' : '') : 'col-span-full'} grid gap-6 md:grid-cols-${dashboardLayout.columnCount === 1 ? '1' : '2'} auto-rows-min`">
                    
                    <!-- Tasks Overview Card -->
                    <Card v-if="userWidgets.find(w => w.id === 'tasks-overview')?.enabled" :class="{'md:col-span-2': userWidgets.find(w => w.id === 'tasks-overview')?.order % 2 === 0}">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-base">Tasks Overview</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold mb-2">{{ summaryData.tasks.completed }}/{{ summaryData.tasks.total }}</div>
                            <div class="space-y-1.5">
                                <div class="h-2 w-full bg-muted rounded-full overflow-hidden">
                                    <div class="h-full bg-primary rounded-full" :style="{ width: `${taskProgressPercentage}%` }"></div>
                                </div>
                                <div class="flex items-center justify-between text-xs text-muted-foreground">
                                    <span>{{ taskProgressPercentage }}% Complete</span>
                                    <span>{{ summaryData.tasks.overdue }} Overdue</span>
                                </div>
                            </div>
                        </CardContent>
                        <CardFooter class="pt-0">
                            <Button variant="outline" size="sm" class="w-full" as-child>
                                <Link :href="route('mockups.tickets')">
                                    View All Tasks
                                </Link>
                            </Button>
                        </CardFooter>
                    </Card>
                    
                    <!-- Team Boards Card -->
                    <Card v-if="userWidgets.find(w => w.id === 'team-boards')?.enabled" :class="{'md:col-span-2': userWidgets.find(w => w.id === 'team-boards')?.order % 2 === 0}">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-base">Team Boards</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-2">
                                <Link :href="route('mockups.board.detail', { id: 'eng-frontend' })" class="flex items-center justify-between p-2 rounded-md hover:bg-muted">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                        <span>Frontend Development</span>
                                    </div>
                                    <span class="text-sm text-muted-foreground">58% Complete</span>
                                </Link>
                                <Link :href="route('mockups.board.detail', { id: 'eng-backend' })" class="flex items-center justify-between p-2 rounded-md hover:bg-muted">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                        <span>Backend Development</span>
                                    </div>
                                    <span class="text-sm text-muted-foreground">56% Complete</span>
                                </Link>
                                <Link :href="route('mockups.board.detail', { id: 'mkt-campaigns' })" class="flex items-center justify-between p-2 rounded-md hover:bg-muted">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full bg-purple-500"></div>
                                        <span>Marketing Campaigns</span>
                                    </div>
                                    <span class="text-sm text-muted-foreground">53% Complete</span>
                                </Link>
                            </div>
                        </CardContent>
                        <CardFooter class="pt-0">
                            <Button variant="outline" size="sm" class="w-full" as-child>
                                <Link :href="route('mockups.boards')">
                                    View All Boards
                                </Link>
                            </Button>
                        </CardFooter>
                    </Card>
                    
                    <!-- Recent Activity Card -->
                    <Card v-if="userWidgets.find(w => w.id === 'recent-activity')?.enabled" :class="{'md:col-span-2': userWidgets.find(w => w.id === 'recent-activity')?.order % 2 === 0}">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-base">Recent Activity</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div v-for="(activity, i) in summaryData.recentActivity" :key="i" class="flex gap-3">
                                    <Avatar class="h-8 w-8">
                                        <AvatarFallback>{{ useInitials(activity.user) }}</AvatarFallback>
                                    </Avatar>
                                    <div class="flex-1 space-y-1">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-medium">{{ activity.user }}</p>
                                            <span class="text-xs text-muted-foreground">{{ formatActivityDate(activity.timestamp) }}</span>
                                        </div>
                                        <p class="text-sm text-muted-foreground">{{ activity.details }}</p>
                                        <p class="text-xs">{{ activity.title }}</p>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    
                    <!-- Upcoming Deadlines Card -->
                    <Card v-if="userWidgets.find(w => w.id === 'upcoming-deadlines')?.enabled" :class="{'md:col-span-2': userWidgets.find(w => w.id === 'upcoming-deadlines')?.order % 2 === 0}">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-base">Upcoming Deadlines</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-2">
                                <div v-for="deadline in summaryData.upcomingDeadlines" :key="deadline.id" class="flex items-center justify-between p-2 rounded-md border">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <Link :href="route('mockups.ticket')" class="text-sm font-medium hover:underline">{{ deadline.id }}</Link>
                                            <span class="px-2 py-0.5 text-xs rounded-full" :class="{
                                                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300': deadline.priority === 'High',
                                                'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300': deadline.priority === 'Medium',
                                                'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300': deadline.priority === 'Low'
                                            }">{{ deadline.priority }}</span>
                                        </div>
                                        <p class="text-sm">{{ deadline.title }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-muted-foreground">{{ deadline.board }}</p>
                                        <p class="text-sm font-medium">Due {{ new Date(deadline.dueDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) }}</p>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    
                    <!-- My Tasks Card -->
                    <Card v-if="userWidgets.find(w => w.id === 'my-tasks')?.enabled" :class="{'md:col-span-2': userWidgets.find(w => w.id === 'my-tasks')?.order % 2 === 0}">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-base">My Tasks</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between p-2 rounded-md border">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium">PM-123</span>
                                            <span class="px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">In Progress</span>
                                        </div>
                                        <p class="text-sm">Implement AI-powered task prioritization</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-muted-foreground">Priority: High</p>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between p-2 rounded-md border">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium">PM-125</span>
                                            <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">Backlog</span>
                                        </div>
                                        <p class="text-sm">Implement team collaboration features</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-muted-foreground">Priority: Medium</p>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between p-2 rounded-md border">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium">PM-130</span>
                                            <span class="px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">In Progress</span>
                                        </div>
                                        <p class="text-sm">Implement user authentication with SSO</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-muted-foreground">Priority: High</p>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                        <CardFooter class="pt-0">
                            <Button variant="outline" size="sm" class="w-full">View All My Tasks</Button>
                        </CardFooter>
                    </Card>
                    
                    <!-- Time Tracking Widget -->
                    <Card v-if="userWidgets.find(w => w.id === 'time-tracking')?.enabled" :class="{'md:col-span-2': userWidgets.find(w => w.id === 'time-tracking')?.order % 2 === 0}">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-base">Time Tracking</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div>
                                    <h4 class="text-sm font-medium mb-2">Today's Tracked Time</h4>
                                    <div class="text-2xl font-bold">3h 45m</div>
                                    <div class="text-xs text-muted-foreground">Weekly total: 18h 20m</div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between p-2 rounded-md border">
                                        <div>
                                            <p class="text-sm font-medium">PM-123</p>
                                            <p class="text-xs text-muted-foreground">Implement AI-powered task prioritization</p>
                                        </div>
                                        <div class="text-sm font-medium">1h 30m</div>
                                    </div>
                                    <div class="flex items-center justify-between p-2 rounded-md border">
                                        <div>
                                            <p class="text-sm font-medium">PM-130</p>
                                            <p class="text-xs text-muted-foreground">Implement user authentication with SSO</p>
                                        </div>
                                        <div class="text-sm font-medium">2h 15m</div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                        <CardFooter class="pt-0">
                            <Button variant="outline" size="sm" class="w-full">Start Timer</Button>
                        </CardFooter>
                    </Card>
                    
                    <!-- Team Workload Widget -->
                    <Card v-if="userWidgets.find(w => w.id === 'team-workload')?.enabled" :class="{'md:col-span-2': userWidgets.find(w => w.id === 'team-workload')?.order % 2 === 0}">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-base">Team Workload</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <p class="text-sm font-medium">Jordan Dalton</p>
                                        <p class="text-xs text-muted-foreground">3 tasks</p>
                                    </div>
                                    <div class="h-2 w-full bg-muted rounded-full overflow-hidden">
                                        <div class="h-full bg-orange-500 rounded-full" style="width: 85%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <p class="text-sm font-medium">Alex Smith</p>
                                        <p class="text-xs text-muted-foreground">2 tasks</p>
                                    </div>
                                    <div class="h-2 w-full bg-muted rounded-full overflow-hidden">
                                        <div class="h-full bg-green-500 rounded-full" style="width: 60%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <p class="text-sm font-medium">Taylor Morgan</p>
                                        <p class="text-xs text-muted-foreground">4 tasks</p>
                                    </div>
                                    <div class="h-2 w-full bg-muted rounded-full overflow-hidden">
                                        <div class="h-full bg-red-500 rounded-full" style="width: 95%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <p class="text-sm font-medium">Emily Davis</p>
                                        <p class="text-xs text-muted-foreground">1 task</p>
                                    </div>
                                    <div class="h-2 w-full bg-muted rounded-full overflow-hidden">
                                        <div class="h-full bg-blue-500 rounded-full" style="width: 30%"></div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    
                    <!-- Project Milestones Widget -->
                    <Card v-if="userWidgets.find(w => w.id === 'project-milestones')?.enabled" :class="{'md:col-span-2': userWidgets.find(w => w.id === 'project-milestones')?.order % 2 === 0}">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-base">Project Milestones</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div class="relative pl-5 pb-5 border-l-2 border-primary">
                                    <div class="absolute w-3 h-3 bg-primary rounded-full -left-[6.5px] top-0"></div>
                                    <div class="mb-1">
                                        <span class="text-sm font-medium">Alpha Release</span>
                                        <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">Completed</span>
                                    </div>
                                    <p class="text-xs text-muted-foreground">March 15, 2025</p>
                                </div>
                                <div class="relative pl-5 pb-5 border-l-2 border-primary">
                                    <div class="absolute w-3 h-3 bg-primary rounded-full -left-[6.5px] top-0"></div>
                                    <div class="mb-1">
                                        <span class="text-sm font-medium">Beta Release</span>
                                        <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">In Progress</span>
                                    </div>
                                    <p class="text-xs text-muted-foreground">April 30, 2025</p>
                                </div>
                                <div class="relative pl-5 border-l-2 border-muted">
                                    <div class="absolute w-3 h-3 bg-muted rounded-full -left-[6.5px] top-0"></div>
                                    <div class="mb-1">
                                        <span class="text-sm font-medium">V1.0 Release</span>
                                        <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300">Upcoming</span>
                                    </div>
                                    <p class="text-xs text-muted-foreground">June 15, 2025</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    
                    <!-- Quick Actions Widget -->
                    <Card v-if="userWidgets.find(w => w.id === 'quick-actions')?.enabled" :class="{'md:col-span-2': userWidgets.find(w => w.id === 'quick-actions')?.order % 2 === 0}">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-base">Quick Actions</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-2 gap-2">
                                <Button size="sm" variant="outline" class="justify-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><path d="M11 12H3"></path><path d="m16 7 5 5-5 5"></path></svg>
                                    Create Task
                                </Button>
                                <Button size="sm" variant="outline" class="justify-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><rect width="18" height="18" x="3" y="3" rx="2"></rect><path d="M8 12h8"></path><path d="M12 8v8"></path></svg>
                                    Add Board
                                </Button>
                                <Button size="sm" variant="outline" class="justify-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v8"></path><path d="M8 12h8"></path></svg>
                                    Create Meeting
                                </Button>
                                <Button size="sm" variant="outline" class="justify-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                    Generate Report
                                </Button>
                                <Button size="sm" variant="outline" class="justify-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><path d="M3 7V5c0-1.1.9-2 2-2h2"></path><path d="M17 3h2c1.1 0 2 .9 2 2v2"></path><path d="M21 17v2c0 1.1-.9 2-2 2h-2"></path><path d="M7 21H5c-1.1 0-2-.9-2-2v-2"></path><rect width="7" height="5" x="7" y="7" rx="1"></rect><rect width="7" height="5" x="10" y="12" rx="1"></rect></svg>
                                    View All Boards
                                </Button>
                                <Button size="sm" variant="outline" class="justify-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                                    Invite Team
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
                
                <!-- AI Assistant Card -->
                <div v-if="dashboardLayout.showAI" class="md:col-span-1 h-full">
                    <Card class="h-full flex flex-col">
                        <CardHeader class="pb-2 border-b">
                            <div class="flex items-center justify-between">
                                <CardTitle class="text-base">AI Assistant</CardTitle>
                                <Button variant="ghost" size="sm" class="h-8 w-8 p-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </Button>
                            </div>
                        </CardHeader>
                        <div ref="chatContainerRef" class="flex-1 overflow-y-auto max-h-[calc(100vh-300px)]">
                            <div class="p-4 space-y-4">
                                <div v-for="(message, index) in messages" :key="index" 
                                    :class="{ 
                                        'flex items-start gap-2.5': true, 
                                        'justify-start': message.role === 'assistant',
                                        'justify-end': message.role === 'user'
                                    }">
                                    <!-- Avatar for AI -->
                                    <div v-if="message.role === 'assistant'" class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center text-blue-600 dark:text-blue-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-9-9 8.997 8.997 0 0 1 8.484 6"></path><path d="M14.942 11.942 21 12"></path><path d="M9.002 16c.855.74 2.053.93 3.122.494 1.07-.434 1.796-1.426 1.879-2.554"></path><rect x="10" y="8" width="0.01" height="0.01"></rect><rect x="14" y="8" width="0.01" height="0.01"></rect></svg>
                                    </div>
                                    
                                    <!-- Message content -->
                                    <div class="max-w-[80%]">
                                        <div :class="{
                                            'rounded-lg p-3 inline-block': true,
                                            'bg-primary text-primary-foreground': message.role === 'user',
                                            'bg-muted': message.role === 'assistant'
                                        }">
                                            <p class="text-sm whitespace-pre-line">{{ message.content }}</p>
                                        </div>
                                        <p class="text-xs text-muted-foreground mt-1">
                                            {{ formatDate(message.timestamp) }}
                                        </p>
                                    </div>
                                    
                                    <!-- Avatar for user -->
                                    <Avatar v-if="message.role === 'user'" class="h-8 w-8">
                                        <AvatarFallback>JD</AvatarFallback>
                                    </Avatar>
                                </div>
                                
                                <!-- Typing indicator -->
                                <div v-if="isProcessing" class="flex items-start gap-2.5 justify-start">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center text-blue-600 dark:text-blue-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-9-9 8.997 8.997 0 0 1 8.484 6"></path><path d="M14.942 11.942 21 12"></path><path d="M9.002 16c.855.74 2.053.93 3.122.494 1.07-.434 1.796-1.426 1.879-2.554"></path><rect x="10" y="8" width="0.01" height="0.01"></rect><rect x="14" y="8" width="0.01" height="0.01"></rect></svg>
                                    </div>
                                    <div class="max-w-[80%]">
                                        <div class="bg-muted rounded-lg p-3 inline-block">
                                            <div class="flex space-x-1">
                                                <div class="h-2 w-2 rounded-full bg-muted-foreground/40 animate-bounce"></div>
                                                <div class="h-2 w-2 rounded-full bg-muted-foreground/40 animate-bounce" style="animation-delay: 0.2s"></div>
                                                <div class="h-2 w-2 rounded-full bg-muted-foreground/40 animate-bounce" style="animation-delay: 0.4s"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Example prompts -->
                        <div v-if="messages.length <= 2 && !isProcessing" class="px-4 py-2 border-t">
                            <p class="text-xs text-muted-foreground mb-2">Suggested prompts:</p>
                            <div class="flex flex-wrap gap-2">
                                <button 
                                    v-for="example in exampleMessages.slice(0, 3)" 
                                    :key="example"
                                    @click="handleExampleClick(example)"
                                    class="px-2 py-1 text-xs rounded-full bg-muted hover:bg-muted/80 transition-colors"
                                >
                                    {{ example }}
                                </button>
                            </div>
                        </div>
                        
                        <!-- Input area -->
                        <div class="p-2 border-t mt-auto">
                            <form @submit.prevent="sendMessage" class="flex gap-2">
                                <input
                                    v-model="userInput"
                                    type="text"
                                    placeholder="Ask me anything about your projects..."
                                    class="flex-1 rounded-full border px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                                    :disabled="isProcessing"
                                />
                                <Button 
                                    type="submit" 
                                    size="sm" 
                                    class="rounded-full w-9 h-9 p-0 flex items-center justify-center"
                                    :disabled="isProcessing || !userInput.trim()"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                                </Button>
                            </form>
                        </div>
                    </Card>
                </div>
            </div>
        </div>
        
        <!-- Live Content Preview Overlay -->
        <div 
            v-if="showPreview" 
            class="fixed inset-0 bg-background/80 backdrop-blur-sm z-50 flex items-center justify-center"
            @click="showPreview = false"
        >
            <div class="w-full max-w-3xl max-h-[80vh] bg-card border rounded-lg shadow-lg overflow-hidden" @click.stop>
                <div class="border-b p-4 flex items-center justify-between">
                    <h3 class="font-semibold">
                        <template v-if="previewType === 'ticket-list'">High Priority Tickets</template>
                        <template v-if="previewType === 'board-summary'">Board Summary: {{ previewContent?.name }}</template>
                        <template v-if="previewType === 'user-tasks'">{{ previewContent?.user }}'s Tasks</template>
                        <template v-if="previewType === 'report'">Project Status Report</template>
                    </h3>
                    <Button variant="ghost" size="sm" class="h-8 w-8 p-0" @click="showPreview = false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
                    </Button>
                </div>
                
                <div class="p-6 overflow-auto max-h-[calc(80vh-70px)]">
                    <!-- Ticket List Preview -->
                    <div v-if="previewType === 'ticket-list'">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left p-2 font-medium text-muted-foreground">ID</th>
                                    <th class="text-left p-2 font-medium text-muted-foreground">Title</th>
                                    <th class="text-left p-2 font-medium text-muted-foreground">Status</th>
                                    <th class="text-left p-2 font-medium text-muted-foreground">Priority</th>
                                    <th class="text-left p-2 font-medium text-muted-foreground">Assignee</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="ticket in previewContent" :key="ticket.id" class="border-b hover:bg-muted/50">
                                    <td class="p-2 text-sm font-medium text-primary">{{ ticket.id }}</td>
                                    <td class="p-2 text-sm">{{ ticket.title }}</td>
                                    <td class="p-2">
                                        <span class="px-2 py-1 text-xs rounded-full" 
                                            :class="{
                                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': ticket.status === 'Done',
                                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300': ticket.status === 'In Progress',
                                                'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300': ticket.status === 'To Do',
                                                'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300': ticket.status === 'QA/Testing',
                                                'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300': ticket.status === 'Backlog'
                                            }">
                                            {{ ticket.status }}
                                        </span>
                                    </td>
                                    <td class="p-2">
                                        <span class="px-2 py-1 text-xs rounded-full" 
                                            :class="{
                                                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300': ticket.priority === 'High',
                                                'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300': ticket.priority === 'Medium',
                                                'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300': ticket.priority === 'Low'
                                            }">
                                            {{ ticket.priority }}
                                        </span>
                                    </td>
                                    <td class="p-2 text-sm">{{ ticket.assignee }}</td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div class="mt-6 flex justify-end gap-2">
                            <Button variant="outline" @click="showPreview = false">Close</Button>
                            <Button>Open in Board</Button>
                        </div>
                    </div>
                    
                    <!-- Board Summary Preview -->
                    <div v-if="previewType === 'board-summary'">
                        <div class="flex flex-col gap-6">
                            <div>
                                <h4 class="text-lg font-medium mb-2">{{ previewContent?.name }}</h4>
                                <div class="space-y-1.5">
                                    <div class="h-2.5 w-full bg-muted rounded-full overflow-hidden">
                                        <div 
                                            class="h-full bg-primary rounded-full" 
                                            :style="{ width: `${previewContent?.progress}%` }"
                                        ></div>
                                    </div>
                                    <div class="flex items-center justify-between text-sm text-muted-foreground">
                                        <span>{{ previewContent?.progress }}% Complete</span>
                                        <span>{{ previewContent?.completed }}/{{ previewContent?.total }} tasks</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <h4 class="text-base font-medium mb-2">Tasks by Status</h4>
                                <div class="grid grid-cols-3 gap-4">
                                    <div v-for="status in previewContent?.byStatus" :key="status.status" class="bg-muted rounded-md p-4 text-center">
                                        <div class="text-2xl font-bold">{{ status.count }}</div>
                                        <div class="text-sm text-muted-foreground">{{ status.status }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6 flex justify-end gap-2">
                            <Button variant="outline" @click="showPreview = false">Close</Button>
                            <Button>Open Board</Button>
                        </div>
                    </div>
                    
                    <!-- User Tasks Preview -->
                    <div v-if="previewType === 'user-tasks'">
                        <div class="mb-4">
                            <h4 class="text-lg font-medium">{{ previewContent?.user }}'s Tasks</h4>
                            <p class="text-sm text-muted-foreground">{{ previewContent?.assigned.length }} assigned tasks, {{ previewContent?.recentlyCompleted }} recently completed</p>
                        </div>
                        
                        <div class="space-y-3">
                            <div v-for="task in previewContent?.assigned" :key="task.id" class="border rounded-md p-3">
                                <div class="flex items-center justify-between">
                                    <div class="font-medium">{{ task.id }}</div>
                                    <span class="px-2 py-1 text-xs rounded-full" 
                                        :class="{
                                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': task.status === 'Done',
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300': task.status === 'In Progress',
                                            'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300': task.status === 'To Do',
                                            'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300': task.status === 'QA/Testing',
                                            'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300': task.status === 'Backlog'
                                        }">
                                        {{ task.status }}
                                    </span>
                                </div>
                                <div class="mt-1">{{ task.title }}</div>
                            </div>
                        </div>
                        
                        <div class="mt-6 flex justify-end gap-2">
                            <Button variant="outline" @click="showPreview = false">Close</Button>
                            <Button>View All Tasks</Button>
                        </div>
                    </div>
                    
                    <!-- Report Preview -->
                    <div v-if="previewType === 'report'">
                        <div class="mb-6">
                            <h4 class="text-lg font-medium">Project Status Report: {{ previewContent?.period }}</h4>
                            <div class="mt-4 space-y-1.5">
                                <div class="h-2.5 w-full bg-muted rounded-full overflow-hidden">
                                    <div 
                                        class="h-full bg-primary rounded-full" 
                                        :style="{ width: `${previewContent?.completion}%` }"
                                    ></div>
                                </div>
                                <div class="flex items-center justify-between text-sm text-muted-foreground">
                                    <span>{{ previewContent?.completion }}% Complete</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-3 gap-4 mb-6">
                            <div class="bg-muted rounded-md p-4 text-center">
                                <div class="text-2xl font-bold">{{ previewContent?.tasks.completed }}</div>
                                <div class="text-sm text-muted-foreground">Completed</div>
                            </div>
                            <div class="bg-muted rounded-md p-4 text-center">
                                <div class="text-2xl font-bold">{{ previewContent?.tasks.inProgress }}</div>
                                <div class="text-sm text-muted-foreground">In Progress</div>
                            </div>
                            <div class="bg-muted rounded-md p-4 text-center">
                                <div class="text-2xl font-bold">{{ previewContent?.tasks.created }}</div>
                                <div class="text-sm text-muted-foreground">New</div>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="text-base font-medium mb-2">Top Contributors</h4>
                            <div class="space-y-2">
                                <div v-for="contributor in previewContent?.contributors" :key="contributor.name" class="flex items-center justify-between p-2 border rounded-md">
                                    <div>{{ contributor.name }}</div>
                                    <div class="font-medium">{{ contributor.count }} tasks</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6 flex justify-end gap-2">
                            <Button variant="outline" @click="showPreview = false">Close</Button>
                            <Button>Download PDF</Button>
                            <Button>Share Report</Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Action Confirmation Dialog -->
        <div 
            v-if="showActionConfirmation" 
            class="fixed inset-0 bg-background/80 backdrop-blur-sm z-50 flex items-center justify-center"
        >
            <div class="w-full max-w-md bg-card border rounded-lg shadow-lg overflow-hidden" @click.stop>
                <div class="border-b p-4">
                    <h3 class="font-semibold">
                        <template v-if="pendingAction?.type === 'create-task'">Confirm Task Creation</template>
                        <template v-if="pendingAction?.type === 'schedule-meeting'">Confirm Meeting Schedule</template>
                    </h3>
                </div>
                
                <div class="p-6">
                    <!-- Task Creation Confirmation -->
                    <div v-if="pendingAction?.type === 'create-task'">
                        <p class="mb-4">Are you sure you want to create this task?</p>
                        
                        <div class="space-y-3 mb-4">
                            <div class="flex gap-2">
                                <span class="font-medium w-20">ID:</span>
                                <span>{{ pendingAction.id }}</span>
                            </div>
                            <div class="flex gap-2">
                                <span class="font-medium w-20">Title:</span>
                                <span>{{ pendingAction.title }}</span>
                            </div>
                            <div class="flex gap-2">
                                <span class="font-medium w-20">Status:</span>
                                <span>{{ pendingAction.status }}</span>
                            </div>
                            <div class="flex gap-2">
                                <span class="font-medium w-20">Priority:</span>
                                <span>{{ pendingAction.priority }}</span>
                            </div>
                        </div>
                        
                        <div class="space-y-3 mb-6">
                            <div>
                                <label class="text-sm font-medium">Assign to:</label>
                                <select class="w-full rounded-md border p-2 text-sm mt-1">
                                    <option value="">Unassigned</option>
                                    <option v-for="assignee in assignees.filter(a => a !== 'All')" :key="assignee" :value="assignee">
                                        {{ assignee }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Meeting Schedule Confirmation -->
                    <div v-if="pendingAction?.type === 'schedule-meeting'">
                        <p class="mb-4">Are you sure you want to schedule this meeting?</p>
                        
                        <div class="space-y-3 mb-4">
                            <div class="flex gap-2">
                                <span class="font-medium w-20">Title:</span>
                                <span>{{ pendingAction.title }}</span>
                            </div>
                            <div class="flex gap-2">
                                <span class="font-medium w-20">Time:</span>
                                <span>{{ new Date(pendingAction.time).toLocaleString() }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="font-medium">Attendees:</span>
                                <ul class="ml-6 list-disc">
                                    <li v-for="attendee in pendingAction.attendees" :key="attendee" class="text-sm">
                                        {{ attendee }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="space-y-3 mb-6">
                            <div>
                                <label class="text-sm font-medium">Meeting Agenda:</label>
                                <textarea class="w-full rounded-md border p-2 text-sm mt-1 min-h-[80px]" placeholder="Enter meeting agenda..."></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end gap-2">
                        <Button variant="outline" @click="showActionConfirmation = false">Cancel</Button>
                        <Button @click="() => {
                            if (pendingAction?.type === 'create-task') {
                                // Open the ticket edit modal with the task data
                                editableTicket.value = {
                                    id: pendingAction.id,
                                    title: pendingAction.title,
                                    description: 'Implement secure user authentication flow with support for email/password and social login providers.',
                                    status: pendingAction.status,
                                    priority: pendingAction.priority,
                                    assignee: null,
                                    dueDate: null,
                                    labels: ['Security', 'Backend', 'Feature'],
                                    attachments: [],
                                    comments: []
                                };
                                showActionConfirmation.value = false;
                                showTicketEditModal.value = true;
                            } else {
                                showActionConfirmation.value = false;
                            }
                        }">
                            <template v-if="pendingAction?.type === 'create-task'">Create Task</template>
                            <template v-if="pendingAction?.type === 'schedule-meeting'">Schedule Meeting</template>
                        </Button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Customize Dashboard Panel -->
        <div 
            v-if="showCustomizePanel" 
            class="fixed inset-0 bg-background/80 backdrop-blur-sm z-50 flex items-center justify-center overflow-y-auto"
        >
            <div class="w-full max-w-3xl my-8 bg-card border rounded-lg shadow-lg overflow-hidden" @click.stop>
                <div class="border-b p-4 flex items-center justify-between sticky top-0 bg-card z-10">
                    <h3 class="font-semibold">Customize Your Dashboard</h3>
                    <Button variant="ghost" size="sm" class="h-8 w-8 p-0" @click="showCustomizePanel = false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
                    </Button>
                </div>
                
                <div class="p-6 space-y-6">
                    <!-- Dashboard Layout Options -->
                    <div>
                        <h4 class="text-base font-medium mb-3">Layout Options</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium mb-1 block">Column Layout</label>
                                <select v-model="dashboardLayout.columnCount" class="w-full rounded-md border p-2 text-sm">
                                    <option :value="1">Single Column</option>
                                    <option :value="2">Two Columns</option>
                                    <option :value="3">Three Columns</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-sm font-medium mb-1 block">Show AI Assistant</label>
                                <div class="flex items-center h-10 mt-1">
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="checkbox" v-model="dashboardLayout.showAI" class="rounded border-gray-300 text-primary">
                                        <span>Show AI Assistant panel</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Widgets Selection -->
                    <div>
                        <h4 class="text-base font-medium mb-3">Widgets</h4>
                        <p class="text-sm text-muted-foreground mb-4">Select which widgets to display on your dashboard and their order.</p>
                        
                        <div class="space-y-2">
                            <div v-for="widget in userWidgets" :key="widget.id" class="flex items-center justify-between p-3 rounded-md border">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" v-model="widget.enabled" class="rounded border-gray-300 text-primary">
                                    <span>{{ widget.title }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-muted-foreground">Order:</span>
                                    <select v-model="widget.order" class="rounded-md border p-1 text-xs w-14">
                                        <option v-for="n in userWidgets.length" :key="n" :value="n">{{ n }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="border-t p-4 flex justify-end gap-2 sticky bottom-0 bg-card">
                    <Button variant="outline" @click="showCustomizePanel = false">Cancel</Button>
                    <Button @click="saveDashboardPreferences">Save Dashboard Layout</Button>
                </div>
            </div>
        </div>
        
        <!-- Ticket Edit Modal -->
        <div 
            v-if="showTicketEditModal && editableTicket" 
            class="fixed inset-0 bg-background/80 backdrop-blur-sm z-50 flex items-center justify-center overflow-y-auto"
        >
            <div class="w-full max-w-4xl my-8 bg-card border rounded-lg shadow-lg overflow-hidden" @click.stop>
                <div class="border-b p-4 flex items-center justify-between sticky top-0 bg-card z-10">
                    <div class="flex items-center gap-2">
                        <h3 class="font-semibold">Edit Ticket: {{ editableTicket.id }}</h3>
                        <span class="px-2 py-0.5 text-xs rounded-full" 
                            :class="{
                                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300': editableTicket.priority === 'High',
                                'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300': editableTicket.priority === 'Medium',
                                'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300': editableTicket.priority === 'Low'
                            }">
                            {{ editableTicket.priority }}
                        </span>
                    </div>
                    <Button variant="ghost" size="sm" class="h-8 w-8 p-0" @click="showTicketEditModal = false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
                    </Button>
                </div>
                
                <div class="p-6 space-y-6">
                    <!-- Two column layout -->
                    <div class="grid md:grid-cols-3 gap-6">
                        <!-- Main content column -->
                        <div class="md:col-span-2 space-y-6">
                            <!-- Title -->
                            <div>
                                <label class="text-sm font-medium mb-1 block">Title</label>
                                <input 
                                    v-model="editableTicket.title" 
                                    type="text" 
                                    class="w-full rounded-md border p-2 text-sm"
                                />
                            </div>
                            
                            <!-- Description -->
                            <div>
                                <label class="text-sm font-medium mb-1 block">Description</label>
                                <textarea 
                                    v-model="editableTicket.description" 
                                    class="w-full rounded-md border p-2 text-sm min-h-[150px]"
                                ></textarea>
                            </div>
                            
                            <!-- Labels -->
                            <div>
                                <label class="text-sm font-medium mb-1 block">Labels</label>
                                <div class="flex flex-wrap gap-2 mb-2">
                                    <div 
                                        v-for="label in editableTicket.labels" 
                                        :key="label" 
                                        class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 flex items-center gap-1"
                                    >
                                        {{ label }}
                                        <button class="h-3 w-3 rounded-full hover:bg-blue-200 dark:hover:bg-blue-800 flex items-center justify-center" @click="editableTicket.labels = editableTicket.labels.filter(l => l !== label)">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <input type="text" ref="newLabelInput" placeholder="Add a label" class="flex-1 rounded-md border p-2 text-xs" @keyup.enter="
                                        if ($event.target.value.trim()) {
                                            editableTicket.labels.push($event.target.value.trim());
                                            $event.target.value = '';
                                        }
                                    " />
                                    <Button size="sm" @click="
                                        const input = $refs.newLabelInput;
                                        if (input && input.value.trim()) {
                                            editableTicket.labels.push(input.value.trim());
                                            input.value = '';
                                        }
                                    ">Add</Button>
                                </div>
                            </div>
                            
                            <!-- Attachments section -->
                            <div>
                                <label class="text-sm font-medium mb-1 block">Attachments</label>
                                <div v-if="editableTicket.attachments.length === 0" class="p-8 border border-dashed rounded-md flex flex-col items-center justify-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground"><path d="M4 22h16a2 2 0 0 0 2-2V7.5L14.5 2H6a2 2 0 0 0-2 2v4"></path><polyline points="14 2 14 8 20 8"></polyline><path d="M2 15h10v5h-8a2 2 0 0 1-2-2z"></path><path d="m9 15-3-3-3 3"></path><path d="M6 12v3"></path></svg>
                                    <div class="text-sm text-muted-foreground">Drag and drop files or</div>
                                    <Button size="sm" variant="outline">Browse Files</Button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sidebar column -->
                        <div class="space-y-4">
                            <!-- Status -->
                            <div>
                                <label class="text-sm font-medium mb-1 block">Status</label>
                                <select v-model="editableTicket.status" class="w-full rounded-md border p-2 text-sm">
                                    <option v-for="status in allStatuses" :key="status" :value="status">
                                        {{ status }}
                                    </option>
                                </select>
                            </div>
                            
                            <!-- Priority -->
                            <div>
                                <label class="text-sm font-medium mb-1 block">Priority</label>
                                <select v-model="editableTicket.priority" class="w-full rounded-md border p-2 text-sm">
                                    <option value="Low">Low</option>
                                    <option value="Medium">Medium</option>
                                    <option value="High">High</option>
                                </select>
                            </div>
                            
                            <!-- Assignee -->
                            <div>
                                <label class="text-sm font-medium mb-1 block">Assignee</label>
                                <select v-model="editableTicket.assignee" class="w-full rounded-md border p-2 text-sm">
                                    <option :value="null">Unassigned</option>
                                    <option v-for="assignee in assignees.filter(a => a !== 'All')" :key="assignee" :value="assignee">
                                        {{ assignee }}
                                    </option>
                                </select>
                            </div>
                            
                            <!-- Due Date -->
                            <div>
                                <label class="text-sm font-medium mb-1 block">Due Date</label>
                                <input 
                                    v-model="editableTicket.dueDate" 
                                    type="date" 
                                    class="w-full rounded-md border p-2 text-sm"
                                />
                            </div>
                            
                            <!-- AI Assistant Card in context -->
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950 dark:to-indigo-950 border border-blue-100 dark:border-blue-900 rounded-md p-3 mt-6">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center text-blue-600 dark:text-blue-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-9-9 8.997 8.997 0 0 1 8.484 6"></path><path d="M14.942 11.942 21 12"></path><path d="M9.002 16c.855.74 2.053.93 3.122.494 1.07-.434 1.796-1.426 1.879-2.554"></path><rect x="10" y="8" width="0.01" height="0.01"></rect><rect x="14" y="8" width="0.01" height="0.01"></rect></svg>
                                    </div>
                                    <span class="text-xs font-medium text-blue-700 dark:text-blue-300">AI Suggestions</span>
                                </div>
                                <div class="text-xs text-blue-600 dark:text-blue-400">
                                    <p class="mb-2">This looks like an authentication task. Consider:</p>
                                    <ul class="ml-4 list-disc space-y-1 text-xs">
                                        <li>Setting up JWT token handling</li>
                                        <li>Implementing OAuth providers</li>
                                        <li>Adding user sessions management</li>
                                    </ul>
                                    <button class="text-xs mt-2 underline">Generate detailed subtasks</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="border-t p-4 flex justify-end gap-2 sticky bottom-0 bg-card">
                    <Button variant="outline" @click="showTicketEditModal = false">Cancel</Button>
                    <Button @click="
                        // In a real app, would save the ticket via API
                        // For demo, just close the modal
                        showTicketEditModal = false;
                        
                        // Show notification for ticket creation
                        recentNotifications.unshift({
                            type: 'task_update',
                            title: 'Ticket Created',
                            message: `${editableTicket.id}: ${editableTicket.title} created`,
                            time: new Date().toISOString()
                        });
                        notificationCount.value += 1;
                        hasNotifications.value = true;
                        
                        // Show success message via AI assistant
                        messages.value.push({
                            role: 'assistant',
                            content: `I've saved the ticket ${editableTicket.id}. You can view it in the ${editableTicket.status} column of your board. Would you like me to assign any subtasks or set up related tickets?`,
                            timestamp: new Date().toISOString()
                        });
                        scrollToBottom();
                    ">Save Ticket</Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Ensure that the chat container can grow within the flex context but respect the max-height */
.flex-1.overflow-y-auto {
  min-height: 300px;
}
</style>