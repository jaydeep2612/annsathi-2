<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyWaiter
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->is_super_admin || $user->hasAnyRole(['restaurant_admin', 'branch_admin', 'manager', 'waiter'])) {
                return $next($request);
            }
        }

        abort(403, 'Unauthorized. Waiter staff access only.');
    }
}
