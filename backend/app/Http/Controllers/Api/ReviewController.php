<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'media_id' => 'nullable|exists:media,id',
            'album_id' => 'nullable|exists:albums,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        if (!$request->has('media_id') && !$request->has('album_id')) {
            return response()->json(['error' => 'Media or album required'], 400);
        }

        $existingReview = Review::where('user_id', $request->user()->id)
            ->where(function($query) use ($request) {
                $query->where('media_id', $request->media_id)
                    ->orWhere('album_id', $request->album_id);
            })
            ->first();

        if ($existingReview) {
            return response()->json(['error' => 'Already reviewed'], 400);
        }

        $review = Review::create([
            'user_id' => $request->user()->id,
            'media_id' => $request->input('media_id'),
            'album_id' => $request->input('album_id'),
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        // Send notification to the content owner
        $ownerId = null;
        $itemName = '';
        if ($review->media_id) {
            $ownerId = $review->media->user_id;
            $itemName = $review->media->caption;
        } elseif ($review->album_id) {
            $ownerId = $review->album->user_id;
            $itemName = $review->album->caption;
        }

        if ($ownerId && $ownerId !== $request->user()->id) {
            \App\Models\Notification::create([
                'user_id' => $ownerId,
                'type' => 'new_review',
                'title' => 'Review Baru!',
                'message' => "Anda mendapat review {$review->rating}⭐ untuk '{$itemName}' dari {$request->user()->name}",
                'data' => [
                    'review_id' => $review->id,
                    'media_id' => $review->media_id,
                    'album_id' => $review->album_id,
                    'rating' => $review->rating,
                    'reviewer_id' => $request->user()->id
                ],
            ]);
        }

        Log::info('Review created', ['review_id' => $review->id]);

        return response()->json(['review' => $review], 201);
    }

    public function update(Request $request, $id)
    {
        $review = Review::where('user_id', $request->user()->id)->findOrFail($id);

        $review->update($request->only(['rating', 'comment']));

        Log::info('Review updated', ['review_id' => $review->id]);

        return response()->json(['review' => $review]);
    }

    public function destroy(Request $request, $id)
    {
        $review = Review::where('user_id', $request->user()->id)->findOrFail($id);
        
        $review->delete();

        Log::info('Review deleted', ['review_id' => $id]);

        return response()->json(['message' => 'Review deleted']);
    }

    public function mediaReviews($mediaId)
    {
        $reviews = Review::with(['user'])
            ->where('media_id', $mediaId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($reviews);
    }

    public function albumReviews($albumId)
    {
        $reviews = Review::with(['user'])
            ->where('album_id', $albumId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($reviews);
    }
}