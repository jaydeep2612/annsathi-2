<?php

namespace App\Events;

use App\Models\ApprovalRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApprovalApproved
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public ApprovalRequest $approvalRequest)
    {
    }
}
