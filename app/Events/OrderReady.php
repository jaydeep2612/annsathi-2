<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;

class OrderReady implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Order $order)
    {
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("restaurant.{$this->order->restaurant_id}.branch.{$this->order->branch_id}.alerts"),
        ];
    }
}
