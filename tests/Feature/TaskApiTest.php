<?php

namespace Tests\Feature;


use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use App\Events\TaskUpdated;
use App\Listeners\CheckTaskDeadline;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use App\Notifications\TaskOverdueNotification;
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

    private function assertTaskListResponse($response, $tasks): void
    {
        foreach ($tasks as $task) {
            $response->assertJsonFragment([
                'id' => $task->id,
                'title' => $task->title,
            ]);
        }
    }

    public function test_tasks_for_user_are_returned(): void
    {
        $user = User::first();
        $this->actingAs($user);

        $userTasks = Task::where('user_id', $user->id)->get();

        $response = $this->get("/api/users/{$user->id}/tasks", ['Accept' => 'application/json']);
        $response->assertStatus(200);

        $this->assertTaskListResponse($response, $userTasks);
    }

    public function test_tasks_for_project_are_returned(): void
    {
        $user = User::first();
        $this->actingAs($user);
        $project = Project::first();

        $projectTasks = Task::where('project_id', $project->id)->get();

        $response = $this->get("/api/projects/{$project->id}/tasks", ['Accept' => 'application/json']);
        $response->assertStatus(200);

        $this->assertTaskListResponse($response, $projectTasks);
    }

    public function test_can_get_only_overdue_tasks(): void
    {
        $overdueTask = Task::factory()->create([
            'deadline' => now()->subDay(),
        ]);

        $nonOverdueTask = Task::factory()->create([
            'deadline' => now()->addDay(), 
        ]);

        $response = $this->get('/api/tasks/past-deadline', ['Accept' => 'application/json']);
        $response->assertStatus(200);

        $response->assertJsonCount(21);
        $response->assertJsonFragment([
            'id' => $overdueTask->id,
            'title' => $overdueTask->title,
        ]);
    }

    public function test_can_get_task_list(): void
    {
        $start = microtime(true);
        $response = $this->get('/api/tasks', ['Accept' => 'application/json']);
        $end = microtime(true);
        $responseTime = ($end - $start) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(300, $responseTime, 'Response time should be less than 300ms');
    }

    public function test_can_get_single_task(): void
    {
        $taskToFind = Task::first();
        $this->assertNotNull($taskToFind);

        $start = microtime(true);
        $response = $this->get("/api/tasks/{$taskToFind->id}", ['Accept' => 'application/json']);
        $end = microtime(true);
        $responseTime = ($end - $start) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(300, $responseTime, 'Response time should be less than 300ms');
    }

    public function test_can_delete_task(): void
    {
        $taskToFind = Task::first();
        $this->assertNotNull($taskToFind);
        $this->actingAs($taskToFind->user);

        $start = microtime(true);
        $response = $this->delete("/api/tasks/{$taskToFind->id}", [], ['Accept' => 'application/json']);
        $end = microtime(true);
        $responseTime = ($end - $start);

        $response->assertStatus(204);
        $this->assertLessThan(300, $responseTime, 'Response time should be less than 300ms');
        $this->assertDatabaseMissing('tasks', ['id' => $taskToFind->id]);
    }

    public function test_can_create_task(): void
    {
        $project = Project::first();
        $user = User::first();
        $taskToCreate = [
            'title' => 'Wichtige Aufgabe',
            'description' => 'Das ist eine wichtige Aufgabe',
            'status' => 'todo',
            'project_id' => $project->id,
            'user_id' => $user->id
        ];

        $this->actingAs($user);

        $start = microtime(true);
        $response = $this->post("/api/tasks", $taskToCreate, ['Accept' => 'application/json']);
        $end = microtime(true);
        $responseTime = ($end - $start) * 1000;

        $response->assertStatus(201);
        $this->assertLessThan(300, $responseTime, 'Response time should be less than 300ms');
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
        $task = Task::factory()->create([
            'deadline' => now()->addDay(),
        ]);

        $updatedData = [
            'title' => 'Neuer Titel',
            'description' => 'Neue Beschreibung',
            'status' => 'done',
        ];

        $this->actingAs($task->user);
        $start = microtime(true);
        $response = $this->put("/api/tasks/{$task->id}", $updatedData, ['Accept' => 'application/json']);
        $end = microtime(true);
        $responseTime = ($end - $start) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(300, $responseTime, 'Response time should be less than 300ms');
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Neuer Titel',
            'description' => 'Neue Beschreibung',
            'status' => 'done',
        ]);
    }

    public function test_cant_update_invalid_task(): void
    {
        $task = Task::factory()->create([
            'deadline' => now()->addDay(),
        ]);

        $updatedData = [
            'title' => 'Neuer Titel',
            'description' => 'Neue Beschreibung',
            'status' => 'donee',
        ];

        $this->actingAs($task->user);
        $response = $this->put("/api/tasks/{$task->id}", $updatedData, ['Accept' => 'application/json']);
        $response->assertStatus(422);
    }

    public function test_deadline_overdue_notification(): void
    {
        $task = Task::factory()->create([
            'deadline' => now()->subDay(),
        ]);

        Event::fake();
        Mail::fake();
        Notification::fake();

        event(new TaskUpdated($task));

        Event::assertDispatched(TaskUpdated::class);
        Event::assertDispatched(TaskUpdated::class, function ($event) use ($task) {
            return $event->task->id === $task->id;
        });

        $task->user->notify(new TaskOverdueNotification($task));
        Notification::assertSentTo($task->user, TaskOverdueNotification::class);
    }
}
