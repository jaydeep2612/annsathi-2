<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyRestaurantAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->is_super_admin || $user->hasRole('restaurant_admin')) {
                return $next($request);
            }
        }

        abort(403, 'Unauthorized. Restaurant Admin access only.');
    }
}
