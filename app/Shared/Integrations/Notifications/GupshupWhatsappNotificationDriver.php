<?php

declare(strict_types=1);

namespace App\Shared\Integrations\Notifications;

use App\Shared\Contracts\NotificationDriverInterface;
use Illuminate\Support\Facades\Log;

class GupshupWhatsappNotificationDriver implements NotificationDriverInterface
{
    public function send(string $to, string $title, string $body, array $data = []): void
    {
        // Mock Gupshup API call
        Log::info("WhatsApp notification sent via Gupshup to: {$to}", [
            'title' => $title,
            'body' => $body,
        ]);
    }
}
