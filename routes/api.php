<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TagController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\api\HomeController;
use App\Http\Controllers\ExpenseGroupController;

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

Route::post('register', [AuthController::class, 'register']); 
Route::post('login', [AuthController::class, 'login']); 

Route::middleware('auth:sanctum')->group(function() {
    Route::get('user', [AuthController::class, 'user']); 
    Route::post('logout', [AuthController::class, 'logout']); 
    Route::post('/expenses/{expense}/tags', [ExpenseController::class, 'attachTags']); 
    Route::apiResource('expenses', ExpenseController::class);
    Route::apiResource('groups', GroupController::class);
    Route::post('groups/{group}/expenses', [ExpenseGroupController::class, 'store']); 
    Route::get('groups/{group}/expenses', [ExpenseGroupController::class, 'index']); 
    Route::get('groups/{group}/balances ', [ExpenseGroupController::class, 'balances']); 
});

Route::apiResource('tags', TagController::class);


Route::get('/home',[HomeController::class,'index']);
