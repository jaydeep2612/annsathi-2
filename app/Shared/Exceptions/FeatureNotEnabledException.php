<?php

declare(strict_types=1);

namespace App\Shared\Exceptions;

use Exception;

class FeatureNotEnabledException extends Exception
{
    public function __construct(string $featureKey, string $message = "")
    {
        $message = $message ?: "The feature '{$featureKey}' is not enabled for your restaurant's subscription plan.";
        parent::__construct($message, 403);
    }
}
