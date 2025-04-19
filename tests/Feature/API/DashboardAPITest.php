<?php

namespace Tests\Feature\API;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardAPITest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
    }

    /** @test */
    public function unauthenticated_users_cannot_access_dashboard_api()
    {
        $response = $this->getJson('/api/dashboard/summary');
        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_users_can_get_dashboard_summary()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/dashboard/summary');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'dashboard' => [
                'tasks',
                'recent_activity',
                'upcoming_deadlines',
                'my_tasks',
                'team_boards',
                'team_workload',
                'time_tracking',
                'project_milestones'
            ]
        ]);
    }
}