<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogRequestTiming
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $duration = (microtime(true) - $startTime) * 1000; // in milliseconds

        // Log request timing details if it exceeds 200ms
        if ($duration > 200) {
            Log::warning(sprintf(
                "Slow Request Detected: %s %s [Duration: %.2fms] [IP: %s] [Agent: %s]",
                $request->method(),
                $request->fullUrl(),
                $duration,
                $request->ip(),
                $request->userAgent()
            ));
        }

        return $response;
    }
}
