<?php

declare(strict_types=1);

namespace App\Shared\Contracts;

interface NotificationDriverInterface
{
    /**
     * Send a notification.
     *
     * @param string $to Recipient contact info (email, phone, etc.)
     * @param string $title Title or subject of the notification
     * @param string $body Body content of the notification
     * @param array $data Extra metadata
     * @return void
     * @throws \Exception
     */
    public function send(string $to, string $title, string $body, array $data = []): void;
}
