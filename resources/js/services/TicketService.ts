import api from './api';

export interface Ticket {
    id: string;
    title: string;
    description: string;
    status: string;
    priority: string;
    assignee: string;
    reporter: string;
    created_at: string;
    updated_at: string;
    due_date: string;
    labels: string[];
    board_id: string;
}

export interface TicketDetail extends Ticket {
    comments: {
        id: number;
        user: string;
        content: string;
        created_at: string;
    }[];
    subtasks: {
        id: string;
        title: string;
        status: string;
    }[];
    attachments: {
        id: string;
        name: string;
        size: string;
        uploaded_at: string;
        uploaded_by: string;
    }[];
}

export interface TicketFilters {
    status?: string;
    priority?: string;
    assignee?: string;
    search?: string;
    board_id?: string;
}

export interface NewTicket {
    title: string;
    description: string;
    status: string;
    priority: string;
    assignee?: string;
    due_date?: string;
    labels?: string[];
    board_id: string;
}

export default {
    /**
     * Get all tickets with optional filters
     */
    getTickets(filters: TicketFilters = {}) {
        return api.get('/tickets', { params: filters });
    },
    
    /**
     * Get a specific ticket by ID
     */
    getTicket(id: string) {
        return api.get(`/tickets/${id}`);
    },
    
    /**
     * Create a new ticket
     */
    createTicket(ticket: NewTicket) {
        return api.post('/tickets', ticket);
    },
    
    /**
     * Update an existing ticket
     */
    updateTicket(id: string, ticket: Partial<Ticket>) {
        return api.put(`/tickets/${id}`, ticket);
    },
    
    /**
     * Delete a ticket
     */
    deleteTicket(id: string) {
        return api.delete(`/tickets/${id}`);
    }
};