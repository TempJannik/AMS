<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $seeder = new DatabaseSeeder();
        $seeder->run();
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_can_get_task_list(): void
    {
        $response = $this->get('/api/tasks', ['Accept' => 'application/json']);
        $response->assertStatus(200);
    }

    public function test_can_get_single_task(): void
    {
        $taskToFind = Task::first();
        $this->assertNotNull($taskToFind);

        $response = $this->get("/api/tasks/{$taskToFind->id}", ['Accept' => 'application/json']);
        $response->assertStatus(200);
    }

    public function test_can_delete_task(): void
    {
        $taskToFind = Task::first();
        $this->assertNotNull($taskToFind);

        $response = $this->delete("/api/tasks/{$taskToFind->id}", [], ['Accept' => 'application/json']);
        $response->assertStatus(204);
        $this->assertDatabaseMissing('tasks', ['id' => $taskToFind->id]);
    }

    public function test_can_create_task(): void
    {
        $taskToCreate = [
            'title' => 'Wichtige Aufgabe',
            'description' => 'Das ist eine wichtige Aufgabe',
            'status' => 'todo'
        ];
        $response = $this->post("/api/tasks", $taskToCreate, ['Accept' => 'application/json']);
        $response->assertStatus(201);
        $this->assertDatabaseHas('tasks', $taskToCreate);
    }

    public function test_cant_create_invalid_task(): void
    {
        $taskToCreate = [
            'status' => 'invalid_status'
        ];
        $response = $this->post("/api/tasks", $taskToCreate, ['Accept' => 'application/json']);
        $response->assertStatus(422);
    }

    public function test_can_update_task(): void
    {
        $taskToFind = Task::first();
        $this->assertNotNull($taskToFind);

        $updatedData = [
            'title' => 'Neuer Titel',
            'description' => 'Neue Beschreibung',
            'status' => 'done',
        ];

        $response = $this->put("/api/tasks/{$taskToFind->id}", $updatedData, ['Accept' => 'application/json']);
        $response->assertStatus(200);

        $this->assertDatabaseHas('tasks', [
            'id' => $taskToFind->id,
            'title' => 'Neuer Titel',
            'description' => 'Neue Beschreibung',
            'status' => 'done',
        ]);
    }

    public function test_cant_update_invalid_task(): void
    {
        $taskToFind = Task::first();
        $this->assertNotNull($taskToFind);

        $updatedData = [
            'title' => 'Neuer Titel',
            'description' => 'Neue Beschreibung',
            'status' => 'donee',
        ];

        $response = $this->put("/api/tasks/{$taskToFind->id}", $updatedData, ['Accept' => 'application/json']);
        $response->assertStatus(422);
    }
}
