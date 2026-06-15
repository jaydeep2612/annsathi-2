<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifySuperAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->is_super_admin) {
            return $next($request);
        }

        abort(403, 'Unauthorized. Super Admin access only.');
    }
}
