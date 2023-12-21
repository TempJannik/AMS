<?php

namespace App\Observers;

use App\Events\TaskUpdated;
use App\Models\Task;
use Illuminate\Support\Facades\Cache;

class TaskObserver
{
    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        Cache::forget('tasks');

        if ($task->isOverdue()) {
            Cache::forget('tasks_overdue');
        }

        Cache::forget("tasks_user_{$task->user->id}");
        Cache::forget("tasks_project_{$task->project->id}");
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        Cache::forget('tasks');

        if ($task->isOverdue()) {
            Cache::forget('tasks_overdue');
        }

        Cache::forget("tasks_user_{$task->user->id}");
        Cache::forget("tasks_project_{$task->project->id}");

        event(new TaskUpdated($task));
    }

    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task): void
    {
        Cache::forget('tasks');

        if ($task->isOverdue()) {
            Cache::forget('tasks_overdue');
        }

        Cache::forget("tasks_user_{$task->user->id}");
        Cache::forget("tasks_project_{$task->project->id}");
    }

    /**
     * Handle the Task "restored" event.
     */
    public function restored(Task $task): void
    {
        Cache::forget('tasks');

        if ($task->isOverdue()) {
            Cache::forget('tasks_overdue');
        }

        Cache::forget("tasks_user_{$task->user->id}");
        Cache::forget("tasks_project_{$task->project->id}");
    }

    /**
     * Handle the Task "force deleted" event.
     */
    public function forceDeleted(Task $task): void
    {
        Cache::forget('tasks');

        if ($task->isOverdue()) {
            Cache::forget('tasks_overdue');
        }

        Cache::forget("tasks_user_{$task->user->id}");
        Cache::forget("tasks_project_{$task->project->id}");
    }
}
