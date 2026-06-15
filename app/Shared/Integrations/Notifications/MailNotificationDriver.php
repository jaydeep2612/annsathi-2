<?php

declare(strict_types=1);

namespace App\Shared\Integrations\Notifications;

use App\Shared\Contracts\NotificationDriverInterface;
use Illuminate\Support\Facades\Mail;

class MailNotificationDriver implements NotificationDriverInterface
{
    public function send(string $to, string $title, string $body, array $data = []): void
    {
        Mail::raw($body, function ($message) use ($to, $title) {
            $message->to($to)
                ->subject($title);
        });
    }
}
