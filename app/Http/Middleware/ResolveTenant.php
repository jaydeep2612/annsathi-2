<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\TenantResolver;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;

class ResolveTenant
{
    protected TenantResolver $resolver;

    public function __construct(TenantResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->runningUnitTests()) {
            app()->offsetUnset('tenant.restaurant_id');
            app()->offsetUnset('tenant.branch_id');
        }

        // 1. Resolve for Authenticated User (Admin/Staff panels)
        if ($user = $request->user()) {
            if ($user->restaurant_id) {
                $this->resolver->setRestaurantId($user->restaurant_id);
            }
            if ($user->branch_id) {
                $this->resolver->setBranchId($user->branch_id);
            }
        }
        // 2. Resolve for Customer API requests via session token
        elseif ($token = $request->header('X-Session-Token') ?: $request->input('session_token')) {
            try {
                // Fetch session details from customer_sessions
                $session = DB::table('customer_sessions')
                    ->where('session_token', $token)
                    ->first();

                if ($session) {
                    $this->resolver->setRestaurantId($session->restaurant_id);
                    if ($session->branch_id) {
                        $this->resolver->setBranchId($session->branch_id);
                    }
                }
            } catch (\Exception $e) {
                // Ignore resolve issues, fall back to default
            }
        }
        
        // 3. Resolve from headers or parameters for session starts
        if (!app()->bound('tenant.restaurant_id')) {
            $restaurantId = $request->header('X-Restaurant-ID') ?: $request->input('restaurant_id');
            if ($restaurantId) {
                $this->resolver->setRestaurantId((int) $restaurantId);
            }
            $branchId = $request->header('X-Branch-ID') ?: $request->input('branch_id');
            if ($branchId) {
                $this->resolver->setBranchId((int) $branchId);
            }
        }

        return $next($request);
    }
}
