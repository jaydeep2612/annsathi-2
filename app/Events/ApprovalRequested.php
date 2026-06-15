<?php

namespace App\Events;

use App\Models\ApprovalRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApprovalRequested
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public ApprovalRequest $approvalRequest)
    {
    }
}
