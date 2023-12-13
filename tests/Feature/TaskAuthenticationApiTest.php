<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
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

    public function test_cant_create_task(): void
    {
        $taskToCreate = [
            'title' => 'Wichtige Aufgabe',
            'description' => 'Das ist eine wichtige Aufgabe',
            'status' => 'todo'
        ];
        $response = $this->post("/api/tasks", $taskToCreate, ['Accept' => 'application/json']);
        $response->assertStatus(401);
        $this->assertDatabaseMissing('tasks', $taskToCreate);
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
        $response->assertStatus(401);
        $this->assertDatabaseHas('tasks', $taskToFind->toArray());
    }
}
