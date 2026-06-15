<?php

namespace App\Events;

use App\Models\CreditNote;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreditNoteIssued
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public CreditNote $creditNote)
    {
    }
}
