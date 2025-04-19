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
import BoardService, { type BoardFilters, type TeamBoards, type NewBoard } from '@/services/BoardService';

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

// Data states
const teamBoards = ref<TeamBoards[]>([]);
const isLoading = ref(true);
const error = ref<string | null>(null);

// Search and filter functionality
const searchQuery = ref('');
const selectedTeam = ref('All');

// Team options
const teamOptions = computed(() => {
    const teams = ['All', ...teamBoards.value.map(team => team.team)];
    return [...new Set(teams)]; // Remove duplicates
});

// Filtered teams based on search and team filter
const filteredTeams = computed(() => {
    if (isLoading.value) return [];
    
    let result = [...teamBoards.value];
    
    if (selectedTeam.value !== 'All') {
        result = result.filter(team => team.team === selectedTeam.value);
    }
    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.map(teamBoard => {
            const filteredBoards = teamBoard.boards.filter(board => 
                board.name.toLowerCase().includes(query) || 
                board.description.toLowerCase().includes(query)
            );
            return { ...teamBoard, boards: filteredBoards };
        }).filter(teamBoard => teamBoard.boards.length > 0);
    }
    
    return result;
});

// Function to load boards from API
const loadBoards = async () => {
    isLoading.value = true;
    error.value = null;
    
    try {
        const filters: BoardFilters = {};
        
        // Only add non-default filters
        if (searchQuery.value) {
            filters.search = searchQuery.value;
        }
        
        if (selectedTeam.value !== 'All') {
            filters.team = selectedTeam.value;
        }
        
        const response = await BoardService.getBoards(filters);
        teamBoards.value = response.data.teams || [];
    } catch (err: any) {
        console.error('Error loading boards:', err);
        error.value = err.message || 'Failed to load boards. Please try again.';
    } finally {
        isLoading.value = false;
    }
};

// Watch for filter changes
watch(selectedTeam, () => {
    loadBoards();
});

// Debounce search
watch(searchQuery, () => {
    loadBoards();
}, { debounce: 300 });

// Format date helper
const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

// Dialog for creating a new board
const isCreateDialogOpen = ref(false);
const isCreating = ref(false);
const createError = ref<string | null>(null);

// New board form data
const newBoard = ref<NewBoard>({
    name: '',
    description: '',
    team: 'Development',
    priority: 'Medium'
});

// Create a new board
const createBoard = async () => {
    isCreating.value = true;
    createError.value = null;
    
    try {
        await BoardService.createBoard(newBoard.value);
        
        // Reset form
        newBoard.value = {
            name: '',
            description: '',
            team: 'Development',
            priority: 'Medium'
        };
        
        // Close dialog and reload boards
        isCreateDialogOpen.value = false;
        loadBoards();
    } catch (err: any) {
        console.error('Error creating board:', err);
        createError.value = err.message || 'Failed to create board. Please try again.';
    } finally {
        isCreating.value = false;
    }
};

// Load boards when component mounts
onMounted(() => {
    loadBoards();
});
</script>

