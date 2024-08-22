<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // If the request expects JSON (API request), return null to avoid redirecting
        return $request->expectsJson() ? null : route('login');
    }

    /**
     * Handle unauthenticated users for API requests.
     */
    protected function unauthenticated($request, array $guards)
    {
        // For API requests, return a 401 Unauthorized JSON response
        if ($request->expectsJson()) {
            abort(response()->json(['message' => 'Unauthenticated.'], 401));
        }

        // For non-API requests, fall back to default behavior (redirect)
        parent::unauthenticated($request, $guards);
    }
}

