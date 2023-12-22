<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UserTaskListController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(User $user)
    {
        $this->authorize('viewAny', Task::class);

        $tasks = Cache::remember("tasks_project_{$user->id}", Carbon::now()->addMinutes(15), fn () => 
            $user->load([
                'tasks.user',
                'tasks.project',
            ])->tasks
        );

        return TaskResource::collection($tasks);
    }
}
