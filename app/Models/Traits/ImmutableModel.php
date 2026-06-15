<?php

namespace App\Models\Traits;

use Exception;

trait ImmutableModel
{
    /**
     * Boot the trait.
     */
    public static function bootImmutableModel(): void
    {
        static::updating(function ($model) {
            // Bypass immutability ONLY for linking voiding credit note on an invoice
            if ($model instanceof \App\Models\Invoice && $model->isDirty('voided_by_credit_note_id') && count($model->getDirty()) === 1) {
                return;
            }
            throw new Exception("Model [" . get_class($model) . "] is immutable and cannot be updated.");
        });

        static::deleting(function ($model) {
            throw new Exception("Model [" . get_class($model) . "] is immutable and cannot be deleted.");
        });
    }
}
