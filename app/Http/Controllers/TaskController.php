<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource with a deadline in the past
     */
    public function indexPastDeadline()
    {
        $this->authorize('viewAny', Task::class);

        $tasks = Cache::remember('tasks_overdue', Carbon::now()->addMinutes(15), fn () => 
            Task::with([
                'user',
                'project'
            ])
            ->where('deadline', '<=', Carbon::now())
            ->get()
        );

        return response()->json($tasks);
    }

    /**
     * Display a listing of the resource for a specific user.
     */
    public function indexForUser(int $userId)
    {
        $this->authorize('viewAny', Task::class);

        $tasks = Cache::remember("tasks_project_{$userId}", Carbon::now()->addMinutes(15), fn () =>
            User::with([
                'tasks.user',
                'tasks.project',
            ])
            ->findOrFail($userId)
            ->tasks
        );

        return response()->json($tasks);
    }

    /**
     * Display a listing of the resource for a specific project.
     */
    public function indexForProject(int $projectId)
    {
        $this->authorize('viewAny', Task::class);

        $tasks = Cache::remember("tasks_project_{$projectId}", Carbon::now()->addMinutes(15), fn() =>
            Project::with([
                'tasks.user',
                'tasks.project',
            ])
            ->findOrFail($projectId)
            ->tasks
        );

        return response()->json($tasks);
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

        return response()->json($tasks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Task::class);

        $validator = Validator::make($request->all(), Task::createValidationRules());

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $task = Task::create($request->all());

        return response()->json($task, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, int $id)
    {
        $task = Task::with('user', 'project')->findOrFail($id);
        $this->authorize('view', $task);

        return response()->json($task);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        $task = Task::findOrFail($id);
        $this->authorize('update', $task);

        $validator = Validator::make($request->only($task->editable()), Task::updateValidationRules());

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $task->update($request->only($task->editable()));

        return response()->json($task);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, int $id)
    {
        $task = Task::findOrFail($id);
        $this->authorize('delete', $task);
        $task->delete();

        return response()->json([], 204);
    }
}
