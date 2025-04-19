<?php

namespace Tests\Feature\API;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function users_can_get_token_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/token', [
            'email' => 'test@example.com',
            'password' => 'password',
            'device_name' => 'testing_device',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'token',
            'user' => [
                'id',
                'name',
                'email',
            ],
        ]);
    }

    /** @test */
    public function users_cannot_get_token_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/token', [
            'email' => 'test@example.com',
            'password' => 'wrong_password',
            'device_name' => 'testing_device',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function authenticated_web_session_users_can_generate_api_token()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/token/generate');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'token',
            'user' => [
                'id',
                'name',
                'email',
            ],
        ]);
    }

    /** @test */
    public function users_can_get_their_profile()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/user');
        
        $response->assertStatus(200);
        $response->assertJson([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }

    /** @test */
    public function users_can_revoke_their_tokens()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/token/revoke');
        
        $response->assertStatus(200);
        $response->assertJsonStructure(['message']);
    }
}