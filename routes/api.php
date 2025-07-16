<?php

use App\Http\Controllers\Api\CustomerController;
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

// Customer routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('customers', CustomerController::class);
    Route::get('customers/search', [CustomerController::class, 'search'])->name('customers.search');
    Route::get('customers/by-status', [CustomerController::class, 'byStatus'])->name('customers.by-status');
    Route::get('customers/by-assigned-user', [CustomerController::class, 'byAssignedUser'])->name('customers.by-assigned-user');
});
