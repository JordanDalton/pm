import api from './api';

export interface Board {
    id: string;
    name: string;
    description: string;
    progress: number;
    members: string[];
    last_updated: string;
    priority: string;
}

export interface BoardDetail extends Board {
    columns: {
        id: string;
        title: string;
        ticketIds: string[];
    }[];
    stats: {
        total_tasks: number;
        completed_tasks: number;
        in_progress: number;
        to_do: number;
        review: number;
    };
}

export interface BoardFilters {
    team?: string;
    search?: string;
}

export interface NewBoard {
    name: string;
    description: string;
    team: string;
    priority: string;
}

export interface TeamBoards {
    team: string;
    boards: Board[];
}

export default {
    /**
     * Get all boards grouped by team with optional filters
     */
    getBoards(filters: BoardFilters = {}) {
        return api.get('/boards', { params: filters });
    },
    
    /**
     * Get a specific board by ID
     */
    getBoard(id: string) {
        return api.get(`/boards/${id}`);
    },
    
    /**
     * Create a new board
     */
    createBoard(board: NewBoard) {
        return api.post('/boards', board);
    },
    
    /**
     * Update an existing board
     */
    updateBoard(id: string, board: Partial<Board>) {
        return api.put(`/boards/${id}`, board);
    },
    
    /**
     * Delete a board
     */
    deleteBoard(id: string) {
        return api.delete(`/boards/${id}`);
    },
    
    /**
     * Get tickets for a specific board
     */
    getBoardTickets(id: string) {
        return api.get(`/boards/${id}/tickets`);
    }
};