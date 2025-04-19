<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BoardAPIController extends Controller
{
    /**
     * Display a listing of the boards.
     */
    public function index(Request $request): JsonResponse
    {
        // Mock data for boards - this will be replaced with database queries
        $boards = [
            [
                'team' => 'Development',
                'boards' => [
                    [
                        'id' => 'DEV-1',
                        'name' => 'Frontend Development',
                        'description' => 'Tasks related to frontend development and UI/UX implementation',
                        'progress' => 45,
                        'members' => ['Jordan Dalton', 'Alex Smith', 'Taylor Morgan'],
                        'last_updated' => '2025-04-18T14:30:00Z',
                        'priority' => 'High'
                    ],
                    [
                        'id' => 'DEV-2',
                        'name' => 'Backend Development',
                        'description' => 'Tasks related to backend development, APIs, and database',
                        'progress' => 60,
                        'members' => ['Jordan Dalton', 'Emily Davis'],
                        'last_updated' => '2025-04-17T11:15:00Z',
                        'priority' => 'High'
                    ],
                    [
                        'id' => 'DEV-3',
                        'name' => 'Application Testing',
                        'description' => 'Testing tasks including unit tests, integration tests, and QA',
                        'progress' => 30,
                        'members' => ['Alex Smith', 'Michael Brown'],
                        'last_updated' => '2025-04-16T09:45:00Z',
                        'priority' => 'Medium'
                    ]
                ]
            ],
            [
                'team' => 'Design',
                'boards' => [
                    [
                        'id' => 'DES-1',
                        'name' => 'UI/UX Design',
                        'description' => 'Design tasks for the user interface and user experience',
                        'progress' => 70,
                        'members' => ['Taylor Morgan', 'Michael Brown'],
                        'last_updated' => '2025-04-15T15:30:00Z',
                        'priority' => 'Medium'
                    ],
                    [
                        'id' => 'DES-2',
                        'name' => 'Brand Assets',
                        'description' => 'Creation and management of brand assets and style guides',
                        'progress' => 85,
                        'members' => ['Taylor Morgan'],
                        'last_updated' => '2025-04-14T10:20:00Z',
                        'priority' => 'Low'
                    ]
                ]
            ],
            [
                'team' => 'Marketing',
                'boards' => [
                    [
                        'id' => 'MKT-1',
                        'name' => 'Content Creation',
                        'description' => 'Content creation tasks for marketing materials',
                        'progress' => 50,
                        'members' => ['Emily Davis', 'Michael Brown'],
                        'last_updated' => '2025-04-13T13:45:00Z',
                        'priority' => 'Medium'
                    ],
                    [
                        'id' => 'MKT-2',
                        'name' => 'Campaign Planning',
                        'description' => 'Planning and execution of marketing campaigns',
                        'progress' => 25,
                        'members' => ['Emily Davis'],
                        'last_updated' => '2025-04-12T09:30:00Z',
                        'priority' => 'High'
                    ]
                ]
            ],
            [
                'team' => 'Documentation',
                'boards' => [
                    [
                        'id' => 'DOC-1',
                        'name' => 'User Documentation',
                        'description' => 'Creation and maintenance of user documentation',
                        'progress' => 80,
                        'members' => ['Michael Brown', 'Jordan Dalton'],
                        'last_updated' => '2025-04-11T14:15:00Z',
                        'priority' => 'Low'
                    ],
                    [
                        'id' => 'DOC-2',
                        'name' => 'API Documentation',
                        'description' => 'Documentation for API endpoints and usage',
                        'progress' => 65,
                        'members' => ['Emily Davis', 'Jordan Dalton'],
                        'last_updated' => '2025-04-10T11:00:00Z',
                        'priority' => 'Medium'
                    ]
                ]
            ]
        ];

        // Apply filters if provided
        if ($request->has('team')) {
            $team = $request->input('team');
            if ($team !== 'All') {
                $boards = array_filter($boards, function($teamData) use ($team) {
                    return $teamData['team'] === $team;
                });
            }
        }
        
        if ($request->has('search')) {
            $search = strtolower($request->input('search'));
            $filteredBoards = [];
            
            foreach ($boards as $teamData) {
                $filteredTeamBoards = array_filter($teamData['boards'], function($board) use ($search) {
                    return strpos(strtolower($board['name']), $search) !== false || 
                           strpos(strtolower($board['description']), $search) !== false;
                });
                
                if (!empty($filteredTeamBoards)) {
                    $filteredBoards[] = [
                        'team' => $teamData['team'],
                        'boards' => array_values($filteredTeamBoards)
                    ];
                }
            }
            
            $boards = $filteredBoards;
        }

        return response()->json(['teams' => array_values($boards)]);
    }

    /**
     * Store a newly created board in storage.
     */
    public function store(Request $request): JsonResponse
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'team' => 'required|string',
            'priority' => 'required|string'
        ]);

        // Generate a new board ID based on the team
        $teamPrefix = substr(strtoupper($request->input('team')), 0, 3);
        $newBoardId = $teamPrefix . '-' . rand(1, 99);

        // In a real application, this would create a board in the database
        // For now, we'll return a mock new board
        $newBoard = [
            'id' => $newBoardId,
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'progress' => 0,
            'members' => [$request->user()->name], // Current user
            'last_updated' => now()->toIso8601String(),
            'priority' => $request->input('priority')
        ];

        return response()->json(['board' => $newBoard], 201);
    }

    /**
     * Display the specified board.
     */
    public function show(string $id): JsonResponse
    {
        // Mock data for a specific board - this will be replaced with a database query
        $board = [
            'id' => $id,
            'name' => 'Frontend Development',
            'description' => 'Tasks related to frontend development and UI/UX implementation',
            'progress' => 45,
            'members' => ['Jordan Dalton', 'Alex Smith', 'Taylor Morgan'],
            'last_updated' => '2025-04-18T14:30:00Z',
            'priority' => 'High',
            'columns' => [
                [
                    'id' => 'column-1',
                    'title' => 'To Do',
                    'ticketIds' => ['PM-124', 'PM-131']
                ],
                [
                    'id' => 'column-2',
                    'title' => 'In Progress',
                    'ticketIds' => ['PM-123', 'PM-129', 'PM-130']
                ],
                [
                    'id' => 'column-3',
                    'title' => 'Review',
                    'ticketIds' => ['PM-127']
                ],
                [
                    'id' => 'column-4',
                    'title' => 'Done',
                    'ticketIds' => ['PM-128']
                ]
            ],
            'stats' => [
                'total_tasks' => 7,
                'completed_tasks' => 1,
                'in_progress' => 3,
                'to_do' => 2,
                'review' => 1
            ]
        ];

        return response()->json(['board' => $board]);
    }

    /**
     * Update the specified board in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        // Validate the request
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'priority' => 'sometimes|string',
            'columns' => 'sometimes|array'
        ]);

        // In a real application, this would update a board in the database
        // For now, we'll return a mock updated board
        $updatedBoard = [
            'id' => $id,
            'name' => $request->input('name', 'Frontend Development'),
            'description' => $request->input('description', 'Tasks related to frontend development and UI/UX implementation'),
            'progress' => 45,
            'members' => ['Jordan Dalton', 'Alex Smith', 'Taylor Morgan'],
            'last_updated' => now()->toIso8601String(),
            'priority' => $request->input('priority', 'High'),
            'columns' => $request->input('columns', [
                [
                    'id' => 'column-1',
                    'title' => 'To Do',
                    'ticketIds' => ['PM-124', 'PM-131']
                ],
                [
                    'id' => 'column-2',
                    'title' => 'In Progress',
                    'ticketIds' => ['PM-123', 'PM-129', 'PM-130']
                ],
                [
                    'id' => 'column-3',
                    'title' => 'Review',
                    'ticketIds' => ['PM-127']
                ],
                [
                    'id' => 'column-4',
                    'title' => 'Done',
                    'ticketIds' => ['PM-128']
                ]
            ])
        ];

        return response()->json(['board' => $updatedBoard]);
    }

    /**
     * Remove the specified board from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        // In a real application, this would delete a board from the database
        // For now, we'll just return a success message
        return response()->json(['message' => "Board {$id} successfully deleted"]);
    }

    /**
     * Get tickets for a specific board.
     */
    public function tickets(string $id): JsonResponse
    {
        // In a real application, this would fetch tickets for a specific board from the database
        // For now, we'll use mock data from the TicketAPIController
        $ticketAPIController = new TicketAPIController();
        $request = new Request(['board_id' => $id]);
        $response = $ticketAPIController->index($request);
        $data = json_decode($response->getContent(), true);

        return response()->json(['tickets' => $data['tickets']]);
    }
}