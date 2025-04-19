import api from './api';

export interface DashboardSummary {
    tasks: {
        total: number;
        completed: number;
        overdue: number;
        in_progress: number;
        to_do: number;
    };
    recent_activity: {
        type: string;
        user: string;
        details: string;
        title: string;
        timestamp: string;
    }[];
    upcoming_deadlines: {
        id: string;
        title: string;
        due_date: string;
        priority: string;
        board: string;
    }[];
    my_tasks: {
        id: string;
        title: string;
        status: string;
        priority: string;
        due_date: string;
        board: string;
    }[];
    team_boards: {
        id: string;
        name: string;
        progress: number;
        priority: string;
    }[];
    team_workload: {
        name: string;
        assigned: number;
        completed: number;
    }[];
    time_tracking: {
        id: string;
        title: string;
        hours_logged: number;
        estimated_hours: number;
    }[];
    project_milestones: {
        title: string;
        due_date: string;
        progress: number;
        status: string;
    }[];
}

export default {
    /**
     * Get dashboard summary data
     */
    getDashboardSummary() {
        return api.get('/dashboard/summary');
    }
};