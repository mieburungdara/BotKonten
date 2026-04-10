<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $wishlists = Wishlist::with(['media', 'album'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['wishlists' => $wishlists]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'media_id' => 'nullable|exists:media,id',
            'album_id' => 'nullable|exists:albums,id',
        ]);

        if (!$request->has('media_id') && !$request->has('album_id')) {
            return response()->json(['error' => 'Media or album required'], 400);
        }

        $existing = Wishlist::where('user_id', $request->user()->id)
            ->where(function($query) use ($request) {
                if ($request->media_id) {
                    $query->orWhere('media_id', $request->media_id);
                }
                if ($request->album_id) {
                    $query->orWhere('album_id', $request->album_id);
                }
            })
            ->first();

        if ($existing) {
            return response()->json(['error' => 'Already in wishlist'], 400);
        }

        $wishlist = Wishlist::create([
            'user_id' => $request->user()->id,
            'media_id' => $request->input('media_id'),
            'album_id' => $request->input('album_id'),
        ]);

        Log::info('Added to wishlist', ['wishlist_id' => $wishlist->id]);

        return response()->json(['wishlist' => $wishlist], 201);
    }

    public function destroy(Request $request, $id)
    {
        $wishlist = Wishlist::where('user_id', $request->user()->id)->findOrFail($id);
        
        $wishlist->delete();

        Log::info('Removed from wishlist', ['wishlist_id' => $id]);

        return response()->json(['message' => 'Removed from wishlist']);
    }

    public function check(Request $request)
    {
        $mediaId = $request->input('media_id');
        $albumId = $request->input('album_id');

        $exists = Wishlist::where('user_id', $request->user()->id)
            ->where(function($query) use ($mediaId, $albumId) {
                if ($mediaId) {
                    $query->orWhere('media_id', $mediaId);
                }
                if ($albumId) {
                    $query->orWhere('album_id', $albumId);
                }
            })
            ->exists();

        return response()->json(['in_wishlist' => $exists]);
    }
}