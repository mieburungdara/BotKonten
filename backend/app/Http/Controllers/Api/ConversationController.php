<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ConversationController extends Controller
{
    public function index(Request $request)
    {
        $conversations = Conversation::with(['user', 'messages'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['conversations' => $conversations]);
    }

    public function store(Request $request)
    {
        $conversation = Conversation::create([
            'user_id' => $request->user()->id,
            'status' => 'open',
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $request->user()->id,
            'content' => $request->input('message'),
            'is_from_admin' => false,
        ]);

        Log::info('Support conversation created', ['conversation_id' => $conversation->id]);

        return response()->json(['conversation' => $conversation->load('messages')], 201);
    }

    public function show($id)
    {
        $conversation = Conversation::with(['user', 'messages.user'])
            ->where('user_id', request()->user()->id)
            ->findOrFail($id);

        return response()->json(['conversation' => $conversation]);
    }

    public function storeMessage(Request $request, $id)
    {
        $conversation = Conversation::where('user_id', $request->user()->id)->findOrFail($id);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $request->user()->id,
            'content' => $request->input('message'),
            'is_from_admin' => false,
        ]);

        Log::info('Support message sent', ['conversation_id' => $conversation->id, 'message_id' => $message->id]);

        return response()->json(['message' => $message], 201);
    }

    public function close(Request $request, $id)
    {
        $conversation = Conversation::where('user_id', $request->user()->id)->findOrFail($id);

        $conversation->update(['status' => 'closed']);

        Log::info('Support conversation closed', ['conversation_id' => $id]);

        return response()->json(['conversation' => $conversation]);
    }
}