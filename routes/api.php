<?php

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
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
        Route::get('/tasks/{id}', 'show');
        Route::post('/tasks', 'store');
        Route::put('/tasks/{id}', 'update');
        Route::delete('/tasks/{id}', 'destroy');
    });
    Route::controller(ProjectController::class)->group(function () {
        Route::get('/projects', 'index');
        Route::get('/projects/{id}', 'show');
        Route::post('/projects', 'store');
        Route::put('/projects/{id}', 'update');
        Route::delete('/projects/{id}', 'destroy');
    });

    Route::prefix('users/{user}/tasks')->group(function () {
        Route::get('/', [TaskController::class, 'indexForUser']);
    });

    Route::prefix('projects/{project}/tasks')->group(function () {
        Route::get('/', [TaskController::class, 'indexForProject']);
    });
});
