<?php

namespace App\Http\Controllers\Traits;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

trait HandlesControllerErrors
{
    /**
     * Handle exceptions in controller actions
     */
    protected function handleException(Exception $e, string $action = 'operation', bool $isJson = false)
    {
        // Log the error for debugging
        logger()->error("Controller error in {$action}", [
            'exception' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        // Handle validation exceptions differently
        if ($e instanceof ValidationException) {
            if ($isJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }

            return back()
                ->withInput()
                ->withErrors($e->errors());
        }

        // Handle general exceptions
        $message = "Error during {$action}: " . $e->getMessage();

        if ($isJson) {
            return response()->json([
                'success' => false,
                'message' => $message
            ], 500);
        }

        return back()->withError($message);
    }

    /**
     * Return success response
     */
    protected function successResponse(string $message, $data = null, bool $isJson = false)
    {
        if ($isJson) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $data
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Return error response
     */
    protected function errorResponse(string $message, bool $isJson = false, int $statusCode = 500)
    {
        if ($isJson) {
            return response()->json([
                'success' => false,
                'message' => $message
            ], $statusCode);
        }

        return back()->withError($message);
    }

    /**
     * Handle not found scenarios
     */
    protected function notFoundResponse(string $resource = 'Resource', bool $isJson = false)
    {
        $message = "{$resource} not found.";

        if ($isJson) {
            return response()->json([
                'success' => false,
                'message' => $message
            ], 404);
        }

        return redirect()->back()->withError($message);
    }

    /**
     * Handle unauthorized scenarios
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized access', bool $isJson = false)
    {
        if ($isJson) {
            return response()->json([
                'success' => false,
                'message' => $message
            ], 403);
        }

        return redirect()->back()->withError($message);
    }

    /**
     * Redirect with success message
     */
    protected function redirectWithSuccess(string $route, string $message, array $parameters = []): RedirectResponse
    {
        return redirect()->route($route, $parameters)->with('success', $message);
    }

    /**
     * Redirect with error message
     */
    protected function redirectWithError(string $route, string $message, array $parameters = []): RedirectResponse
    {
        return redirect()->route($route, $parameters)->withError($message);
    }

    /**
     * Check if request expects JSON response
     */
    protected function expectsJson(): bool
    {
        return request()->expectsJson() || request()->isJson() || request()->ajax();
    }
}