<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = ['current_password', 'password', 'password_confirmation'];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     * PERBAIKAN: Prioritaskan API response handling
     */
    public function render($request, Throwable $e)
    {
        // CRITICAL: Handle API routes FIRST
        if ($request->is('api/*') || $request->expectsJson()) {
            return $this->handleApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Handle API exceptions and return JSON responses
     * PERBAIKAN: Comprehensive API exception handling
     */
    private function handleApiException($request, Throwable $e)
    {
        // Authentication Exception - HIGHEST PRIORITY
        if ($e instanceof AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please provide valid Bearer token.',
                'error_code' => 'UNAUTHENTICATED',
                'timestamp' => now()->toISOString()
            ], 401);
        }

        // Validation Exception
        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $e->errors(),
                'error_code' => 'VALIDATION_ERROR',
                'timestamp' => now()->toISOString()
            ], 422);
        }

        // Model Not Found Exception
        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found',
                'error_code' => 'MODEL_NOT_FOUND',
                'timestamp' => now()->toISOString()
            ], 404);
        }

        // HTTP Not Found Exception
        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint not found',
                'error_code' => 'ENDPOINT_NOT_FOUND',
                'timestamp' => now()->toISOString()
            ], 404);
        }

        // Method Not Allowed Exception
        if ($e instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Method not allowed',
                'error_code' => 'METHOD_NOT_ALLOWED',
                'timestamp' => now()->toISOString()
            ], 405);
        }

        // Generic Exception
        $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
        $message = $e->getMessage() ?: 'Internal server error';

        // Don't expose sensitive info in production
        if (app()->environment('production')) {
            $message = $statusCode === 500 ? 'Internal server error' : $message;
        }

        return response()->json([
            'success' => false,
            'message' => $message,
            'error_code' => 'INTERNAL_SERVER_ERROR',
            'timestamp' => now()->toISOString()
        ], $statusCode);
    }

    /**
     * Convert an authentication exception into a response.
     * BACKUP: Jika render method tidak catch AuthenticationException
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        // API request handling
        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please provide valid Bearer token.',
                'error_code' => 'UNAUTHENTICATED',
                'timestamp' => now()->toISOString()
            ], 401);
        }

        // Web request - redirect to login
        return redirect()->guest(route('login'));
    }
}
