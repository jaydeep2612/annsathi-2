<?php

declare(strict_types=1);

namespace App\Shared\Integrations\Notifications;

use App\Shared\Contracts\NotificationDriverInterface;
use Illuminate\Support\Facades\Log;

class LogNotificationDriver implements NotificationDriverInterface
{
    public function send(string $to, string $title, string $body, array $data = []): void
    {
        Log::info("Notification Sent via Log Driver", [
            'to' => $to,
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ]);
    }
}
