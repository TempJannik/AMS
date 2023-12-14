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

class TaskController extends Controller
{
    /**
     * Display a listing of the resource for a specific user.
     */
    public function indexPastDeadline()
    {
        $tasks = Task::where('deadline', '<', Carbon::now())->get();
        return response()->json($tasks);
    }

    /**
     * Display a listing of the resource for a specific user.
     */
    public function indexForUser(int $user_id)
    {
        $user = User::findOrFail($user_id);
        $tasks = $user->tasks()->with('user', 'project')->get();
        return response()->json($tasks);
    }

    /**
     * Display a listing of the resource for a specific project.
     */
    public function indexForProject(int $project_id)
    {
        $project = Project::findOrFail($project_id);
        $tasks = $project->tasks()->with('user', 'project')->get();
        return response()->json($tasks);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tasks = Task::with('user', 'project')->get();
        return response()->json($tasks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'status' => ['required', Rule::in(['todo', 'in_progress', 'done'])],
            'user_id' => 'required|exists:users,id',
            'project_id' => 'required|exists:projects,id',
            'deadline' => 'nullable|date'
        ]);

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
        return response()->json($task);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        $task = Task::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'status' => ['required', Rule::in(['todo', 'in_progress', 'done'])],
            'user_id' => 'required|exists:users,id',
            'project_id' => 'required|exists:projects,id',
            'deadline' => 'nullable|date'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $task->update($request->all());
        return response()->json($task);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, int $id)
    {
        $task = Task::findOrFail($id);
        $task->delete();
        return response()->json([], 204);
    }
}
