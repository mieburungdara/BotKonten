<?php

namespace App\Http\Controllers;

use App\Models\Bot;
use App\Models\Draft;
use App\Models\User;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected $telegramApi;

    public function __construct()
    {
        $this->telegramApi = 'https://api.telegram.org';
    }

    public function handle(Request $request, $botId)
    {
        $bot = Bot::findOrFail($botId);

        $update = $request->all();

        Log::info('Webhook received', ['bot_id' => $botId, 'update_type' => isset($update['message']) ? 'message' : (isset($update['callback_query']) ? 'callback' : 'unknown')]);

        if (isset($update['message'])) {
            return $this->handleMessage($bot, $update['message']);
        }

        if (isset($update['callback_query'])) {
            return $this->handleCallback($bot, $update['callback_query']);
        }

        if (isset($update['my_chat_member'])) {
            return $this->handleMyChatMember($bot, $update['my_chat_member']);
        }

        return response()->json(['ok' => true]);
    }

    protected function handleMessage($bot, $message)
    {
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';
        $messageId = $message['message_id'];

        if (strpos($text, '/start') === 0) {
            return $this->handleStartCommand($bot, $chatId, $message);
        }

        if (isset($message['photo'])) {
            return $this->handlePhoto($bot, $message, $chatId);
        }

        if (isset($message['video'])) {
            return $this->handleVideo($bot, $message, $chatId);
        }

        if (isset($message['audio'])) {
            return $this->handleAudio($bot, $message, $chatId);
        }

        if (isset($message['document'])) {
            return $this->handleDocument($bot, $message, $chatId);
        }

        return $this->sendMessage($bot, $chatId, "Kirim foto, video, audio, atau dokumen untuk diupload.");
    }

    protected function handleStartCommand($bot, $chatId, $message)
    {
        $user = $this->getOrCreateUser($message['from']);

        $text = "Selamat datang di {$bot->name}!\n\n";
        $text .= "Kirim media (foto, video, audio, dokumen) untuk membuat draft.\n";
        $text .= "Draft akan muncul di webapp untuk di-publish atau dihapus.";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => 'Buka Webapp', 'web_app' => ['url' => config('app.webapp_url') . '?bot_id=' . $bot->id]]
                ]
            ]
        ];

        return $this->sendMessage($bot, $chatId, $text, $keyboard);
    }

    protected function handlePhoto($bot, $message, $chatId)
    {
        $fileId = end($message['photo'])['file_id'];
        $caption = $message['caption'] ?? null;

        return $this->createDraft($bot, $message['from'], 'photo', $fileId, $caption, $chatId, $message['message_id']);
    }

    protected function handleVideo($bot, $message, $chatId)
    {
        $fileId = $message['video']['file_id'];
        $caption = $message['caption'] ?? null;

        return $this->createDraft($bot, $message['from'], 'video', $fileId, $caption, $chatId, $message['message_id']);
    }

    protected function handleAudio($bot, $message, $chatId)
    {
        $fileId = $message['audio']['file_id'];
        $caption = $message['caption'] ?? null;

        return $this->createDraft($bot, $message['from'], 'audio', $fileId, $caption, $chatId, $message['message_id']);
    }

    protected function handleDocument($bot, $message, $chatId)
    {
        $fileId = $message['document']['file_id'];
        $caption = $message['caption'] ?? null;

        return $this->createDraft($bot, $message['from'], 'document', $fileId, $caption, $chatId, $message['message_id']);
    }

    protected function createDraft($bot, $telegramUser, $type, $fileId, $caption, $chatId, $messageId)
    {
        $user = $this->getOrCreateUser($telegramUser);

        $draft = Draft::create([
            'bot_id' => $bot->id,
            'user_id' => $user->id,
            'type' => $type,
            'file_id' => $fileId,
            'caption' => $caption,
            'price' => 0,
            'telegram_message_id' => $messageId,
        ]);

        $text = "✅ Draft received!\n";
        $text .= "Type: {$type}\n";
        if ($caption) {
            $text .= "Caption: {$caption}\n";
        }
        $text .= "\nBuka webapp untuk edit, set harga, atau publish.";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => 'Buka Webapp', 'web_app' => ['url' => config('app.webapp_url') . '?bot_id=' . $bot->id . '&draft_id=' . $draft->id]]
                ]
            ]
        ];

        $this->sendMessage($bot, $chatId, $text, $keyboard);

        Log::info('Draft created from webhook', ['draft_id' => $draft->id, 'type' => $type]);

        return response()->json(['ok' => true, 'draft_id' => $draft->id]);
    }

    protected function handleCallback($bot, $callback)
    {
        $data = $callback['data'];
        $chatId = $callback['message']['chat']['id'];

        $this->answerCallbackQuery($bot, $callback['id'], "Action processed");

        return response()->json(['ok' => true]);
    }

    protected function handleMyChatMember($bot, $chatMember)
    {
        if ($chatMember['new_chat_member']['status'] === 'bot') {
            Log::info('Bot added to channel/group', ['bot_id' => $bot->id]);
        }
        return response()->json(['ok' => true]);
    }

    protected function getOrCreateUser($telegramUser)
    {
        $telegramId = $telegramUser['id'];

        $user = User::where('telegram_id', $telegramId)->first();

        if (!$user) {
            $user = User::create([
                'telegram_id' => $telegramId,
                'anonymous_id' => 'anon_' . Str::random(16),
                'name' => $telegramUser['first_name'] . ($telegramUser['last_name'] ?? ''),
            ]);

            Log::info('User created from webhook', ['user_id' => $user->id]);
        }

        return $user;
    }

    protected function sendMessage($bot, $chatId, $text, $keyboard = null)
    {
        $params = [
            'chat_id' => $chatId,
            'text' => $text,
        ];

        if ($keyboard) {
            $params['reply_markup'] = json_encode($keyboard);
        }

        $url = "{$this->telegramApi}/bot{$bot->token}/sendMessage";

        $client = new \GuzzleHttp\Client();
        try {
            $client->post($url, ['json' => $params]);
        } catch (\Exception $e) {
            Log::error('Telegram API error', ['error' => $e->getMessage()]);
        }

        return response()->json(['ok' => true]);
    }

    protected function answerCallbackQuery($bot, $callbackId, $text)
    {
        $url = "{$this->telegramApi}/bot{$bot->token}/answerCallbackQuery";
        
        $client = new \GuzzleHttp\Client();
        try {
            $client->post($url, ['json' => [
                'callback_query_id' => $callbackId,
                'text' => $text,
            ]]);
        } catch (\Exception $e) {
            Log::error('Telegram API error', ['error' => $e->getMessage()]);
        }
    }
}