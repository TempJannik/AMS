<?php

namespace App\Listeners;

use App\Events\TaskUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Carbon\Carbon;

class CheckTaskDeadline
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TaskUpdated $event): void
    {
        $task = $event->task;

        if ($task->deadline < Carbon::now()) {
            $task->user->notify(new \App\Notifications\TaskOverdueNotification($task));
        }
    }
}
