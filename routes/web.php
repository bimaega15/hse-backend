<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthenticationController;
use App\Http\Controllers\Admin\DashboardController;

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
                    return view('admin.dashboard');
                case 'hse_staff':
                    return view('hse.dashboard');
                case 'employee':
                    return view('employee.dashboard');
                default:
                    return view('admin.dashboard');
            }
        })->name('dashboard');
    });
});

// Role-specific protected routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Tambahkan routes khusus admin disini
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

    // Tambahkan routes yang bisa diakses semua role disini
    // Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    // Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
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
