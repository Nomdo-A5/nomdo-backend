<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoardsController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\TaskController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('/logout', [AuthController::class,'logout']);

    Route::get('/boards',  [BoardsController::class,'index']);
    Route::post('/boards', [BoardsController::class,'store']);
    Route::get('/boards', [BoardsController::class,'edit']);
    Route::put('/boards',  [BoardsController::class,'update']);
    Route::delete('/boards', [BoardsController::class,'delete']);

    Route::post('/workspace', [WorkspaceController::class,'create']);
    Route::get('/workspace', [WorkspaceController::class,'show']);
    Route::delete('/workspace', [WorkspaceController::class,'delete']);
    Route::patch('/workspace', [WorkspaceController::class,'update']);

    Route::post('/task', [TaskController::class,'create']);
    Route::get('/task', [TaskController::class, 'index']);
    Route::patch('/task',[TaskController::class, 'update']);
    Route::delete('/task',[TaskController::class, 'delete']);
    
});
Route::post('/register', [AuthController::class,'register']);
Route::post('/login', [AuthController::class,'login']);
