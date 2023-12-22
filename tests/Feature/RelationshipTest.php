<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_user_relationship()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $reloadedTask = Task::with('user')->find($task->id);

        $this->assertTrue($reloadedTask->user instanceof User);
        $this->assertEquals($user->id, $reloadedTask->user->id);
    }

    public function test_task_project_relationship()
    {
        $project = Project::factory()->create();
        $task = Task::factory()->create(['project_id' => $project->id]);

        $reloadedTask = Task::with('project')->find($task->id);

        $this->assertTrue($reloadedTask->project instanceof Project);
        $this->assertEquals($project->id, $reloadedTask->project->id);
    }

    public function test_user_tasks_relationship()
    {
        $user = User::factory()->create();
        $tasks = Task::factory()->count(3)->create(['user_id' => $user->id]);

        $reloadedUser = User::with('tasks')->find($user->id);

        $this->assertTrue($reloadedUser->tasks instanceof Collection);
        $this->assertCount(3, $reloadedUser->tasks);
        $this->assertEquals($tasks->pluck('id'), $reloadedUser->tasks->pluck('id'));
    }

    public function test_project_tasks_relationship()
    {
        $project = Project::factory()->create();
        $tasks = Task::factory()->count(3)->create(['project_id' => $project->id]);

        $reloadedProject = Project::with('tasks')->find($project->id);

        $this->assertTrue($reloadedProject->tasks instanceof Collection);
        $this->assertCount(3, $reloadedProject->tasks);
        $this->assertEquals($tasks->pluck('id'), $reloadedProject->tasks->pluck('id'));
    }
}
