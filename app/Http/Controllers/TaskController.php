<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use Illuminate\Validation\Rule;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
        $tasks = Cache::remember("tasks_overdue", now()->addMinutes(15), function () {
            return Task::with('user', 'project')->where('deadline', '<', Carbon::now())->get();
        });

        return response()->json($tasks);
    }

    /**
     * Display a listing of the resource for a specific user.
     */
    public function indexForUser(int $user_id)
    {
        $this->authorize('viewAny', Task::class);
        $user = User::findOrFail($user_id);
        $tasks = Cache::remember("tasks_project_{$user_id}", now()->addMinutes(15), function () use ($user) {
            return $user->tasks()->with('user', 'project')->get();
        });
        return response()->json($tasks);
    }

    /**
     * Display a listing of the resource for a specific project.
     */
    public function indexForProject(int $project_id)
    {
        $this->authorize('viewAny', Task::class);
        $project = Project::findOrFail($project_id);
        $tasks = Cache::remember("tasks_project_{$project_id}", now()->addMinutes(15), function () use ($project) {
            return $project->tasks()->with('user', 'project')->get();
        });
        
        return response()->json($tasks);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Task::class);
        $tasks = Cache::remember('tasks', now()->addMinutes(15), function () {
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
        $validator = Validator::make($request->all(), Task::validationRules());

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

        $validator = Validator::make($request->only($task->editable()), Task::validationRules());

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
