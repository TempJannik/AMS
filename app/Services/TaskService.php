<?php

namespace App\Services;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;

class TaskService
{
    public function createTask(StoreTaskRequest $request): Task
    {
        $task = new Task();      
        $task->fill($request->only($task->fillable));
        $task->setUser($request->post('user_id'));
        $task->setProject($request->post('project_id'));
        $task->save();

        return $task;
    }

    public function updateTask(UpdateTaskRequest $request, Task $task): Task
    {
        $task->update($request->only([$task->fillable]));
        return $task;
    }
}