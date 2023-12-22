<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskResource;
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
    public function __invoke(Project $project)
    {
        $this->authorize('viewAny', Task::class);

        $tasks = Cache::remember("tasks_project_{$project->id}", Carbon::now()->addMinutes(15), fn () => $project->load([
            'tasks.user',
            'tasks.project',
        ])->tasks
        );

        return TaskResource::collection($tasks);
    }
}
