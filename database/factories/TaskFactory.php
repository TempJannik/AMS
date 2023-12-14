<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Project;
use App\Models\User;

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
            'title' => Str::random(10),
            'description' => fake()->catchPhrase(),
            'status' => fake()->randomElement(['in_progress', 'todo', 'done']),
            'deadline' => fake()->dateTime(),
            'user_id' => User::factory(), 
            'project_id' => Project::factory(),
        ];
    }
}
