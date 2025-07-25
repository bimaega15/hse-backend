<?php
// app/Exceptions/Handler.php

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
     */
    public function render($request, Throwable $e)
    {
        // Handle API routes with JSON responses
        if ($request->is('api/*') || $request->expectsJson()) {
            return $this->handleApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Handle API exceptions and return JSON responses
     */
    private function handleApiException($request, Throwable $e)
    {
        if ($e instanceof AuthenticationException) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Unauthenticated. Please login.',
                    'error_code' => 'UNAUTHENTICATED',
                ],
                401,
            );
        }

        if ($e instanceof ValidationException) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                    'error_code' => 'VALIDATION_ERROR',
                ],
                422,
            );
        }

        if ($e instanceof ModelNotFoundException) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Resource not found',
                    'error_code' => 'RESOURCE_NOT_FOUND',
                ],
                404,
            );
        }

        if ($e instanceof NotFoundHttpException) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Endpoint not found',
                    'error_code' => 'ENDPOINT_NOT_FOUND',
                ],
                404,
            );
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Method not allowed',
                    'error_code' => 'METHOD_NOT_ALLOWED',
                ],
                405,
            );
        }

        // Handle general exceptions
        $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

        if ($statusCode === 403) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Access denied. Insufficient permissions.',
                    'error_code' => 'ACCESS_DENIED',
                ],
                403,
            );
        }

        // For development, show detailed error
        if (config('app.debug')) {
            return response()->json(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'error_code' => 'INTERNAL_SERVER_ERROR',
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ],
                $statusCode >= 100 && $statusCode < 600 ? $statusCode : 500,
            );
        }

        // For production, show generic error
        return response()->json(
            [
                'success' => false,
                'message' => 'Internal server error',
                'error_code' => 'INTERNAL_SERVER_ERROR',
            ],
            500,
        );
    }
}
