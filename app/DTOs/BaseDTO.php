<?php

namespace App\DTOs;

abstract class BaseDTO
{
    /**
     * Convert the DTO properties to an array.
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
