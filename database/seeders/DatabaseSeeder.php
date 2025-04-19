<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\Task;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create main test user
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        
        // Create additional users
        $users = User::factory(5)->create();
        
        // Create boards for the main test user
        $boards = Board::factory(3)
            ->for($user)
            ->create();
            
        // Create tasks for each board
        foreach ($boards as $board) {
            Task::factory(5)
                ->for($board)
                ->for($user)
                ->create();
        }
        
        // Create some boards with tasks for other users
        foreach ($users as $otherUser) {
            $boards = Board::factory(rand(1, 3))
                ->for($otherUser)
                ->create();
                
            foreach ($boards as $board) {
                Task::factory(rand(3, 8))
                    ->for($board)
                    ->for($otherUser)
                    ->create();
            }
        }
    }
}
