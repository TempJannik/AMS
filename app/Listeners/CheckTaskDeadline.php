<?php

namespace App\Listeners;

use App\Events\TaskUpdated;
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
