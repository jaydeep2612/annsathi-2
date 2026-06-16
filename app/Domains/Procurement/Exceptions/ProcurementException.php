<?php

declare(strict_types=1);

namespace App\Domains\Procurement\Exceptions;

use Exception;

class ProcurementException extends Exception
{
    public static function supplierNotFound(int $id): self
    {
        return new self("Supplier with ID {$id} not found.");
    }

    public static function invalidAmount(float $amount): self
    {
        return new self("Amount must be greater than zero. Given: {$amount}");
    }

    public static function insufficientBalance(string $supplierName, float $requested, float $available): self
    {
        return new self("Insufficient balance for supplier '{$supplierName}'. Requested debit: {$requested}, available: {$available}");
    }
    
    public static function poNotOpen(string $poNumber, string $status): self
    {
        return new self("Purchase Order {$poNumber} is not in a status that can receive goods. Current status: {$status}");
    }
}
