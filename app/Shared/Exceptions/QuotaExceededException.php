<?php

declare(strict_types=1);

namespace App\Shared\Exceptions;

use Exception;

class QuotaExceededException extends Exception
{
    public function __construct(string $metricKey, int $limit, string $message = "")
    {
        $message = $message ?: "Subscription plan limit reached for '{$metricKey}'. Allowed quota limit is {$limit}.";
        parent::__construct($message, 403);
    }
}
