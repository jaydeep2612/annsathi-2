<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyChef
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->is_super_admin || $user->hasAnyRole(['restaurant_admin', 'branch_admin', 'manager', 'chef'])) {
                return $next($request);
            }
        }

        abort(403, 'Unauthorized. Kitchen staff access only.');
    }
}
