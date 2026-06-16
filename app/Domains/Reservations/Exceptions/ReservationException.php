<?php

declare(strict_types=1);

namespace App\Domains\Reservations\Exceptions;

use Exception;

class ReservationException extends Exception
{
    public static function capacityExceeded(int $paxCount, int $capacity): self
    {
        return new self("Requested pax count ({$paxCount}) exceeds table capacity ({$capacity}).");
    }

    public static function tableOverbooked(string $tableName, string $timeStr): self
    {
        return new self("Table '{$tableName}' is already booked or occupied for the requested slot: {$timeStr}.");
    }
}
