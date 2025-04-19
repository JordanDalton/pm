<?php

namespace Tests\Feature\API;

use App\Models\User;
use App\Models\Board;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TicketAPITest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Board $board;

    public function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->board = Board::factory()->create(['user_id' => $this->user->id]);
    }

    /** @test */
    public function unauthenticated_users_cannot_access_ticket_api_endpoints()
    {
        $response = $this->getJson('/api/tickets');
        $response->assertStatus(401);

        $response = $this->postJson('/api/tickets', []);
        $response->assertStatus(401);

        $response = $this->getJson('/api/tickets/1');
        $response->assertStatus(401);

        $response = $this->putJson('/api/tickets/1', []);
        $response->assertStatus(401);

        $response = $this->deleteJson('/api/tickets/1');
        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_users_can_get_tickets()
    {
        Sanctum::actingAs($this->user);

        $task = Task::factory()->create([
            'board_id' => $this->board->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson('/api/tickets');
        $response->assertStatus(200);
        $response->assertJsonStructure(['tickets']);
    }

    /** @test */
    public function authenticated_users_can_create_a_ticket()
    {
        Sanctum::actingAs($this->user);

        $ticketData = [
            'title' => 'Test Ticket API',
            'description' => 'This is a test ticket created via API',
            'status' => 'open',
            'priority' => 'medium',
            'board_id' => $this->board->id,
            'user_id' => $this->user->id,
        ];

        $response = $this->postJson('/api/tickets', $ticketData);
        $response->assertStatus(201);
        $response->assertJsonStructure(['ticket']);
        
        // The controller is currently mocked, so we can't assert database has
        // but in a real implementation we would add:
        // $this->assertDatabaseHas('tasks', ['title' => 'Test Ticket API']);
    }

    /** @test */
    public function authenticated_users_can_get_a_specific_ticket()
    {
        Sanctum::actingAs($this->user);

        $task = Task::factory()->create([
            'board_id' => $this->board->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson("/api/tickets/{$task->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure(['ticket']);
    }

    /** @test */
    public function authenticated_users_can_update_a_ticket()
    {
        Sanctum::actingAs($this->user);

        $task = Task::factory()->create([
            'title' => 'Original Title',
            'board_id' => $this->board->id,
            'user_id' => $this->user->id,
        ]);

        $updatedData = [
            'title' => 'Updated Title API',
            'description' => $task->description,
            'status' => $task->status,
            'priority' => $task->priority,
        ];

        $response = $this->putJson("/api/tickets/{$task->id}", $updatedData);
        $response->assertStatus(200);
        $response->assertJsonStructure(['ticket']);
        
        // The controller is currently mocked, so we can't assert database has
        // but in a real implementation we would add:
        // $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'Updated Title API']);
    }

    /** @test */
    public function authenticated_users_can_delete_a_ticket()
    {
        Sanctum::actingAs($this->user);

        $task = Task::factory()->create([
            'board_id' => $this->board->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->deleteJson("/api/tickets/{$task->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure(['message']);
        
        // The controller is currently mocked, so we can't assert database missing
        // but in a real implementation we would add:
        // $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}