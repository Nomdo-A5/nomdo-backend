<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoardsController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\AttachmentController;
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
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'getActiveUser']);

    Route::get('/boards',  [BoardsController::class, 'index']);
    Route::post('/boards', [BoardsController::class, 'store']);
    Route::patch('/boards',  [BoardsController::class, 'update']);
    Route::delete('/boards', [BoardsController::class, 'destroy']);
    Route::get('/boards/task-information', [BoardsController::class, 'taskCount']);

    Route::post('/workspace', [WorkspaceController::class,'create']);
    Route::get('/workspace', [WorkspaceController::class,'show']);
    Route::delete('/workspace', [WorkspaceController::class,'delete']);
    Route::patch('/workspace', [WorkspaceController::class,'update']);
    Route::get('/workspace/{workspace}/boards', [WorkspaceController::class,'filterTask']);
    Route::get('/workspace/{workspace}/boards/{boards}/tasks', [WorkspaceController::class,'getTasks']);

    Route::get('/workspace/task-information', [WorkspaceController::class, 'getTaskInfo']);
    Route::get('/workspace/board-information', [WorkspaceController::class, 'getBoardInfo']);
    Route::get('/workspace/member', [WorkspaceController::class, 'getMember']);

    Route::get('/join',[WorkspaceController::class ,'join']);
    
    Route::post('/task', [TaskController::class,'create']);

    Route::get('/task', [TaskController::class, 'index']);
    Route::patch('/task', [TaskController::class, 'update']);
    Route::delete('/task', [TaskController::class, 'delete']);
    Route::get('/task/member', [TaskController::class, 'getMember']);
    Route::get('/task_due', [TaskController::class, 'filterdue_date']);

    Route::post('/balance', [BalanceController::class,'create']);
    Route::get('/balance', [BalanceController::class,'index']);
    Route::put('/balance', [BalanceController::class,'update']);
    Route::delete('/balance', [BalanceController::class,'delete']);
    
    Route::get('/report', [ReportController::class,'select']);
    Route::get('/report/overview', [ReportController::class,'overview']);
    Route::post('/report', [ReportController::class,'create']);

    Route::post('/attachment', [AttachmentController::class,'create']);

    Route::get('/search',[SearchController::class,'search']);
});
Route::post('/register', [AuthController::class,'register']);
Route::post('/login', [AuthController::class,'login']);

