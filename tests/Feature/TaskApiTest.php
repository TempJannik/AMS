<?php

namespace Tests\Feature;

use App\Events\TaskUpdated;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskOverdueNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
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
        $user = User::factory()->create();
        $tasks = Task::factory(5)->create(['user_id' => $user->id]);
        $differentUser = User::factory()->create();
        Task::factory(5)->create(['user_id' => $differentUser->id]);

        $taskResource = TaskResource::collection($tasks);
        $taskResourceJson = $taskResource->response()->getData(true);

        $this->actingAs($user);

        $response = $this->get("/api/users/{$user->id}/tasks");

        $response->assertStatus(200);
        $responseData = $response->json('data');
        $response->assertJsonCount(5, 'data');
        $this->assertEquals($taskResourceJson['data'], $responseData);
    }

    public function test_tasks_for_project_are_returned(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $tasks = Task::factory(5)->create(['project_id' => $project->id]);
        $differentProject = Project::factory()->create();
        Task::factory(5)->create(['project_id' => $differentProject->id]);

        $taskResource = TaskResource::collection($tasks);
        $taskResourceJson = $taskResource->response()->getData(true);

        $this->actingAs($user);

        $response = $this->get("/api/projects/{$project->id}/tasks");

        $response->assertStatus(200);
        $responseData = $response->json('data');
        $response->assertJsonCount(5, 'data');
        $this->assertEquals($taskResourceJson['data'], $responseData);
    }

    public function test_can_get_only_overdue_tasks(): void
    {
        $user = User::factory()->create();
        $overdueTasks = Task::factory(2)->create([
            'deadline' => Carbon::now()->subDay(),
        ]);

        Task::factory()->create([
            'deadline' => Carbon::now()->addDay(),
        ]);

        $this->actingAs($user);

        $response = $this->getJson('/api/tasks/past-deadline');
        $response->assertStatus(200);
        $responseData = $response->json('data');

        $taskResource = TaskResource::collection($overdueTasks);
        $taskResourceJson = $taskResource->response()->getData(true);

        $response->assertJsonCount(2, 'data');
        $this->assertEquals($taskResourceJson['data'], $responseData);
    }

    public function test_can_get_task_list(): void
    {
        $tasks = Task::factory(5)->create();

        $start = microtime(true);
        $response = $this->getJson('/api/tasks');
        $end = microtime(true);
        $responseTime = ($end - $start) * 1000;

        $taskResource = TaskResource::collection($tasks);
        $taskResourceJson = $taskResource->response()->getData(true);

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
        $responseData = $response->json('data');

        $this->assertEquals($taskResourceJson['data'], $responseData);
        $this->assertLessThan(300, $responseTime, 'Response time should be less than 300ms');
    }

    public function test_can_get_single_task(): void
    {
        $task = Task::factory()->create();

        $start = microtime(true);
        $response = $this->getJson("/api/tasks/{$task->id}");
        $end = microtime(true);
        $responseTime = ($end - $start) * 1000;

        $taskResource = new TaskResource($task);
        $taskResourceJson = $taskResource->response()->getData(true);

        $response->assertStatus(200);
        $responseData = $response->json('data');

        $this->assertEquals($taskResourceJson['data'], $responseData);
        $this->assertLessThan(300, $responseTime, 'Response time should be less than 300ms');
    }

    public function test_can_delete_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user);

        $start = microtime(true);
        $response = $this->deleteJson("/api/tasks/{$task->id}");
        $end = microtime(true);
        $responseTime = ($end - $start);

        $response->assertStatus(204);
        $this->assertLessThan(300, $responseTime, 'Response time should be less than 300ms');
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_can_create_task(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $taskToCreate = [
            'title' => 'Wichtige Aufgabe',
            'description' => 'Das ist eine wichtige Aufgabe',
            'status' => 'todo',
            'project_id' => $project->id,
            'user_id' => $user->id,
        ];

        $this->actingAs($user);

        $start = microtime(true);
        $response = $this->postJson('/api/tasks', $taskToCreate);
        $end = microtime(true);
        $responseTime = ($end - $start) * 1000;

        $response->assertStatus(201);
        $this->assertLessThan(300, $responseTime, 'Response time should be less than 300ms');
        $this->assertDatabaseHas('tasks', $taskToCreate);
    }

    public function test_cant_create_invalid_task(): void
    {
        $taskToCreate = [
            'status' => 'invalid_status',
        ];
        $response = $this->postJson('/api/tasks', $taskToCreate);
        $response->assertStatus(422);
    }

    public function test_can_update_task(): void
    {
        $task = Task::factory()->create([
            'deadline' => Carbon::now()->addDay(),
        ]);

        $updatedData = [
            'title' => 'Neuer Titel',
            'description' => 'Neue Beschreibung',
            'status' => 'done',
        ];

        $this->actingAs($task->user);
        $start = microtime(true);
        $response = $this->putJson("/api/tasks/{$task->id}", $updatedData);
        $end = microtime(true);
        $responseTime = ($end - $start) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(300, $responseTime, 'Response time should be less than 300ms');
        $this->assertDatabaseHas('tasks', array_merge(['id' => $task->id], $updatedData));
    }

    public function test_cant_update_invalid_task(): void
    {
        $task = Task::factory()->create([
            'deadline' => Carbon::now()->addDay(),
        ]);

        $updatedData = [
            'title' => 'Neuer Titel',
            'description' => 'Neue Beschreibung',
            'status' => 'donee',
        ];

        $this->actingAs($task->user);
        $response = $this->putJson("/api/tasks/{$task->id}", $updatedData);
        $response->assertStatus(422);
    }

    public function test_deadline_overdue_notification(): void
    {
        $task = Task::factory()->create([
            'deadline' => Carbon::now()->subDay(),
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
