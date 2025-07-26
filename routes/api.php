<?php
// routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ReportController;
use App\Http\Controllers\API\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // Authentication routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/profile', [AuthController::class, 'updateProfile']); // Method spoofing support
    Route::put('/change-password', [AuthController::class, 'changePassword']);

    // Reports CRUD routes
    Route::prefix('reports')->group(function () {
        // List reports (with filtering, search, pagination)
        Route::get('/', [ReportController::class, 'index']);

        // Create new report
        Route::post('/', [ReportController::class, 'store']);

        // Get specific report by ID
        Route::get('/{id}', [ReportController::class, 'show']);

        // Update specific report by ID (only employee owner, status = waiting)
        Route::put('/{id}', [ReportController::class, 'update']);
        Route::post('/{id}', [ReportController::class, 'update']); // Method spoofing support for file uploads

        // Delete specific report by ID (only employee owner, status = waiting)
        Route::delete('/{id}', [ReportController::class, 'destroy']);

        // HSE Staff specific actions
        Route::post('/{id}/start-process', [ReportController::class, 'startProcess']);
        Route::post('/{id}/complete', [ReportController::class, 'complete']);
    });

    // Reports statistics
    Route::get('/reports-statistics', [ReportController::class, 'statistics']);

    // Alternative: Using apiResource (shorter but less explicit)
    // Route::apiResource('reports', ReportController::class);
    // Route::post('/reports/{id}/start-process', [ReportController::class, 'startProcess']);
    // Route::post('/reports/{id}/complete', [ReportController::class, 'complete']);

    // Notifications routes
    Route::prefix('notifications')->group(function () {
        // List notifications
        Route::get('/', [NotificationController::class, 'index']);

        // Mark specific notification as read
        Route::put('/{id}/read', [NotificationController::class, 'markAsRead']);

        // Mark all notifications as read
        Route::put('/mark-all-read', [NotificationController::class, 'markAllAsRead']);

        // Get unread notifications count
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);

        // Delete specific notification
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
    });
});

// Health check (public)
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0',
        'environment' => app()->environment()
    ]);
});
