<?php

use App\Models\User;
use App\Models\Board;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('unauthenticated users cannot access board routes', function () {
    $this->get(route('boards.index'))->assertRedirect(route('login'));
    $this->get(route('boards.create'))->assertRedirect(route('login'));
    $this->post(route('boards.store'))->assertRedirect(route('login'));
    $this->get(route('boards.show', 1))->assertRedirect(route('login'));
    $this->get(route('boards.edit', 1))->assertRedirect(route('login'));
    $this->put(route('boards.update', 1))->assertRedirect(route('login'));
    $this->delete(route('boards.destroy', 1))->assertRedirect(route('login'));
});

test('authenticated users can view board index', function () {
    $this->actingAs($this->user)
        ->get(route('boards.index'))
        ->assertStatus(200);
});

test('authenticated users can view board create form', function () {
    $this->actingAs($this->user)
        ->get(route('boards.create'))
        ->assertStatus(200);
});

test('authenticated users can store a new board', function () {
    $boardData = [
        'name' => 'Test Board',
        'description' => 'This is a test board',
        'status' => 'active',
        'user_id' => $this->user->id,
    ];

    $this->actingAs($this->user)
        ->post(route('boards.store'), $boardData)
        ->assertRedirect();

    $this->assertDatabaseHas('boards', [
        'name' => 'Test Board',
    ]);
});

test('authenticated users can view a board', function () {
    $board = Board::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user)
        ->get(route('boards.show', $board->id))
        ->assertStatus(200);
});

test('authenticated users can edit a board', function () {
    $board = Board::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user)
        ->get(route('boards.edit', $board->id))
        ->assertStatus(200);
});

test('authenticated users can update a board', function () {
    $board = Board::factory()->create([
        'name' => 'Original Name',
        'user_id' => $this->user->id,
    ]);

    $updatedData = [
        'name' => 'Updated Name',
        'description' => $board->description,
        'status' => $board->status,
        'user_id' => $this->user->id,
    ];

    $response = $this->actingAs($this->user)
        ->from(route('boards.edit', $board->id))
        ->put(route('boards.update', $board->id), $updatedData);
    
    $response->assertRedirect();
});

test('authenticated users can delete a board', function () {
    $board = Board::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user)
        ->delete(route('boards.destroy', $board->id))
        ->assertRedirect();

    $this->assertDatabaseMissing('boards', [
        'id' => $board->id,
    ]);
});