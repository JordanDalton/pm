<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TicketAPIController extends Controller
{
    /**
     * Display a listing of the tickets.
     */
    public function index(Request $request): JsonResponse
    {
        // Mock data for tickets - this will be replaced with database queries
        $tickets = [
            [
                'id' => 'PM-123',
                'title' => 'Implement AI-powered task prioritization',
                'description' => 'Create a system that uses AI to suggest task priorities based on deadlines, dependencies, and team workload.',
                'status' => 'In Progress',
                'priority' => 'High',
                'assignee' => 'Jordan Dalton',
                'reporter' => 'Alex Smith',
                'created_at' => '2025-03-15T09:00:00Z',
                'updated_at' => '2025-04-18T14:30:00Z',
                'due_date' => '2025-04-30',
                'labels' => ['AI', 'Enhancement'],
                'board_id' => 'DEV-1'
            ],
            [
                'id' => 'PM-124',
                'title' => 'Refactor authentication system',
                'description' => 'Refactor the current authentication system to use the latest security practices and improve performance.',
                'status' => 'To Do',
                'priority' => 'Medium',
                'assignee' => 'Emily Davis',
                'reporter' => 'Jordan Dalton',
                'created_at' => '2025-03-20T11:15:00Z',
                'updated_at' => '2025-03-20T11:15:00Z',
                'due_date' => '2025-05-10',
                'labels' => ['Security', 'Technical Debt'],
                'board_id' => 'DEV-1'
            ],
            [
                'id' => 'PM-125',
                'title' => 'Implement team collaboration features',
                'description' => 'Add features that enable better team collaboration, including shared notes, real-time comments, and status updates.',
                'status' => 'Backlog',
                'priority' => 'Medium',
                'assignee' => 'Jordan Dalton',
                'reporter' => 'Alex Smith',
                'created_at' => '2025-03-25T13:45:00Z',
                'updated_at' => '2025-03-25T13:45:00Z',
                'due_date' => '2025-05-20',
                'labels' => ['Enhancement', 'Collaboration'],
                'board_id' => 'DEV-2'
            ],
            [
                'id' => 'PM-126',
                'title' => 'Add email notification system',
                'description' => 'Implement an email notification system for task assignments, status changes, and approaching deadlines.',
                'status' => 'In Progress',
                'priority' => 'Medium',
                'assignee' => 'Taylor Morgan',
                'reporter' => 'Emily Davis',
                'created_at' => '2025-03-28T10:30:00Z',
                'updated_at' => '2025-04-15T16:20:00Z',
                'due_date' => '2025-04-28',
                'labels' => ['Enhancement', 'Notification'],
                'board_id' => 'DEV-2'
            ],
            [
                'id' => 'PM-127',
                'title' => 'Fix search functionality in project view',
                'description' => 'Resolve issues with search functionality not returning accurate results in the project view.',
                'status' => 'QA/Testing',
                'priority' => 'High',
                'assignee' => 'Alex Smith',
                'reporter' => 'Taylor Morgan',
                'created_at' => '2025-04-01T09:15:00Z',
                'updated_at' => '2025-04-17T11:45:00Z',
                'due_date' => '2025-04-22',
                'labels' => ['Bug', 'Search'],
                'board_id' => 'DEV-1'
            ],
            [
                'id' => 'PM-128',
                'title' => 'Create user documentation',
                'description' => 'Create comprehensive user documentation for all features in the application.',
                'status' => 'Done',
                'priority' => 'Low',
                'assignee' => 'Michael Brown',
                'reporter' => 'Jordan Dalton',
                'created_at' => '2025-04-05T14:00:00Z',
                'updated_at' => '2025-04-18T09:30:00Z',
                'due_date' => '2025-04-19',
                'labels' => ['Documentation'],
                'board_id' => 'DOC-1'
            ],
            [
                'id' => 'PM-129',
                'title' => 'Optimize database queries',
                'description' => 'Identify and optimize slow database queries to improve application performance.',
                'status' => 'In Progress',
                'priority' => 'Medium',
                'assignee' => 'Emily Davis',
                'reporter' => 'Jordan Dalton',
                'created_at' => '2025-04-08T11:30:00Z',
                'updated_at' => '2025-04-16T15:45:00Z',
                'due_date' => '2025-05-01',
                'labels' => ['Performance', 'Database'],
                'board_id' => 'DEV-1'
            ],
            [
                'id' => 'PM-130',
                'title' => 'Implement user authentication with SSO',
                'description' => 'Implement Single Sign-On (SSO) for user authentication to streamline the login process.',
                'status' => 'In Progress',
                'priority' => 'High',
                'assignee' => 'Jordan Dalton',
                'reporter' => 'Alex Smith',
                'created_at' => '2025-04-10T13:15:00Z',
                'updated_at' => '2025-04-17T16:00:00Z',
                'due_date' => '2025-05-05',
                'labels' => ['Authentication', 'Security'],
                'board_id' => 'DEV-1'
            ],
            [
                'id' => 'PM-131',
                'title' => 'Optimize database queries for performance',
                'description' => 'Identify and optimize slow database queries to improve application performance.',
                'status' => 'To Do',
                'priority' => 'High',
                'assignee' => 'Alex Smith',
                'reporter' => 'Jordan Dalton',
                'created_at' => '2025-04-19T10:15:00Z',
                'updated_at' => '2025-04-19T10:15:00Z',
                'due_date' => '2025-05-10',
                'labels' => ['Performance', 'Database'],
                'board_id' => 'DEV-1'
            ],
        ];

        // Apply filters if provided
        if ($request->has('status')) {
            $status = $request->input('status');
            if ($status !== 'All') {
                $tickets = array_filter($tickets, function($ticket) use ($status) {
                    return $ticket['status'] === $status;
                });
            }
        }
        
        if ($request->has('priority')) {
            $priority = $request->input('priority');
            if ($priority !== 'All') {
                $tickets = array_filter($tickets, function($ticket) use ($priority) {
                    return $ticket['priority'] === $priority;
                });
            }
        }
        
        if ($request->has('assignee')) {
            $assignee = $request->input('assignee');
            if ($assignee !== 'All') {
                $tickets = array_filter($tickets, function($ticket) use ($assignee) {
                    return $ticket['assignee'] === $assignee;
                });
            }
        }
        
        if ($request->has('search')) {
            $search = strtolower($request->input('search'));
            $tickets = array_filter($tickets, function($ticket) use ($search) {
                return strpos(strtolower($ticket['id']), $search) !== false || 
                       strpos(strtolower($ticket['title']), $search) !== false;
            });
        }

        if ($request->has('board_id')) {
            $boardId = $request->input('board_id');
            $tickets = array_filter($tickets, function($ticket) use ($boardId) {
                return $ticket['board_id'] === $boardId;
            });
        }

        return response()->json(['tickets' => array_values($tickets)]);
    }

    /**
     * Store a newly created ticket in storage.
     */
    public function store(Request $request): JsonResponse
    {
        // Validate the request
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|string',
            'priority' => 'required|string',
            'assignee' => 'nullable|string',
            'board_id' => 'required|string'
        ]);

        // In a real application, this would create a ticket in the database
        // For now, we'll return a mock new ticket
        $newTicket = [
            'id' => 'PM-' . rand(132, 999),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'status' => $request->input('status'),
            'priority' => $request->input('priority'),
            'assignee' => $request->input('assignee'),
            'reporter' => 'Jordan Dalton', // Current user
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
            'due_date' => $request->input('due_date'),
            'labels' => $request->input('labels', []),
            'board_id' => $request->input('board_id')
        ];

        return response()->json(['ticket' => $newTicket], 201);
    }

    /**
     * Display the specified ticket.
     */
    public function show(string $id): JsonResponse
    {
        // Mock data for a specific ticket - this will be replaced with a database query
        $ticket = [
            'id' => $id,
            'title' => 'Implement AI-powered task prioritization',
            'description' => 'Create a system that uses AI to suggest task priorities based on deadlines, dependencies, and team workload.',
            'status' => 'In Progress',
            'priority' => 'High',
            'assignee' => 'Jordan Dalton',
            'reporter' => 'Alex Smith',
            'created_at' => '2025-03-15T09:00:00Z',
            'updated_at' => '2025-04-18T14:30:00Z',
            'due_date' => '2025-04-30',
            'labels' => ['AI', 'Enhancement'],
            'board_id' => 'DEV-1',
            'comments' => [
                [
                    'id' => 1,
                    'user' => 'Jordan Dalton',
                    'content' => 'I\'ve started working on the AI integration. Will update the team once I have a prototype.',
                    'created_at' => '2025-04-16T11:30:00Z'
                ],
                [
                    'id' => 2,
                    'user' => 'Alex Smith',
                    'content' => 'Great! Let me know if you need any help with the algorithm.',
                    'created_at' => '2025-04-16T13:45:00Z'
                ],
                [
                    'id' => 3,
                    'user' => 'Taylor Morgan',
                    'content' => 'We should also consider how this will impact the UX. I can help with that aspect.',
                    'created_at' => '2025-04-17T09:15:00Z'
                ]
            ],
            'subtasks' => [
                [
                    'id' => 'ST-1',
                    'title' => 'Research ML algorithms for task prioritization',
                    'status' => 'Done'
                ],
                [
                    'id' => 'ST-2',
                    'title' => 'Create API for priority suggestions',
                    'status' => 'In Progress'
                ],
                [
                    'id' => 'ST-3',
                    'title' => 'Design UI for priority visualization',
                    'status' => 'To Do'
                ]
            ],
            'attachments' => [
                [
                    'id' => 'ATT-1',
                    'name' => 'algorithm_design.pdf',
                    'size' => '2.4 MB',
                    'uploaded_at' => '2025-04-16T14:00:00Z',
                    'uploaded_by' => 'Jordan Dalton'
                ],
                [
                    'id' => 'ATT-2',
                    'name' => 'ui_mockup.png',
                    'size' => '1.1 MB',
                    'uploaded_at' => '2025-04-17T10:30:00Z',
                    'uploaded_by' => 'Taylor Morgan'
                ]
            ]
        ];

        return response()->json(['ticket' => $ticket]);
    }

    /**
     * Update the specified ticket in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        // Validate the request
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'status' => 'sometimes|string',
            'priority' => 'sometimes|string',
            'assignee' => 'sometimes|nullable|string',
            'due_date' => 'sometimes|nullable|date'
        ]);

        // In a real application, this would update a ticket in the database
        // For now, we'll return a mock updated ticket
        $updatedTicket = [
            'id' => $id,
            'title' => $request->input('title', 'Implement AI-powered task prioritization'),
            'description' => $request->input('description', 'Create a system that uses AI to suggest task priorities based on deadlines, dependencies, and team workload.'),
            'status' => $request->input('status', 'In Progress'),
            'priority' => $request->input('priority', 'High'),
            'assignee' => $request->input('assignee', 'Jordan Dalton'),
            'reporter' => 'Alex Smith',
            'created_at' => '2025-03-15T09:00:00Z',
            'updated_at' => now()->toIso8601String(),
            'due_date' => $request->input('due_date', '2025-04-30'),
            'labels' => $request->input('labels', ['AI', 'Enhancement']),
            'board_id' => $request->input('board_id', 'DEV-1')
        ];

        return response()->json(['ticket' => $updatedTicket]);
    }

    /**
     * Remove the specified ticket from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        // In a real application, this would delete a ticket from the database
        // For now, we'll just return a success message
        return response()->json(['message' => "Ticket {$id} successfully deleted"]);
    }
}