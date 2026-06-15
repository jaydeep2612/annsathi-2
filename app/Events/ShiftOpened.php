<?php

namespace App\Events;

use App\Models\Shift;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShiftOpened
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Shift $shift)
    {
    }
}
