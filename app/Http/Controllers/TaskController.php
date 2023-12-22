<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\TaskService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource with a deadline in the past
     */
    public function indexPastDeadline()
    {
        $this->authorize('viewAny', Task::class);

        $tasks = Cache::remember('tasks_overdue', Carbon::now()->addMinutes(15), fn () => Task::with([
            'user',
            'project',
        ])
            ->where('deadline', '<=', Carbon::now())
            ->get()
        );

        return TaskResource::collection($tasks);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Task::class);

        $tasks = Cache::remember('tasks', Carbon::now()->addMinutes(15), function () {
            return Task::with('user', 'project')->get();
        });

        return TaskResource::collection($tasks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request, TaskService $taskService)
    {
        $this->authorize('create', Task::class);

        $task = $taskService->createTask($request);

        return new TaskResource($task);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        $this->authorize('view', $task);

        $task->load([
            'user',
            'project',
        ]);

        return new TaskResource($task);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task, TaskService $taskService)
    {
        $this->authorize('update', $task);

        $task = $taskService->updateTask($request, $task);

        return new TaskResource($task);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json([], 204);
    }
}
