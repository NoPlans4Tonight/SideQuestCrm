<?php

use Illuminate\Support\Facades\Route;

// Authentication routes (handled by Fortify)
Route::get('/login', function () {
    return view('app');
})->name('login');

Route::get('/register', function () {
    return view('app')->name('register');
});

Route::get('/forgot-password', function () {
    return view('app')->name('password.request');
});
Route::get('/reset-password/{token}', function () {
    return view('app')->name('password.reset');
});
Route::get('/user/confirm-password', function () {
    return view('app')->name('password.confirm');
});

// Specific routes for the SPA
Route::get('/', function () {
    return view('app');
});

Route::get('/dashboard', function () {
    return view('app');
});

Route::get('/customers', function () {
    return view('app');
});

Route::get('/customers/{id}', function () {
    return view('app');
});

Route::get('/jobs', function () {
    return view('app');
});

Route::get('/jobs/{id}', function () {
    return view('app');
});

Route::get('/services', function () {
    return view('app');
});

Route::get('/services/{id}', function () {
    return view('app');
});

Route::get('/appointments', function () {
    return view('app');
});

Route::get('/appointments/create', function () {
    return view('app');
});

Route::get('/appointments/{id}', function () {
    return view('app');
});

Route::get('/estimates', function () {
    return view('app');
});

Route::get('/estimates/create', function () {
    return view('app');
});

Route::get('/estimates/{id}', function () {
    return view('app');
});

// Catch-all route for SPA - this should be the last route
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
