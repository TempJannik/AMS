<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Database\Factories\TaskFactory;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskAuthenticationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_guest_cant_get_task_list(): void
    {
        $response = $this->getJson('/api/tasks');
        $response->assertStatus(401);
    }

    public function test_guest_cant_get_single_task(): void
    {
        $task = Task::factory()->create();

        $response = $this->getJson("/api/tasks/{$task->id}");
        $response->assertStatus(401);
    }

    public function test_guest_cant_delete_task(): void
    {
        $task = Task::factory()->create();

        $response = $this->deleteJson("/api/tasks/{$task->id}");
        $response->assertStatus(401);
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    public function test_user_cant_delete_other_users_task(): void
    {
        $task = Task::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->deleteJson("/api/tasks/{$task->id}");
        $response->assertStatus(403);
    }

    public function test_guest_cant_create_task(): void
    {
        $taskToCreate = [
            'title' => 'Wichtige Aufgabe',
            'description' => 'Das ist eine wichtige Aufgabe',
            'status' => 'todo',
        ];
        $response = $this->postJson('/api/tasks', $taskToCreate);
        $response->assertStatus(401);
        $this->assertDatabaseMissing('tasks', $taskToCreate);
    }

    public function test_user_cant_update_other_users_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();

        $this->actingAs($user);

        $updatedData = [
            'title' => 'Aktualisierter Titel',
            'description' => 'Neue Beschreibung',
            'status' => 'in_progress',
        ];

        $response = $this->putJson("/api/tasks/{$task->id}", $updatedData);
        $response->assertStatus(403);
    }

    public function test_normal_user_cant_update_overdue_task(): void
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
        $response = $this->putJson("/api/tasks/{$task->id}", $updatedData);
        $response->assertStatus(403);
    }

    public function test_admin_can_update_overdue_task(): void
    {
        $adminUser = User::factory()->create();
        (new RolesAndPermissionsSeeder())->run();
        $adminUser->assignRole('Super-Admin');

        $task = Task::factory()->create([
            'deadline' => now()->subDay(),
            'user_id' => $adminUser->id,
        ]);

        $updatedData = [
            'title' => 'Neuer Titel',
            'description' => 'Neue Beschreibung',
            'status' => 'done',
        ];

        $this->actingAs($adminUser);
        $response = $this->putJson("/api/tasks/{$task->id}", $updatedData);
        $response->assertStatus(200);
    }
}
