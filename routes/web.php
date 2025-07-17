<?php

use Illuminate\Support\Facades\Route;

// Catch-all route for Vue.js SPA (everything including auth)
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');

// Dummy dashboard route for tests
Route::get('/dashboard', function () {
    return 'Dashboard';
});
