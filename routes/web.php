<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('app');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('app');
    })->name('dashboard');

    Route::get('/customers', function () {
        return view('app');
    })->name('customers.index');

    Route::get('/customers/create', function () {
        return view('app');
    })->name('customers.create');

    Route::get('/customers/{id}', function () {
        return view('app');
    })->name('customers.show');

    Route::get('/customers/{id}/edit', function () {
        return view('app');
    })->name('customers.edit');

    Route::get('/jobs', function () {
        return view('app');
    })->name('jobs.index');

    Route::get('/jobs/create', function () {
        return view('app');
    })->name('jobs.create');

    Route::get('/jobs/{id}', function () {
        return view('app');
    })->name('jobs.show');

    Route::get('/jobs/{id}/edit', function () {
        return view('app');
    })->name('jobs.edit');
});
