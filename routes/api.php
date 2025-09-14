<?php
// routes/api.php (Complete API routes with ALL existing endpoints)

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ReportController;
use App\Http\Controllers\API\ReportDetailController;
use App\Http\Controllers\API\MasterDataController;
use App\Http\Controllers\API\BannerController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\ObservationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/

// IMPORTANT: Authentication Login - Public endpoint
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Authentication Required)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // IMPORTANT: Authentication routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);

    // Profile management routes
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/profile', [AuthController::class, 'updateProfile']); // Method spoofing support

    // Profile image specific routes
    Route::post('/profile/image/upload', [AuthController::class, 'uploadProfileImage']);
    Route::delete('/profile/image', [AuthController::class, 'deleteProfileImage']);

    // Password change
    Route::put('/change-password', [AuthController::class, 'changePassword']);

    // Master Data routes
    Route::prefix('master-data')->group(function () {
        Route::get('/all', [MasterDataController::class, 'getAllMasterData']);
        Route::get('/categories', [MasterDataController::class, 'getCategories']);
        Route::get('/categories/{categoryId}/contributings', [MasterDataController::class, 'getContributingsByCategory']);
        Route::get('/contributings', [MasterDataController::class, 'getContributings']);
        Route::get('/contributings/{contributingId}', [MasterDataController::class, 'getContributingDetail']);
        Route::get('/contributings/{contributingId}/actions', [MasterDataController::class, 'getActionsByContributing']);
        Route::get('/actions', [MasterDataController::class, 'getActions']);
        Route::get('/search', [MasterDataController::class, 'search']);
        Route::get('/actions/{actionId}/path', [MasterDataController::class, 'getActionPath']);
        Route::get('/statistics', [MasterDataController::class, 'getStatistics']);
        Route::get('/users', [MasterDataController::class, 'getEmployeeUsers']);
    });

    // IMPORTANT: Dashboard API - untuk home page frontend
    Route::get('/dashboard', [ReportController::class, 'dashboard']);

    // Banner management routes
    Route::prefix('banners')->group(function () {
        // Get active banners for frontend (public display)
        Route::get('/active', [BannerController::class, 'getActiveBanners']);

        // CRUD operations
        Route::get('/', [BannerController::class, 'index']);
        Route::post('/', [BannerController::class, 'store']);
        Route::get('/{id}', [BannerController::class, 'show']);
        Route::put('/{id}', [BannerController::class, 'update']);
        Route::post('/{id}', [BannerController::class, 'update']); // Method spoofing support
        Route::delete('/{id}', [BannerController::class, 'destroy']);
        Route::post('/{id}/toggle', [BannerController::class, 'toggleStatus']);
        Route::post('/reorder', [BannerController::class, 'reorder']);
    });

    // Reports CRUD routes
    Route::prefix('reports')->group(function () {
        // Main report routes
        Route::get('/', [ReportController::class, 'index']);
        Route::post('/', [ReportController::class, 'store']);
        Route::get('/{id}', [ReportController::class, 'show']);
        Route::put('/{id}', [ReportController::class, 'update']);
        Route::post('/{id}', [ReportController::class, 'update']); // Method spoofing support for file uploads
        Route::delete('/{id}', [ReportController::class, 'destroy']);

        // HSE Staff specific actions
        Route::post('/{id}/start-process', [ReportController::class, 'startProcess']);
        Route::post('/{id}/complete', [ReportController::class, 'complete']);

        // Reports statistics
        Route::get('/statistics/dashboard', [ReportController::class, 'statistics']);

        // Analytics API endpoint
        Route::get('/analytics/get-report', [ReportController::class, 'getAnalyticsFiltered']);

        // NEW: Report Details routes (nested under reports)
        Route::prefix('{reportId}/details')->group(function () {
            // List all details for a specific report
            Route::get('/', [ReportDetailController::class, 'index']);

            // Create new detail for a report (HSE staff only)
            Route::post('/', [ReportDetailController::class, 'store']);

            // Get specific detail
            Route::get('/{detailId}', [ReportDetailController::class, 'show']);

            // Update specific detail (HSE staff only)
            Route::put('/{detailId}', [ReportDetailController::class, 'update']);
            Route::post('/{detailId}', [ReportDetailController::class, 'update']); // Method spoofing support

            // Delete specific detail (HSE staff only)
            Route::delete('/{detailId}', [ReportDetailController::class, 'destroy']);

            // Quick status update
            Route::patch('/{detailId}/status', [ReportDetailController::class, 'updateStatus']);
        });

        // Report details statistics (global)
        Route::get('/details/statistics', [ReportDetailController::class, 'statistics']);
    });

    // Observations routes
    Route::prefix('observations')->group(function () {
        // List observations (with filtering, search, pagination)
        Route::get('/', [ObservationController::class, 'index']);

        // Create new observation
        Route::post('/', [ObservationController::class, 'store']);

        // Get specific observation by ID
        Route::get('/{id}', [ObservationController::class, 'show']);

        // Update specific observation by ID (only owner, status = draft)
        Route::put('/{id}', [ObservationController::class, 'update']);
        Route::post('/{id}', [ObservationController::class, 'update']); // Method spoofing support

        // Delete specific observation by ID (only owner, status = draft)
        Route::delete('/{id}', [ObservationController::class, 'destroy']);

        // Submit observation for review
        Route::post('/{id}/submit', [ObservationController::class, 'submit']);

        // Review observation (HSE staff only)
        Route::post('/{id}/review', [ObservationController::class, 'review']);

        // Observation statistics
        Route::get('/statistics/dashboard', [ObservationController::class, 'statistics']);

        // Dashboard data
        Route::get('/dashboard/data', [ObservationController::class, 'dashboard']);
    });

    // Notifications routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
    });
});
