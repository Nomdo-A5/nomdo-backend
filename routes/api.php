<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoardsController;
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
    Route::post('/logout',[AuthController::class, 'logout']);
    Route::post('/boards','BoardsController@store');
    Route::post('/boards/update/{id?}', 'BoardsController@update');
});

Route::post('/register', 'AuthController@classregister');
Route::get('/login', 'AuthController@login');