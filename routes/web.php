<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthenticationController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfileController;

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
    // Admin Dashboard Routes
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Dashboard API endpoints
    Route::prefix('admin/dashboard')->name('admin.dashboard.')->group(function () {
        Route::get('/data', [DashboardController::class, 'getData'])->name('data');
        Route::get('/recent-reports', [DashboardController::class, 'getRecentReports'])->name('recent-reports');
        Route::get('/statistics', [DashboardController::class, 'getStatistics'])->name('statistics');
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

    // Categories Management Routes
    Route::prefix('admin/categories')->name('admin.categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/data', [CategoryController::class, 'getData'])->name('data');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::get('/{id}', [CategoryController::class, 'show'])->name('show');
        Route::put('/{id}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{id}', [CategoryController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('toggle-status');
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

    // Tambahkan routes khusus admin lainnya disini
    // Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users');
    // Route::get('/admin/settings', [SettingController::class, 'index'])->name('admin.settings');
});

Route::middleware(['auth', 'role:hse_staff,admin'])->group(function () {
    Route::get('/hse/dashboard', function () {
        return view('hse.dashboard');
    })->name('hse.dashboard');

    // Tambahkan routes khusus HSE staff disini (admin juga bisa akses)
    // Route::get('/hse/reports', [HSEReportController::class, 'index'])->name('hse.reports');
    // Route::get('/hse/incidents', [IncidentController::class, 'index'])->name('hse.incidents');
});

Route::middleware(['auth', 'role:employee,hse_staff,admin'])->group(function () {
    Route::get('/employee/dashboard', function () {
        return view('employee.dashboard');
    })->name('employee.dashboard');
});

// Default redirect
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        switch ($user->role) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'hse_staff':
                return redirect()->route('hse.dashboard');
            case 'employee':
                return redirect()->route('employee.dashboard');
            default:
                return redirect()->route('admin.dashboard');
        }
    }
    return redirect()->route('admin.login');
});
