<?php

namespace App\Http\Controllers;

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
    public function __invoke(Request $request, int $userId)
    {
        $this->authorize('viewAny', Task::class);

        $tasks = Cache::remember("tasks_project_{$userId}", Carbon::now()->addMinutes(15), fn () => User::with([
            'tasks.user',
            'tasks.project',
        ])
            ->findOrFail($userId)
            ->tasks
        );

        return response()->json($tasks);
    }
}
