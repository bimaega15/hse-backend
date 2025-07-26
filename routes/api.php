<?php
// routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ReportController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\MasterDataController;

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

    // Profile update routes
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/profile', [AuthController::class, 'updateProfile']); // Method spoofing support

    // Profile image specific routes
    Route::post('/profile/image/upload', [AuthController::class, 'uploadProfileImage']);
    Route::delete('/profile/image', [AuthController::class, 'deleteProfileImage']);

    // Password change
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

    // Notifications routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
    });
});

// Master Data Routes
Route::middleware('auth:sanctum')->group(function () {
    // Get all master data (categories separate, contributings with actions)
    Route::get('/master-data/all', [MasterDataController::class, 'getAllMasterData']);

    // Get categories (standalone)
    Route::get('/master-data/categories', [MasterDataController::class, 'getCategories']);

    // Get contributings with their actions
    Route::get('/master-data/contributings', [MasterDataController::class, 'getContributings']);

    // Get specific contributing detail with actions
    Route::get('/master-data/contributings/{contributingId}', [MasterDataController::class, 'getContributingDetail']);

    // Get actions by contributing
    Route::get('/master-data/contributings/{contributingId}/actions', [MasterDataController::class, 'getActionsByContributing']);

    // Get all actions
    Route::get('/master-data/actions', [MasterDataController::class, 'getActions']);

    // Search master data
    Route::get('/master-data/search', [MasterDataController::class, 'search']);

    // Get full path for an action (contributing → action)
    Route::get('/master-data/actions/{actionId}/path', [MasterDataController::class, 'getActionPath']);

    // Get master data statistics
    Route::get('/master-data/statistics', [MasterDataController::class, 'getStatistics']);
});
