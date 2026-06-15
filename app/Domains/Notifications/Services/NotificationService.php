<?php

declare(strict_types=1);

namespace App\Domains\Notifications\Services;

use App\Domains\Notifications\Models\NotificationChannel;
use App\Domains\Notifications\Models\NotificationPreference;
use App\Domains\Notifications\Models\NotificationTemplate;
use App\Domains\Notifications\Models\NotificationsLog;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Dispatch notification for an event.
     */
    public function dispatch(string $eventName, array $data, User|array $recipient): void
    {
        // Resolve tenant context
        $restaurantId = app()->bound('tenant.restaurant_id') ? app('tenant.restaurant_id') : null;
        $branchId = app()->bound('tenant.branch_id') ? app('tenant.branch_id') : null;

        // 1. Find the template (customized for tenant, or global platform fallback)
        $template = NotificationTemplate::where('is_active', true)
            ->where(function ($query) use ($restaurantId) {
                if ($restaurantId) {
                    $query->where('restaurant_id', $restaurantId)
                        ->orWhereNull('restaurant_id');
                } else {
                    $query->whereNull('restaurant_id');
                }
            })
            ->where('event_name', $eventName)
            ->orderBy('restaurant_id', 'desc') // customized first
            ->first();

        if (! $template) {
            Log::warning("No active notification template found for event: {$eventName}");
            return;
        }

        // Resolve user ID and details
        $userId = null;
        $recipientEmail = null;
        $recipientPhone = null;

        if ($recipient instanceof User) {
            $userId = $recipient->id;
            $recipientEmail = $recipient->email;
            $recipientPhone = $recipient->phone ?? $recipient->email;
            $restaurantId = $restaurantId ?? $recipient->restaurant_id;
            $branchId = $branchId ?? $recipient->branch_id;
        } else {
            $userId = $recipient['user_id'] ?? null;
            $recipientEmail = $recipient['email'] ?? null;
            $recipientPhone = $recipient['phone'] ?? $recipientEmail;
        }

        // 2. Resolve channels (Check user preferences first, fallback to template default channels)
        $channels = null;
        if ($userId) {
            $preference = NotificationPreference::where('user_id', $userId)
                ->where('event_name', $eventName)
                ->first();
            if ($preference) {
                $channels = $preference->channels;
            }
        }

        if (empty($channels)) {
            $channels = $template->channels ?? [];
        }

        if (is_string($channels)) {
            $channels = json_decode($channels, true) ?? [];
        }

        // 3. Process each channel
        foreach ($channels as $channelName) {
            // Check if this channel is active in configuration
            $channelConfig = NotificationChannel::where('name', $channelName)
                ->where('is_active', true)
                ->first();

            if (! $channelConfig) {
                Log::warning("Notification channel '{$channelName}' is not configured or disabled.");
                continue;
            }

            try {
                // Parse template content
                $parsedTitle = $this->parseContent($template->title, $data);
                $parsedBody = $this->parseContent($template->body, $data);

                // Resolve contact address depending on channel
                $contactAddress = match ($channelName) {
                    'email' => $recipientEmail,
                    'sms', 'whatsapp' => $recipientPhone,
                    default => $recipientEmail ?? $recipientPhone ?? 'unknown',
                };

                if (! $contactAddress) {
                    throw new Exception("No contact address found for channel '{$channelName}'");
                }

                // Resolve driver class
                $driverClass = $this->resolveDriverClass($channelConfig->driver);
                $driver = app($driverClass);

                // Send notification
                $driver->send($contactAddress, $parsedTitle, $parsedBody, $data);

                // Log success
                NotificationsLog::create([
                    'restaurant_id' => $restaurantId ?? 0,
                    'branch_id' => $branchId,
                    'user_id' => $userId,
                    'type' => $channelName,
                    'title' => $parsedTitle,
                    'body' => $parsedBody,
                    'data' => [
                        'channel' => $channelName,
                        'recipient_contact' => $contactAddress,
                        'status' => 'sent',
                    ],
                ]);
            } catch (Exception $e) {
                Log::error("Failed to send notification via channel '{$channelName}': " . $e->getMessage());

                // Log failure
                NotificationsLog::create([
                    'restaurant_id' => $restaurantId ?? 0,
                    'branch_id' => $branchId,
                    'user_id' => $userId,
                    'type' => $channelName,
                    'title' => $parsedTitle ?? $template->title,
                    'body' => $parsedBody ?? $template->body,
                    'data' => [
                        'channel' => $channelName,
                        'recipient_contact' => $contactAddress ?? 'unknown',
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                    ],
                ]);
            }
        }
    }

    /**
     * Parse variables in template content.
     */
    protected function parseContent(string $content, array $data): string
    {
        foreach ($data as $key => $val) {
            $content = str_replace("{{{$key}}}", (string) $val, $content);
        }
        return $content;
    }

    /**
     * Map driver string config to driver class.
     */
    protected function resolveDriverClass(string $driver): string
    {
        return match ($driver) {
            'log' => \App\Shared\Integrations\Notifications\LogNotificationDriver::class,
            'mail' => \App\Shared\Integrations\Notifications\MailNotificationDriver::class,
            'twilio' => \App\Shared\Integrations\Notifications\TwilioSmsNotificationDriver::class,
            'gupshup' => \App\Shared\Integrations\Notifications\GupshupWhatsappNotificationDriver::class,
            default => \App\Shared\Integrations\Notifications\LogNotificationDriver::class,
        };
    }
}
