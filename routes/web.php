<?php
// routes/web.php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Add named login route to prevent redirect errors
Route::get('/login', function () {
    return response()->json([
        'message' => 'This is web login route. Use POST /api/login for API authentication.',
        'api_login_url' => url('/api/login')
    ]);
})->name('login');
