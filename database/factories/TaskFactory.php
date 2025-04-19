<?php

namespace Database\Factories;

use App\Models\Board;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'board_id' => Board::factory(),
            'user_id' => User::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(['todo', 'in_progress', 'review', 'done']),
            'priority' => fake()->numberBetween(0, 3),
            'due_date' => fake()->optional(0.7)->dateTimeBetween('now', '+30 days'),
        ];
    }
    
    /**
     * Indicate that the task is a todo.
     */
    public function todo(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'todo',
        ]);
    }
    
    /**
     * Indicate that the task is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
        ]);
    }
    
    /**
     * Indicate that the task is in review.
     */
    public function inReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'review',
        ]);
    }
    
    /**
     * Indicate that the task is completed.
     */
    public function done(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'done',
        ]);
    }
    
    /**
     * Indicate that the task has high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 3,
        ]);
    }
}
