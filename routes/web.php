<?php
// routes/web.php

use App\Http\Controllers\Admin\AuthenticationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthenticationController::class, 'index']);
