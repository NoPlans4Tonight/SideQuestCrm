<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\JobController;

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

// Protected routes (authentication required)
Route::middleware('auth:web')->group(function () {
    Route::get('/user', function (Request $request) {
        return response()->json(['user' => $request->user()]);
    });
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('jobs', JobController::class);
    Route::apiResource('services', \App\Http\Controllers\Api\ServiceController::class);
    Route::apiResource('users', \App\Http\Controllers\Api\UserController::class);
});