<template>
    <Head title="Boards" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Boards</h1>
                    <p class="text-muted-foreground">Manage and organize your project boards</p>
                </div>
                <Dialog v-model:open="isCreateDialogOpen">
                    <DialogTrigger as-child>
                        <Button>Create Board</Button>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-[550px]">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold mb-4">Create New Board</h2>
                            <form @submit.prevent="createBoard" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="board-name">Name</label>
                                    <input 
                                        id="board-name" 
                                        v-model="newBoard.name" 
                                        class="w-full p-2 border rounded-md" 
                                        required
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="board-description">Description</label>
                                    <textarea 
                                        id="board-description" 
                                        v-model="newBoard.description" 
                                        class="w-full p-2 border rounded-md"
                                        rows="3"
                                        required
                                    ></textarea>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="board-team">Team</label>
                                        <select id="board-team" v-model="newBoard.team" class="w-full p-2 border rounded-md">
                                            <option value="Development">Development</option>
                                            <option value="Design">Design</option>
                                            <option value="Marketing">Marketing</option>
                                            <option value="Documentation">Documentation</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="board-priority">Priority</label>
                                        <select id="board-priority" v-model="newBoard.priority" class="w-full p-2 border rounded-md">
                                            <option value="Low">Low</option>
                                            <option value="Medium">Medium</option>
                                            <option value="High">High</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div v-if="createError" class="p-3 rounded-md bg-red-50 text-red-700 text-sm">
                                    {{ createError }}
                                </div>
                                
                                <div class="flex justify-end gap-2">
                                    <Button @click="isCreateDialogOpen = false" variant="outline" :disabled="isCreating">Cancel</Button>
                                    <Button type="submit" :disabled="isCreating">
                                        <span v-if="isCreating" class="inline-block mr-1 animate-spin">&#9696;</span>
                                        {{ isCreating ? 'Creating...' : 'Create Board' }}
                                    </Button>
                                </div>
                            </form>
                        </div>
                    </DialogContent>
                </Dialog>
            </div>
            
            <!-- Filters -->
            <div class="flex flex-wrap gap-4">
                <div class="w-full md:w-auto">
                    <label class="block text-sm font-medium mb-1" for="filter-team">Team</label>
                    <select 
                        id="filter-team" 
                        v-model="selectedTeam" 
                        class="w-full md:w-40 p-2 border rounded-md"
                    >
                        <option v-for="team in teamOptions" :key="team">{{ team }}</option>
                    </select>
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium mb-1" for="search">Search</label>
                    <input 
                        id="search" 
                        v-model="searchQuery" 
                        class="w-full p-2 border rounded-md" 
                        placeholder="Search boards by name or description..."
                    />
                </div>
            </div>
            
            <!-- Loading state -->
            <div v-if="isLoading" class="py-8 text-center">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                <p class="mt-2 text-muted-foreground">Loading boards...</p>
            </div>
            
            <!-- Error state -->
            <div v-else-if="error" class="py-8 text-center">
                <div class="text-destructive mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <p>{{ error }}</p>
                </div>
                <Button @click="loadBoards" variant="outline" size="sm">Try Again</Button>
            </div>
            
            <!-- Empty state -->
            <div v-else-if="filteredTeams.length === 0" class="py-8 text-center border rounded-lg">
                <div class="text-muted-foreground">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    <p class="mb-2">No boards found</p>
                    <p class="text-sm mb-4">Try adjusting your filters or create a new board</p>
                </div>
                <DialogTrigger>
                    <Button @click="isCreateDialogOpen = true">Create a Board</Button>
                </DialogTrigger>
            </div>
            
            <!-- Team boards -->
            <div v-else class="flex flex-col gap-8">
                <div v-for="team in filteredTeams" :key="team.team" class="space-y-4">
                    <h2 class="text-xl font-semibold tracking-tight">{{ team.team }}</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <Card v-for="board in team.boards" :key="board.id" class="hover:shadow-md transition-shadow">
                            <Link :href="route('boards.show', board.id)" class="block">
                                <CardHeader>
                                    <div class="flex justify-between items-start mb-1">
                                        <CardTitle>{{ board.name }}</CardTitle>
                                        <span 
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                            :class="{
                                                'bg-red-100 text-red-800': board.priority === 'High',
                                                'bg-yellow-100 text-yellow-800': board.priority === 'Medium',
                                                'bg-green-100 text-green-800': board.priority === 'Low'
                                            }"
                                        >
                                            {{ board.priority }}
                                        </span>
                                    </div>
                                    <CardDescription>{{ board.description }}</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div class="space-y-4">
                                        <div>
                                            <div class="flex justify-between text-sm mb-1">
                                                <span>Progress</span>
                                                <span>{{ board.progress }}%</span>
                                            </div>
                                            <div class="w-full bg-muted rounded-full h-2">
                                                <div 
                                                    class="bg-primary rounded-full h-2" 
                                                    :style="{ width: `${board.progress}%` }"
                                                ></div>
                                            </div>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <div class="flex -space-x-2">
                                                <Avatar v-for="(member, index) in board.members.slice(0, 3)" :key="index" class="h-8 w-8 border-2 border-background">
                                                    <AvatarImage :src="null" />
                                                    <AvatarFallback>{{ useInitials(member) }}</AvatarFallback>
                                                </Avatar>
                                                <div v-if="board.members.length > 3" class="h-8 w-8 rounded-full bg-muted border-2 border-background flex items-center justify-center text-xs font-medium">
                                                    +{{ board.members.length - 3 }}
                                                </div>
                                            </div>
                                            <span class="text-xs text-muted-foreground">
                                                Updated {{ formatDate(board.last_updated) }}
                                            </span>
                                        </div>
                                    </div>
                                </CardContent>
                            </Link>
                        </Card>
                    </div>
                </div>
            </div>
            
            <!-- AI Assistant Insights -->
            <Card>
                <CardHeader>
                    <CardTitle>AI Assistant Insights</CardTitle>
                    <CardDescription>Analysis of your project boards</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="p-4 bg-primary/5 rounded-lg">
                        <p class="mb-2"><strong>Project Status Overview:</strong></p>
                        <ul class="space-y-1 list-disc list-inside">
                            <li>Frontend Development is progressing well but has 3 high priority tickets that need attention</li>
                            <li>The Design team has completed 70% of their work, ahead of schedule</li>
                            <li>Marketing Campaign Planning is behind at 25% completion - consider allocating more resources</li>
                            <li>Backend Development has good progress (60%) but could benefit from more thorough testing</li>
                        </ul>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>