<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\EstimateController;

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

    // Estimate routes (specific routes must come before resource route)
    Route::get('/estimates/search', [EstimateController::class, 'search']);
    Route::get('/estimates/status/{status}', [EstimateController::class, 'byStatus']);
    Route::get('/estimates/customer/{customerId}', [EstimateController::class, 'byCustomer']);
    Route::get('/estimates/status/expired', [EstimateController::class, 'expired']);
    Route::get('/estimates/status/pending', [EstimateController::class, 'pending']);
    Route::get('/estimates/status/sent', [EstimateController::class, 'sent']);
    Route::get('/estimates/status/accepted', [EstimateController::class, 'accepted']);
    Route::get('/estimates/status/rejected', [EstimateController::class, 'rejected']);
    Route::apiResource('estimates', EstimateController::class);
    Route::post('/estimates/{id}/mark-sent', [EstimateController::class, 'markAsSent']);
    Route::post('/estimates/{id}/mark-accepted', [EstimateController::class, 'markAsAccepted']);
    Route::post('/estimates/{id}/mark-rejected', [EstimateController::class, 'markAsRejected']);
    Route::post('/estimates/{id}/mark-expired', [EstimateController::class, 'markAsExpired']);
    Route::get('/estimates/{id}/pdf', [EstimateController::class, 'generatePdf']);
        // Appointment specific routes (must come before resource route)
    Route::get('/appointments/upcoming', [\App\Http\Controllers\Api\AppointmentController::class, 'upcoming']);
    Route::get('/appointments/by-date', [\App\Http\Controllers\Api\AppointmentController::class, 'byDate']);
    Route::get('/appointments/by-user/{userId}', [\App\Http\Controllers\Api\AppointmentController::class, 'byUser']);
    Route::get('/appointments/by-customer/{customerId}', [\App\Http\Controllers\Api\AppointmentController::class, 'byCustomer']);
    Route::post('/appointments/check-availability', [\App\Http\Controllers\Api\AppointmentController::class, 'checkAvailability']);
    Route::patch('/appointments/{id}/confirm', [\App\Http\Controllers\Api\AppointmentController::class, 'markAsConfirmed']);
    Route::patch('/appointments/{id}/complete', [\App\Http\Controllers\Api\AppointmentController::class, 'markAsCompleted']);
    Route::patch('/appointments/{id}/cancel', [\App\Http\Controllers\Api\AppointmentController::class, 'markAsCancelled']);
    Route::patch('/appointments/{id}/no-show', [\App\Http\Controllers\Api\AppointmentController::class, 'markAsNoShow']);

    Route::apiResource('appointments', \App\Http\Controllers\Api\AppointmentController::class);
    Route::apiResource('users', \App\Http\Controllers\Api\UserController::class);
});
