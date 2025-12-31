<?php

namespace App\Http\Middleware;

use App\Enums\UserType;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictUserType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$allowedTypes): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $userType = $user->user_type;

        // Check if user type is in allowed types
        $allowed = false;
        foreach ($allowedTypes as $type) {
            if ($userType->value === $type || $userType === UserType::Admin) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed) {
            return response()->json([
                'error' => 'Access denied. This account does not have permission to access this resource.'
            ], 403);
        }

        return $next($request);
    }
}

