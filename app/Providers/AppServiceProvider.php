<?php
// app/Providers/AppServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Services\ReportService;
use App\Services\NotificationService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind services to container
        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService();
        });

        $this->app->singleton(ReportService::class, function ($app) {
            return new ReportService($app->make(NotificationService::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set default string length for older MySQL versions
        Schema::defaultStringLength(191);

        // Configure API response format
        $this->configureApiResponses();
    }

    /**
     * Configure API response formats
     */
    private function configureApiResponses(): void
    {
        // Add custom response macros
        \Illuminate\Http\JsonResponse::macro('success', function ($data = null, $message = 'Success', $status = 200) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $data,
                'timestamp' => now()->toISOString()
            ], $status);
        });

        \Illuminate\Http\JsonResponse::macro('error', function ($message = 'Error', $errors = null, $status = 400) {
            $response = [
                'success' => false,
                'message' => $message,
                'timestamp' => now()->toISOString()
            ];

            if ($errors) {
                $response['errors'] = $errors;
            }

            return response()->json($response, $status);
        });
    }
}
