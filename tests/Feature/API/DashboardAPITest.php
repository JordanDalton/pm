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

    public function test_unauthenticated_users_cannot_access_dashboard_api()
    {
        // Since we don't have actual API implementations yet, these tests are currently disabled
        $this->markTestSkipped('API implementation not yet available');
        
        $response = $this->getJson('/api/dashboard/summary');
        $response->assertStatus(401);
    }

    public function test_authenticated_users_can_get_dashboard_summary()
    {
        // Since we don't have actual API implementations yet, these tests are currently disabled
        $this->markTestSkipped('API implementation not yet available');
        
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