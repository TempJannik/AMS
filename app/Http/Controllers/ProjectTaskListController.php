<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProjectTaskListController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, int $projectId)
    {
        $this->authorize('viewAny', Task::class);

        $tasks = Cache::remember("tasks_project_{$projectId}", Carbon::now()->addMinutes(15), fn () => Project::with([
            'tasks.user',
            'tasks.project',
        ])
            ->findOrFail($projectId)
            ->tasks
        );

        return response()->json($tasks);
    }
}
