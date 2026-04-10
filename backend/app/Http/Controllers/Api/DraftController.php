<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Draft;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class DraftController extends Controller
{
    public function index(Request $request)
    {
        $drafts = Draft::with(['bot'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['drafts' => $drafts]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bot_id' => 'required|exists:bots,id',
            'type' => 'required|string',
            'file_id' => 'nullable|string',
            'file_path' => 'nullable|string',
            'caption' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'category' => 'nullable|string',
            'telegram_message_id' => 'nullable|integer',
        ]);

        $draft = Draft::create([
            'bot_id' => $validated['bot_id'],
            'user_id' => $request->user()->id,
            'type' => $validated['type'],
            'file_id' => $validated['file_id'] ?? null,
            'file_path' => $validated['file_path'] ?? null,
            'caption' => $validated['caption'] ?? null,
            'price' => $validated['price'] ?? 0,
            'category' => $validated['category'] ?? null,
            'telegram_message_id' => $validated['telegram_message_id'] ?? null,
        ]);

        Log::info('Draft created', ['draft_id' => $draft->id]);

        return response()->json(['draft' => $draft], 201);
    }

    public function show($id)
    {
        $draft = Draft::with(['bot'])
            ->where('user_id', request()->user()->id)
            ->findOrFail($id);

        return response()->json(['draft' => $draft]);
    }

    public function update(Request $request, $id)
    {
        $draft = Draft::where('user_id', $request->user()->id)->findOrFail($id);

        $draft->update($request->only(['caption', 'price', 'category']));

        Log::info('Draft updated', ['draft_id' => $draft->id]);

        return response()->json(['draft' => $draft]);
    }

    public function destroy(Request $request, $id)
    {
        $draft = Draft::where('user_id', $request->user()->id)->findOrFail($id);
        
        $draft->delete();

        Log::info('Draft deleted', ['draft_id' => $id]);

        return response()->json(['message' => 'Draft deleted']);
    }

    public function publish(Request $request, $id)
    {
        $draft = Draft::where('user_id', $request->user()->id)->findOrFail($id);

        $media = Media::create([
            'unique_id' => 'media_' . Str::random(12),
            'bot_id' => $draft->bot_id,
            'user_id' => $draft->user_id,
            'type' => $draft->type,
            'file_id' => $draft->file_id,
            'file_path' => $draft->file_path,
            'caption' => $draft->caption,
            'price' => $draft->price,
            'category' => $draft->category,
            'is_published' => true,
        ]);

        $draft->delete();

        Log::info('Draft published to media', ['draft_id' => $id, 'media_id' => $media->id]);

        return response()->json(['media' => $media], 201);
    }

    public function publishMultiple(Request $request)
    {
        $draftIds = $request->input('draft_ids', []);

        if (empty($draftIds)) {
            return response()->json(['error' => 'No drafts selected'], 400);
        }

        $drafts = Draft::where('user_id', $request->user()->id)
            ->whereIn('id', $draftIds)
            ->get();

        if ($drafts->count() > 10) {
            return response()->json(['error' => 'Maximum 10 items per album'], 400);
        }

        $botId = $drafts->first()->bot_id;
        
        $album = \App\Models\Album::create([
            'unique_id' => 'album_' . Str::random(12),
            'bot_id' => $botId,
            'user_id' => $request->user()->id,
            'title' => $request->input('title', 'Album'),
            'description' => $request->input('description'),
            'price' => $request->input('price', 0),
            'category' => $request->input('category'),
        ]);

        $position = 0;
        $createdMedia = [];

        foreach ($drafts as $draft) {
            $media = Media::create([
                'unique_id' => 'media_' . Str::random(12),
                'bot_id' => $draft->bot_id,
                'user_id' => $draft->user_id,
                'type' => $draft->type,
                'file_id' => $draft->file_id,
                'file_path' => $draft->file_path,
                'caption' => $draft->caption,
                'price' => 0,
                'category' => $draft->category,
                'is_published' => true,
            ]);

            $album->media()->attach($media->id, ['position' => $position++]);
            $createdMedia[] = $media;
        }

        Draft::whereIn('id', $draftIds)->delete();

        Log::info('Drafts published as album', ['album_id' => $album->id, 'media_count' => count($createdMedia)]);

        return response()->json(['album' => $album->load('media')], 201);
    }
}