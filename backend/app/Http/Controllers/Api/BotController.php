<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BotController extends Controller
{
    public function index(Request $request)
    {
        $bots = Bot::where('is_active', true)->get();
        return response()->json(['bots' => $bots]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'token' => 'required|string|unique:bots,token',
            'username' => 'required|string|unique:bots,username',
            'backup_channel_id' => 'nullable|integer',
        ]);

        $bot = Bot::create([
            'name' => $validated['name'],
            'token' => $validated['token'],
            'username' => $validated['username'],
            'backup_channel_id' => $validated['backup_channel_id'] ?? null,
        ]);

        Log::info('Bot created', ['bot_id' => $bot->id, 'name' => $bot->name]);

        return response()->json(['bot' => $bot], 201);
    }

    public function show($id)
    {
        $bot = Bot::findOrFail($id);
        return response()->json(['bot' => $bot]);
    }

    public function update(Request $request, $id)
    {
        $bot = Bot::findOrFail($id);
        $bot->update($request->only(['name', 'is_active', 'backup_channel_id']));
        
        Log::info('Bot updated', ['bot_id' => $bot->id]);
        
        return response()->json(['bot' => $bot]);
    }

    public function destroy($id)
    {
        $bot = Bot::findOrFail($id);
        $bot->delete();
        
        Log::info('Bot deleted', ['bot_id' => $id]);
        
        return response()->json(['message' => 'Bot deleted']);
    }

    public function setWebhook(Request $request, $id)
    {
        $bot = Bot::findOrFail($id);
        
        $webhookUrl = $request->input('webhook_url');
        
        $bot->update(['webhook_url' => $webhookUrl]);
        
        Log::info('Webhook set', ['bot_id' => $bot->id, 'webhook_url' => $webhookUrl]);
        
        return response()->json(['bot' => $bot, 'webhook_url' => $webhookUrl]);
    }
}