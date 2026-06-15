<?php

namespace App\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;

class WaiterCalled implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public string $sessionToken, public string $locationName)
    {
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $restaurantId = app('tenant.restaurant_id') ?: 1;
        $branchId = app('tenant.branch_id') ?: 1;

        return [
            new PrivateChannel("restaurant.{$restaurantId}.branch.{$branchId}.alerts"),
        ];
    }
}
