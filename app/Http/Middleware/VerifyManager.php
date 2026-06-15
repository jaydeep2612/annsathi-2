<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyManager
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->is_super_admin || $user->hasAnyRole(['restaurant_admin', 'branch_admin', 'manager'])) {
                return $next($request);
            }
        }

        abort(403, 'Unauthorized. Manager access only.');
    }
}
