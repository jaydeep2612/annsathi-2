<?php

namespace App\Events;

use App\Models\GroceryItem;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;

class LowStockAlert implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public GroceryItem $groceryItem, public float $quantity)
    {
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("restaurant.{$this->groceryItem->restaurant_id}.branch.{$this->groceryItem->branch_id}.alerts"),
        ];
    }
}
