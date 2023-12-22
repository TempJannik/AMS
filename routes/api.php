<?php

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectTaskListController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserTaskListController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::controller(TaskController::class)->group(function () {
        Route::get('/tasks', 'index');
        Route::get('/tasks/past-deadline', 'indexPastDeadline');
        Route::get('/tasks/{task}', 'show');
        Route::post('/tasks', 'store');
        Route::put('/tasks/{task}', 'update');
        Route::delete('/tasks/{task}', 'destroy');
    });
    Route::controller(ProjectController::class)->group(function () {
        Route::get('/projects', 'index');
        Route::get('/projects/{project}', 'show');
        Route::post('/projects', 'store');
        Route::put('/projects/{project}', 'update');
        Route::delete('/projects/{project}', 'destroy');
    });

    Route::prefix('users/{user}/tasks')->group(function () {
        Route::get('/', UserTaskListController::class);
    });

    Route::prefix('projects/{project}/tasks')->group(function () {
        Route::get('/', ProjectTaskListController::class);
    });
});
