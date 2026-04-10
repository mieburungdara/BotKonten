<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AlbumController extends Controller
{
    public function index(Request $request)
    {
        $query = Album::with(['bot', 'user', 'media']);

        if ($request->has('bot_id')) {
            $query->where('bot_id', $request->bot_id);
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $albums = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($albums);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bot_id' => 'required|exists:bots,id',
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'category' => 'nullable|string',
            'media_ids' => 'nullable|array',
            'media_ids.*' => 'exists:media,id',
        ]);

        $album = Album::create([
            'unique_id' => 'album_' . uniqid(),
            'bot_id' => $validated['bot_id'],
            'user_id' => $request->user()->id,
            'title' => $validated['title'] ?? 'Album',
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'] ?? 0,
            'category' => $validated['category'] ?? null,
        ]);

        if (!empty($validated['media_ids'])) {
            $position = 0;
            foreach ($validated['media_ids'] as $mediaId) {
                $album->media()->attach($mediaId, ['position' => $position++]);
            }
        }

        Log::info('Album created', ['album_id' => $album->id]);

        return response()->json(['album' => $album->load('media')], 201);
    }

    public function show($id)
    {
        $album = Album::with(['bot', 'user', 'media', 'reviews.user'])
            ->where('id', $id)
            ->orWhere('unique_id', $id)
            ->firstOrFail();

        $avgRating = $album->reviews()->avg('rating') ?? 0;

        return response()->json([
            'album' => $album,
            'average_rating' => round($avgRating, 1),
            'review_count' => $album->reviews()->count(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $album = Album::where('user_id', $request->user()->id)->findOrFail($id);

        $album->update($request->only(['title', 'description', 'price', 'category']));

        if ($request->has('media_ids')) {
            $album->media()->sync([]);
            $position = 0;
            foreach ($request->media_ids as $mediaId) {
                $album->media()->attach($mediaId, ['position' => $position++]);
            }
        }

        Log::info('Album updated', ['album_id' => $album->id]);

        return response()->json(['album' => $album->load('media')]);
    }

    public function destroy(Request $request, $id)
    {
        $album = Album::where('user_id', $request->user()->id)->findOrFail($id);
        
        $album->delete();

        Log::info('Album deleted', ['album_id' => $id]);

        return response()->json(['message' => 'Album deleted']);
    }

    public function myAlbums(Request $request)
    {
        $albums = Album::with(['bot', 'media'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($albums);
    }
}