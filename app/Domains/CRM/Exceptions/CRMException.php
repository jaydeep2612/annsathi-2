<?php

declare(strict_types=1);

namespace App\Domains\CRM\Exceptions;

use Exception;

class CRMException extends Exception
{
    public static function customerNotFound(int $id): self
    {
        return new self("Customer with ID {$id} not found.");
    }

    public static function insufficientPoints(string $customerName, int $requested, int $available): self
    {
        return new self("Customer '{$customerName}' has insufficient loyalty points. Requested: {$requested}, available: {$available}");
    }

    public static function invalidPoints(int $points): self
    {
        return new self("Points must be greater than zero. Given: {$points}");
    }
}
