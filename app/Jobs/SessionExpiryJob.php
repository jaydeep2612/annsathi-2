<?php

namespace App\Jobs;

use App\Models\CustomerSession;
use App\Services\SessionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class SessionExpiryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(SessionService $sessionService): void
    {
        Log::info("Running SessionExpiryJob...");

        // Fetch active/bill_requested sessions that have expired
        $expiredSessions = CustomerSession::whereIn('status', ['active', 'bill_requested'])
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expiredSessions as $session) {
            try {
                // Bind tenant context
                app()->bind('tenant.restaurant_id', fn() => $session->restaurant_id);
                app()->bind('tenant.branch_id', fn() => $session->branch_id);

                $sessionService->closeSession($session->id);
                Log::info("Auto-expired session ID: {$session->id}");
            } catch (\Exception $e) {
                Log::error("Failed to expire session ID {$session->id}: " . $e->getMessage());
            }
        }
    }
}
