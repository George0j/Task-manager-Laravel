<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;

Route::prefix('tasks')->group(function () {
    Route::post('/', [TaskController::class, 'store']);
    Route::get('/', [TaskController::class, 'index']);
    Route::patch('{task}/status', [TaskController::class, 'updateStatus']);
    Route::delete('{task}', [TaskController::class, 'destroy']);
    Route::get('report', [TaskController::class, 'dailyReport']);
    Route::get('{task}', [TaskController::class, 'show']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
