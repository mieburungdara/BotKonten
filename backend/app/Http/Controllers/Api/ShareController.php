<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ShareController extends Controller
{
    public function generateShareLink(Request $request, $type, $id)
    {
        $validTypes = ['media', 'album'];

        if (!in_array($type, $validTypes)) {
            return response()->json(['error' => 'Invalid share type'], 400);
        }

        $model = $type === 'media' ? Media::class : Album::class;
        $item = $model::findOrFail($id);

        $shareCode = Str::random(12);

        // Store share record
        DB::table('shares')->insert([
            'shareable_type' => $type,
            'shareable_id' => $id,
            'user_id' => $request->user()?->id,
            'code' => $shareCode,
            'click_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $shareUrl = url("/share/{$shareCode}");

        return response()->json([
            'code' => $shareCode,
            'url' => $shareUrl,
            'social_links' => [
                'telegram' => "https://t.me/share/url?url=" . urlencode($shareUrl) . "&text=" . urlencode($item->caption ?? 'Check this out!'),
                'whatsapp' => "https://wa.me/?text=" . urlencode($item->caption . ' ' . $shareUrl),
                'twitter' => "https://twitter.com/intent/tweet?url=" . urlencode($shareUrl) . "&text=" . urlencode($item->caption ?? ''),
                'facebook' => "https://www.facebook.com/sharer/sharer.php?u=" . urlencode($shareUrl),
            ]
        ]);
    }

    public function trackShare($code)
    {
        $share = DB::table('shares')->where('code', $code)->first();

        if (!$share) {
            return response()->json(['error' => 'Share not found'], 404);
        }

        DB::table('shares')
            ->where('code', $code)
            ->increment('click_count');

        return response()->json([
            'shareable_type' => $share->shareable_type,
            'shareable_id' => $share->shareable_id,
            'click_count' => $share->click_count + 1,
        ]);
    }
}
