<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskAuthenticationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $seeder = new DatabaseSeeder();
        $seeder->run();
    }

    public function test_cant_get_task_list(): void
    {
        $response = $this->get('/api/tasks', ['Accept' => 'application/json']);
        $response->assertStatus(401);
    }

    public function test_cant_get_single_task(): void
    {
        $taskToFind = Task::first();
        $this->assertNotNull($taskToFind);

        $response = $this->get("/api/tasks/{$taskToFind->id}", ['Accept' => 'application/json']);
        $response->assertStatus(401);
    }

    public function test_cant_delete_task(): void
    {
        $taskToFind = Task::first();
        $this->assertNotNull($taskToFind);

        $response = $this->delete("/api/tasks/{$taskToFind->id}", [], ['Accept' => 'application/json']);
        $response->assertStatus(401);
        $this->assertDatabaseHas('tasks', $taskToFind->toArray());
    }

    public function test_cant_delete_other_users_task(): void
    {
        $task = Task::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->delete("/api/tasks/{$task->id}", [], ['Accept' => 'application/json']);
        $response->assertStatus(403);
    }

    public function test_cant_create_task(): void
    {
        $taskToCreate = [
            'title' => 'Wichtige Aufgabe',
            'description' => 'Das ist eine wichtige Aufgabe',
            'status' => 'todo',
        ];
        $response = $this->post('/api/tasks', $taskToCreate, ['Accept' => 'application/json']);
        $response->assertStatus(401);
        $this->assertDatabaseMissing('tasks', $taskToCreate);
    }

    public function test_cant_update_other_users_task(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $otherUser]);

        $this->actingAs($user);

        $updatedData = [
            'title' => 'Aktualisierter Titel',
            'description' => 'Neue Beschreibung',
            'status' => 'in_progress',
        ];

        $response = $this->put("/api/tasks/{$task->id}", $updatedData, ['Accept' => 'application/json']);
        $response->assertStatus(403);
    }

    public function test_cant_update_overdue_task(): void
    {
        $task = Task::factory()->create([
            'deadline' => now()->subDay(),
        ]);

        $updatedData = [
            'title' => 'Neuer Titel',
            'description' => 'Neue Beschreibung',
            'status' => 'done',
        ];

        $this->actingAs($task->user);
        $response = $this->put("/api/tasks/{$task->id}", $updatedData, ['Accept' => 'application/json']);
        $response->assertStatus(403);
    }

    public function test_admin_can_update_overdue_task(): void
    {
        $adminUser = User::where('name', 'Admin User')->first();
        $this->assertNotNull($adminUser);
        $task = Task::factory()->create([
            'deadline' => now()->subDay(),
            'user_id' => $adminUser,
        ]);
        $this->assertNotNull($task);

        $updatedData = [
            'title' => 'Neuer Titel',
            'description' => 'Neue Beschreibung',
            'status' => 'done',
        ];

        $this->actingAs($adminUser);
        $response = $this->put("/api/tasks/{$task->id}", $updatedData, ['Accept' => 'application/json']);
        $response->assertStatus(200);
    }
}
