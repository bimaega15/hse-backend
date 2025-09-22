<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthenticationController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContributingController;
use App\Http\Controllers\Admin\ActionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ObservationController; // Add this import
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ActivatorController;

// Fallback login route for compatibility
Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

// Auth Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Guest routes (tidak perlu login)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthenticationController::class, 'index'])->name('login');
        Route::post('/login', [AuthenticationController::class, 'login'])->name('login.post');
    });

    // Authenticated routes (perlu login)
    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AuthenticationController::class, 'logout'])->name('logout');

        // Dashboard routes berdasarkan role
        Route::get('/dashboard', function () {
            $user = auth()->user();

            switch ($user->role) {
                case 'admin':
                    return app(DashboardController::class)->index();
                case 'hse_staff':
                    return view('hse.dashboard');
                case 'employee':
                    return view('employee.dashboard');
                default:
                    return app(DashboardController::class)->index();
            }
        })->name('dashboard');
    });
});

// Role-specific protected routes
Route::middleware(['auth', 'role:admin'])->group(function () {

    // Tambahkan route ini ke dalam group route admin dashboard
    Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
        Route::prefix('dashboard')->name('dashboard.')->group(function () {
            Route::get('/', [DashboardController::class, 'index'])->name('index');
            Route::get('/data', [DashboardController::class, 'getData'])->name('data');
            Route::get('/recent-reports', [DashboardController::class, 'getRecentReports'])->name('recent-reports');
            Route::get('/recent-observations', [DashboardController::class, 'getRecentObservations'])->name('recent-observations'); // Route baru
            Route::get('/statistics', [DashboardController::class, 'getStatistics'])->name('statistics');
        });
    });

    // Profile Routes - Updated with all needed endpoints
    Route::prefix('admin/profile')->name('admin.profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::post('/update', [ProfileController::class, 'updateProfile'])->name('update');
        Route::post('/password', [ProfileController::class, 'updatePassword'])->name('password');
        Route::post('/image/upload', [ProfileController::class, 'uploadProfileImage'])->name('image.upload');
        Route::delete('/image', [ProfileController::class, 'deleteProfileImage'])->name('image.delete');
        Route::get('/data', [ProfileController::class, 'getProfile'])->name('data');
    });

    // Reports Management Routes
    Route::prefix('admin/reports')->name('admin.reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/data', [ReportController::class, 'getData'])->name('data');
        Route::get('/create', [ReportController::class, 'create'])->name('create');
        Route::get('/export/excel', [ReportController::class, 'exportExcel'])->name('export.excel');
        Route::get('/statistics/data', [ReportController::class, 'getStatistics'])->name('statistics.data');
        Route::get('/actions/by-contributing/{contributingId}', [ReportController::class, 'getActionsByContributing'])->name('actions.by-contributing');
        // NEW: AJAX Analytics Filter Endpoint
        Route::post('/analytics/filter', [ReportController::class, 'getAnalyticsFiltered'])->name('analytics.filter');
        Route::post('/', [ReportController::class, 'store'])->name('store');
        Route::get('/{id}', [ReportController::class, 'show'])->name('show');
        Route::put('/{id}', [ReportController::class, 'update'])->name('update');
        Route::delete('/{id}', [ReportController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/status', [ReportController::class, 'updateStatus'])->name('update-status');
    });

    // Observations Management Routes
    Route::prefix('admin/observations')->name('admin.observations.')->group(function () {
        // Main index page with different views (default, analytics, filtered)
        Route::get('/', [ObservationController::class, 'index'])->name('index');

        // DataTables data endpoint
        Route::get('/data', [ObservationController::class, 'getData'])->name('data');

        // Filter data endpoint
        Route::get('/filter-data', [ObservationController::class, 'getFilterData'])->name('filter-data');

        // Index behavior data endpoint
        Route::get('/index-behavior-data', [ObservationController::class, 'getIndexBehaviorData'])->name('index-behavior-data');

        // CRUD operations
        Route::get('/create', [ObservationController::class, 'create'])->name('create');
        Route::post('/', [ObservationController::class, 'store'])->name('store');
        Route::get('/{id}', [ObservationController::class, 'show'])->name('show');
        Route::put('/{id}', [ObservationController::class, 'update'])->name('update');
        Route::delete('/{id}', [ObservationController::class, 'destroy'])->name('destroy');

        // Status management
        Route::patch('/{id}/status', [ObservationController::class, 'updateStatus'])->name('update-status');

        // Statistics and analytics
        Route::get('/statistics/data', [ObservationController::class, 'getStatistics'])->name('statistics.data');

        // Export Excel
        Route::get('/export/excel', [ObservationController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/grouped-data', [ObservationController::class, 'getGroupedExportData'])->name('export.grouped-data');

        // Recent observations for dashboard
        Route::get('/recent/graphic', [ObservationController::class, 'getRecent'])->name('recent');
    });

    // Categories Management Routes
    Route::prefix('admin/categories')->name('admin.categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/data', [CategoryController::class, 'getData'])->name('data');
        Route::get('/{id}/contributings', [CategoryController::class, 'getContributings'])->name('contributings');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::get('/{id}', [CategoryController::class, 'show'])->name('show');
        Route::put('/{id}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{id}', [CategoryController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('toggle-status');
    });

    // Contributing Factors Management Routes
    Route::prefix('admin/contributing')->name('admin.contributing.')->group(function () {
        Route::get('/', [ContributingController::class, 'index'])->name('index');
        Route::get('/data', [ContributingController::class, 'getData'])->name('data');
        Route::post('/', [ContributingController::class, 'store'])->name('store');
        Route::get('/{id}', [ContributingController::class, 'show'])->name('show');
        Route::put('/{id}', [ContributingController::class, 'update'])->name('update');
        Route::delete('/{id}', [ContributingController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle-status', [ContributingController::class, 'toggleStatus'])->name('toggle-status');
    });

    // Actions Management Routes
    Route::prefix('admin/actions')->name('admin.actions.')->group(function () {
        Route::get('/', [ActionController::class, 'index'])->name('index');
        Route::get('/data', [ActionController::class, 'getData'])->name('data');
        Route::post('/', [ActionController::class, 'store'])->name('store');
        Route::get('/{id}', [ActionController::class, 'show'])->name('show');
        Route::put('/{id}', [ActionController::class, 'update'])->name('update');
        Route::delete('/{id}', [ActionController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle-status', [ActionController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/by-contributing/{contributingId}', [ActionController::class, 'getByContributing'])->name('by-contributing');
    });

    // Projects Management Routes
    Route::prefix('admin/projects')->name('admin.projects.')->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('index');
        Route::get('/data', [ProjectController::class, 'getData'])->name('data');
        Route::post('/', [ProjectController::class, 'store'])->name('store');
        Route::get('/{id}', [ProjectController::class, 'show'])->name('show');
        Route::put('/{id}', [ProjectController::class, 'update'])->name('update');
        Route::delete('/{id}', [ProjectController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle-status', [ProjectController::class, 'toggleStatus'])->name('toggle-status');
    });

    // Locations Management Routes
    Route::prefix('admin/locations')->name('admin.locations.')->group(function () {
        Route::get('/', [LocationController::class, 'index'])->name('index');
        Route::get('/data', [LocationController::class, 'getData'])->name('data');
        Route::get('/active', [LocationController::class, 'getActiveLocations'])->name('active');
        Route::post('/', [LocationController::class, 'store'])->name('store');
        Route::get('/{id}', [LocationController::class, 'show'])->name('show');
        Route::put('/{id}', [LocationController::class, 'update'])->name('update');
        Route::delete('/{id}', [LocationController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle-status', [LocationController::class, 'toggleStatus'])->name('toggle-status');
    });

    // Activators Management Routes
    Route::prefix('admin/activators')->name('admin.activators.')->group(function () {
        Route::get('/', [ActivatorController::class, 'index'])->name('index');
        Route::get('/data', [ActivatorController::class, 'getData'])->name('data');
        Route::post('/', [ActivatorController::class, 'store'])->name('store');
        Route::get('/{id}', [ActivatorController::class, 'show'])->name('show');
        Route::put('/{id}', [ActivatorController::class, 'update'])->name('update');
        Route::delete('/{id}', [ActivatorController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle-status', [ActivatorController::class, 'toggleStatus'])->name('toggle-status');
    });

    // Banners Management Routes
    Route::prefix('admin/banners')->name('admin.banners.')->group(function () {
        Route::get('/', [BannerController::class, 'index'])->name('index');
        Route::get('/data', [BannerController::class, 'getData'])->name('data');
        Route::post('/', [BannerController::class, 'store'])->name('store');
        Route::get('/{id}', [BannerController::class, 'show'])->name('show');
        Route::put('/{id}', [BannerController::class, 'update'])->name('update');
        Route::delete('/{id}', [BannerController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle-status', [BannerController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/reorder', [BannerController::class, 'reorder'])->name('reorder');
    });

    // User Management Routes
    Route::prefix('admin/users')->name('admin.users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/data', [UserController::class, 'getData'])->name('data');
        Route::get('/statistics', [UserController::class, 'getStatistics'])->name('statistics');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{id}', [UserController::class, 'show'])->name('show');
        Route::put('/{id}', [UserController::class, 'update'])->name('update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
    });

    // Notifications Routes
    Route::prefix('admin/notifications')->name('admin.notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
    });

    // Tambahkan routes khusus admin lainnya disini
    // Route::get('/admin/settings', [SettingController::class, 'index'])->name('admin.settings');
});

// Master Data Routes - accessible by authenticated users
Route::middleware(['auth'])->prefix('master-data')->name('master-data.')->group(function () {
    Route::get('/categories', [CategoryController::class, 'getMasterData'])->name('categories');
    Route::get('/contributings', [ContributingController::class, 'getMasterData'])->name('contributings');
    Route::get('/actions', [ActionController::class, 'getMasterData'])->name('actions');
    Route::get('/locations', [LocationController::class, 'getMasterData'])->name('locations');
    Route::get('/projects', [ProjectController::class, 'getMasterData'])->name('projects');
    Route::get('/activators', [ActivatorController::class, 'getMasterData'])->name('activators');
});

Route::middleware(['auth', 'role:hse_staff,admin'])->group(function () {
    Route::get('/hse/dashboard', function () {
        return view('hse.dashboard');
    })->name('hse.dashboard');

    // HSE Staff can also access observations (read-only or limited access)
    Route::prefix('hse/observations')->name('hse.observations.')->group(function () {
        Route::get('/', [ObservationController::class, 'index'])->name('index');
        Route::get('/data', [ObservationController::class, 'getData'])->name('data');
        Route::get('/{id}', [ObservationController::class, 'show'])->name('show');
        Route::get('/statistics/data', [ObservationController::class, 'getStatistics'])->name('statistics.data');
        Route::get('/recent', [ObservationController::class, 'getRecent'])->name('recent');

        // HSE Staff can review observations
        Route::patch('/{id}/status', [ObservationController::class, 'updateStatus'])->name('update-status');
    });

    // Tambahkan routes khusus HSE staff disini (admin juga bisa akses)
    // Route::get('/hse/reports', [HSEReportController::class, 'index'])->name('hse.reports');
    // Route::get('/hse/incidents', [IncidentController::class, 'index'])->name('hse.incidents');
});

Route::middleware(['auth', 'role:employee,hse_staff,admin'])->group(function () {
    Route::get('/employee/dashboard', function () {
        return view('employee.dashboard');
    })->name('employee.dashboard');

    // Employee can create and view their own observations
    Route::prefix('employee/observations')->name('employee.observations.')->group(function () {
        Route::get('/', [ObservationController::class, 'index'])->name('index');
        Route::get('/data', [ObservationController::class, 'getData'])->name('data');
        Route::get('/create', [ObservationController::class, 'create'])->name('create');
        Route::post('/', [ObservationController::class, 'store'])->name('store');
        Route::get('/{id}', [ObservationController::class, 'show'])->name('show');
        Route::put('/{id}', [ObservationController::class, 'update'])->name('update');
        Route::delete('/{id}', [ObservationController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/status', [ObservationController::class, 'updateStatus'])->name('update-status');
        Route::get('/recent', [ObservationController::class, 'getRecent'])->name('recent');
    });
});

// Default redirect
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        switch ($user->role) {
            case 'admin':
                return redirect()->route('admin.dashboard.index');
            case 'hse_staff':
                return redirect()->route('hse.dashboard');
            case 'employee':
                return redirect()->route('employee.dashboard');
            default:
                return redirect()->route('admin.dashboard.index');
        }
    }
    return redirect()->route('admin.login');
});
