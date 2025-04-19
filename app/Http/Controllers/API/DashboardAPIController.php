<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DashboardAPIController extends Controller
{
    /**
     * Get dashboard summary data.
     */
    public function summary(): JsonResponse
    {
        // Mock data for dashboard summary
        $summaryData = [
            'tasks' => [
                'total' => 53,
                'completed' => 25,
                'overdue' => 3,
                'in_progress' => 15,
                'to_do' => 10
            ],
            'recent_activity' => [
                [
                    'type' => 'ticket_created',
                    'user' => 'Alex Smith',
                    'details' => 'Created ticket PM-131',
                    'title' => 'Optimize database queries for performance',
                    'timestamp' => '2025-04-19T10:15:00Z'
                ],
                [
                    'type' => 'ticket_updated',
                    'user' => 'Jordan Dalton',
                    'details' => 'Changed status of PM-123 from "To Do" to "In Progress"',
                    'title' => 'Implement AI-powered task prioritization',
                    'timestamp' => '2025-04-19T09:30:00Z'
                ],
                [
                    'type' => 'comment_added',
                    'user' => 'Taylor Morgan',
                    'details' => 'Commented on PM-126',
                    'title' => 'Add email notification system',
                    'timestamp' => '2025-04-18T16:45:00Z'
                ],
                [
                    'type' => 'attachment_added',
                    'user' => 'Emily Davis',
                    'details' => 'Added attachment to PM-129',
                    'title' => 'Optimize database queries',
                    'timestamp' => '2025-04-18T14:20:00Z'
                ],
                [
                    'type' => 'ticket_created',
                    'user' => 'Michael Brown',
                    'details' => 'Created ticket PM-130',
                    'title' => 'Implement user authentication with SSO',
                    'timestamp' => '2025-04-18T11:10:00Z'
                ]
            ],
            'upcoming_deadlines' => [
                [
                    'id' => 'PM-127',
                    'title' => 'Fix search functionality in project view',
                    'due_date' => '2025-04-22',
                    'priority' => 'High',
                    'board' => 'Frontend Development'
                ],
                [
                    'id' => 'PM-128',
                    'title' => 'Create user documentation',
                    'due_date' => '2025-04-19',
                    'priority' => 'Low',
                    'board' => 'User Documentation'
                ],
                [
                    'id' => 'PM-126',
                    'title' => 'Add email notification system',
                    'due_date' => '2025-04-28',
                    'priority' => 'Medium',
                    'board' => 'Backend Development'
                ],
                [
                    'id' => 'PM-129',
                    'title' => 'Optimize database queries',
                    'due_date' => '2025-05-01',
                    'priority' => 'Medium',
                    'board' => 'Backend Development'
                ],
                [
                    'id' => 'PM-130',
                    'title' => 'Implement user authentication with SSO',
                    'due_date' => '2025-05-05',
                    'priority' => 'High',
                    'board' => 'Frontend Development'
                ]
            ],
            'my_tasks' => [
                [
                    'id' => 'PM-123',
                    'title' => 'Implement AI-powered task prioritization',
                    'status' => 'In Progress',
                    'priority' => 'High',
                    'due_date' => '2025-04-30',
                    'board' => 'Frontend Development'
                ],
                [
                    'id' => 'PM-125',
                    'title' => 'Implement team collaboration features',
                    'status' => 'Backlog',
                    'priority' => 'Medium',
                    'due_date' => '2025-05-20',
                    'board' => 'Backend Development'
                ],
                [
                    'id' => 'PM-130',
                    'title' => 'Implement user authentication with SSO',
                    'status' => 'In Progress',
                    'priority' => 'High',
                    'due_date' => '2025-05-05',
                    'board' => 'Frontend Development'
                ]
            ],
            'team_boards' => [
                [
                    'id' => 'DEV-1',
                    'name' => 'Frontend Development',
                    'progress' => 45,
                    'priority' => 'High'
                ],
                [
                    'id' => 'DEV-2',
                    'name' => 'Backend Development',
                    'progress' => 60,
                    'priority' => 'High'
                ],
                [
                    'id' => 'DES-1',
                    'name' => 'UI/UX Design',
                    'progress' => 70,
                    'priority' => 'Medium'
                ],
                [
                    'id' => 'MKT-2',
                    'name' => 'Campaign Planning',
                    'progress' => 25,
                    'priority' => 'High'
                ]
            ],
            'team_workload' => [
                [
                    'name' => 'Jordan Dalton',
                    'assigned' => 3,
                    'completed' => 2
                ],
                [
                    'name' => 'Alex Smith',
                    'assigned' => 2,
                    'completed' => 1
                ],
                [
                    'name' => 'Taylor Morgan',
                    'assigned' => 1,
                    'completed' => 3
                ],
                [
                    'name' => 'Emily Davis',
                    'assigned' => 2,
                    'completed' => 2
                ],
                [
                    'name' => 'Michael Brown',
                    'assigned' => 1,
                    'completed' => 1
                ]
            ],
            'time_tracking' => [
                [
                    'id' => 'PM-123',
                    'title' => 'Implement AI-powered task prioritization',
                    'hours_logged' => 12.5,
                    'estimated_hours' => 20
                ],
                [
                    'id' => 'PM-130',
                    'title' => 'Implement user authentication with SSO',
                    'hours_logged' => 8,
                    'estimated_hours' => 16
                ],
                [
                    'id' => 'PM-127',
                    'title' => 'Fix search functionality in project view',
                    'hours_logged' => 3.5,
                    'estimated_hours' => 6
                ]
            ],
            'project_milestones' => [
                [
                    'title' => 'Alpha Release',
                    'due_date' => '2025-05-15',
                    'progress' => 65,
                    'status' => 'On Track'
                ],
                [
                    'title' => 'Beta Testing',
                    'due_date' => '2025-06-30',
                    'progress' => 25,
                    'status' => 'At Risk'
                ],
                [
                    'title' => 'Production Release',
                    'due_date' => '2025-07-31',
                    'progress' => 15,
                    'status' => 'On Track'
                ]
            ]
        ];

        return response()->json(['dashboard' => $summaryData]);
    }
}