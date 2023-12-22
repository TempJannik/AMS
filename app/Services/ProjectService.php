<?php

namespace App\Services;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;

class ProjectService
{
    public function updateProject(UpdateProjectRequest $request, Project $project): Project
    {
        $project->update($request->only(['name']));
        return $project;
    }

    public function createProject(StoreProjectRequest $request): Project
    {
        $project = new Project();
        $project->fill($request->only(['name']));
        $project->save();

        return $project;
    }
}