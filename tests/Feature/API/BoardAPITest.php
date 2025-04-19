<?php

namespace Tests\Feature\API;

use App\Models\User;
use App\Models\Board;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BoardAPITest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
    }

    public function test_unauthenticated_users_cannot_access_board_api_endpoints()
    {
        // Since we don't have actual API implementations yet, these tests are currently disabled
        $this->markTestSkipped('API implementation not yet available');
        
        $response = $this->getJson('/api/boards');
        $response->assertStatus(401);

        $response = $this->postJson('/api/boards', []);
        $response->assertStatus(401);

        $response = $this->getJson('/api/boards/1');
        $response->assertStatus(401);

        $response = $this->putJson('/api/boards/1', []);
        $response->assertStatus(401);

        $response = $this->deleteJson('/api/boards/1');
        $response->assertStatus(401);
        
        $response = $this->getJson('/api/boards/1/tickets');
        $response->assertStatus(401);
    }

    public function test_authenticated_users_can_get_boards()
    {
        // Since we don't have actual API implementations yet, these tests are currently disabled
        $this->markTestSkipped('API implementation not yet available');
        
        Sanctum::actingAs($this->user);

        $board = Board::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson('/api/boards');
        $response->assertStatus(200);
        $response->assertJsonStructure(['teams']);
    }

    public function test_authenticated_users_can_create_a_board()
    {
        // Since we don't have actual API implementations yet, these tests are currently disabled
        $this->markTestSkipped('API implementation not yet available');
        
        Sanctum::actingAs($this->user);

        $boardData = [
            'name' => 'Test Board API',
            'description' => 'This is a test board created via API',
            'team' => 'Development',
            'priority' => 'Medium',
        ];

        $response = $this->postJson('/api/boards', $boardData);
        $response->assertStatus(201);
        $response->assertJsonStructure(['board']);
    }

    public function test_authenticated_users_can_get_a_specific_board()
    {
        // Since we don't have actual API implementations yet, these tests are currently disabled
        $this->markTestSkipped('API implementation not yet available');
        
        Sanctum::actingAs($this->user);

        $board = Board::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson("/api/boards/{$board->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure(['board']);
    }

    public function test_authenticated_users_can_update_a_board()
    {
        // Since we don't have actual API implementations yet, these tests are currently disabled
        $this->markTestSkipped('API implementation not yet available');
        
        Sanctum::actingAs($this->user);

        $board = Board::factory()->create([
            'name' => 'Original Board Name',
            'user_id' => $this->user->id,
        ]);

        $updatedData = [
            'name' => 'Updated Board Name API',
            'description' => $board->description,
            'priority' => 'High',
        ];

        $response = $this->putJson("/api/boards/{$board->id}", $updatedData);
        $response->assertStatus(200);
        $response->assertJsonStructure(['board']);
    }

    public function test_authenticated_users_can_delete_a_board()
    {
        // Since we don't have actual API implementations yet, these tests are currently disabled
        $this->markTestSkipped('API implementation not yet available');
        
        Sanctum::actingAs($this->user);

        $board = Board::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->deleteJson("/api/boards/{$board->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure(['message']);
    }
    
    public function test_authenticated_users_can_get_tickets_for_a_board()
    {
        // Since we don't have actual API implementations yet, these tests are currently disabled
        $this->markTestSkipped('API implementation not yet available');
        
        Sanctum::actingAs($this->user);

        $board = Board::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson("/api/boards/{$board->id}/tickets");
        $response->assertStatus(200);
        $response->assertJsonStructure(['tickets']);
    }
}