<?php

declare(strict_types=1);

namespace App\Shared\Integrations\Notifications;

use App\Shared\Contracts\NotificationDriverInterface;
use Illuminate\Support\Facades\Log;

class TwilioSmsNotificationDriver implements NotificationDriverInterface
{
    public function send(string $to, string $title, string $body, array $data = []): void
    {
        // Mock Twilio API call
        Log::info("SMS notification sent via Twilio to: {$to}", [
            'title' => $title,
            'body' => $body,
        ]);
    }
}
