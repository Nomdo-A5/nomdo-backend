<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\BoardsController;

use App\Http\Controllers\WorkspaceController;

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
    Route::post('/logout', 'AuthController@logout');

    Route::get('/boards', 'BoardsController@index');
    Route::post('/boards/store','BoardsController@store');
    Route::get('/article/edit/{id}', 'ArticleController@getArticle');
    Route::put('/boards/update/{id}', 'BoardsController@update');
    Route::delete('boards/delete/{id}','BoardsController@delete');

    Route::post('/workspace', [WorkspaceController::class,'create']);
    Route::get('/workspace', [WorkspaceController::class,'show']);


});

Route::post('/register', 'AuthController@register');
Route::get('/login', 'AuthController@login');


Route::get('/articles', 'ArticleController@index');
Route::post('/article/store', 'ArticleController@store');
Route::get('/article/edit/{id}', 'ArticleController@getArticle');
Route::get('/article/{id}', 'ArticleController@getArticle');
Route::put('/article/{id}', 'ArticleController@update');
Route::delete('/article/delete/{id}', 'ArticleController@delete');