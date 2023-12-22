<?php

namespace App\Http\Controllers;

use App\Models\Task;
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

        $tasks = Cache::remember('tasks_overdue', Carbon::now()->addMinutes(15), fn () => Task::with([
            'user',
            'project',
        ])
            ->where('deadline', '<=', Carbon::now())
            ->get()
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

        $task = new Task();
        
        $validator = Validator::make($request->only(['title', 'description', 'status', 'deadline', 'user_id', 'project_id']), Task::createValidationRules());

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        
        $task->fill($request->only($task->fillable));
        $task->setUser($request->post('user_id'));
        $task->setProject($request->post('project_id'));
        $task->save();
        return response()->json($task, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        $this->authorize('view', $task);

        $task->load([
            'user',
            'project'
        ]);
        return response()->json($task);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validator = Validator::make($request->only($task->fillable), Task::updateValidationRules());

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $task->update($request->only($task->fillable));

        return response()->json($task);
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
