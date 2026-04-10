<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $query = Media::with(['bot', 'user', 'reviews'])
            ->where('is_published', true);

        if ($request->has('bot_id')) {
            $query->where('bot_id', $request->bot_id);
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('search')) {
            $query->where('caption', 'like', '%' . $request->search . '%');
        }

        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        }

        $media = $query->paginate(20);

        return response()->json($media);
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
        ]);

        $media = Media::create([
            'unique_id' => 'media_' . uniqid(),
            'bot_id' => $validated['bot_id'],
            'user_id' => $request->user()->id,
            'type' => $validated['type'],
            'file_id' => $validated['file_id'] ?? null,
            'file_path' => $validated['file_path'] ?? null,
            'caption' => $validated['caption'] ?? null,
            'price' => $validated['price'] ?? 0,
            'category' => $validated['category'] ?? null,
        ]);

        Log::info('Media created', ['media_id' => $media->id]);

        return response()->json(['media' => $media], 201);
    }

    public function show($id)
    {
        $media = Media::with(['bot', 'user', 'reviews.user'])
            ->where('id', $id)
            ->orWhere('unique_id', $id)
            ->firstOrFail();

        $avgRating = $media->reviews()->avg('rating') ?? 0;

        return response()->json([
            'media' => $media,
            'average_rating' => round($avgRating, 1),
            'review_count' => $media->reviews()->count(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $media = Media::where('user_id', $request->user()->id)->findOrFail($id);

        $media->update($request->only(['caption', 'price', 'category', 'is_published']));

        Log::info('Media updated', ['media_id' => $media->id]);

        return response()->json(['media' => $media]);
    }

    public function destroy(Request $request, $id)
    {
        $media = Media::where('user_id', $request->user()->id)->findOrFail($id);
        
        $media->delete();

        Log::info('Media deleted', ['media_id' => $id]);

        return response()->json(['message' => 'Media deleted']);
    }

    public function publish(Request $request, $id)
    {
        $media = Media::where('user_id', $request->user()->id)->findOrFail($id);

        $media->update(['is_published' => true]);

        // Send notification to seller about successful publication
        \App\Models\Notification::create([
            'user_id' => $media->user_id,
            'type' => 'draft_published',
            'title' => 'Draft Berhasil Dipublish!',
            'message' => "Media '{$media->caption}' telah berhasil dipublish dan siap dijual.",
            'data' => ['media_id' => $media->id],
        ]);

        Log::info('Media published', ['media_id' => $media->id]);

        return response()->json(['media' => $media]);
    }

    public function myMedia(Request $request)
    {
        $media = Media::with(['bot'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($media);
    }
}