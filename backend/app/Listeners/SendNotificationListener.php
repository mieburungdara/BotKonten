<?php

namespace App\Listeners;

use App\Events\NotificationSent;
use App\Models\Bot;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendNotificationListener implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(NotificationSent $event)
    {
        $notification = $event->notification;
        $user = $notification->user;

        // Skip if user doesn't have telegram_id
        if (!$user->telegram_id) {
            Log::info('Skipping notification - no telegram_id', ['user_id' => $user->id]);
            return;
        }

        // Get a bot token (use the first available bot for notifications)
        $bot = Bot::first();
        if (!$bot) {
            Log::error('No bot available for notifications');
            return;
        }

        $this->sendTelegramNotification($bot->token, $user->telegram_id, $notification);
    }

    private function sendTelegramNotification($botToken, $chatId, $notification)
    {
        try {
            $message = "🔔 *{$notification->title}*\n\n{$notification->message}";

            // Add contextual buttons based on notification type
            $inlineKeyboard = $this->getInlineKeyboard($notification);

            $payload = [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ];

            if ($inlineKeyboard) {
                $payload['reply_markup'] = json_encode([
                    'inline_keyboard' => $inlineKeyboard
                ]);
            }

            $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", $payload);

            if ($response->successful()) {
                Log::info('Telegram notification sent', [
                    'notification_id' => $notification->id,
                    'chat_id' => $chatId
                ]);
            } else {
                Log::error('Failed to send Telegram notification', [
                    'notification_id' => $notification->id,
                    'response' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Exception sending Telegram notification', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function getInlineKeyboard($notification)
    {
        switch ($notification->type) {
            case 'draft_published':
                if (isset($notification->data['media_id'])) {
                    return [[
                        [
                            'text' => '👀 Lihat Media',
                            'url' => url("/media/{$notification->data['media_id']}")
                        ]
                    ]];
                }
                break;

            case 'payment_completed':
                return [[
                    [
                        'text' => '📦 Lihat Pembelian',
                        'callback_data' => 'view_purchases'
                    ]
                ]];
                break;

            case 'new_review':
                if (isset($notification->data['media_id'])) {
                    return [[
                        [
                            'text' => '⭐ Lihat Review',
                            'url' => url("/media/{$notification->data['media_id']}")
                        ]
                    ]];
                }
                break;

            case 'badge_earned':
                return [[
                    [
                        'text' => '🏆 Lihat Badges',
                        'callback_data' => 'view_badges'
                    ]
                ]];
                break;
        }

        return null;
    }
}
