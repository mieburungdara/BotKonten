<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Album;
use App\Models\Purchase;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();

        $totalMedia = Media::where('user_id', $user->id)->count();
        $totalAlbums = Album::where('user_id', $user->id)->count();
        $publishedMedia = Media::where('user_id', $user->id)->where('is_published', true)->count();

        $revenue = Purchase::whereHas('media', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orWhereHas('album', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('payment_status', 'completed')
            ->sum('amount');

        $recentSales = Purchase::whereHas('media', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orWhereHas('album', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['media', 'album', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $topMedia = Media::where('user_id', $user->id)
            ->withCount('purchases')
            ->orderBy('purchases_count', 'desc')
            ->limit(5)
            ->get();

        $mediaByType = Media::where('user_id', $user->id)
            ->select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get();

        $dailyRevenue = Purchase::whereHas('media', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orWhereHas('album', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('payment_status', 'completed')
            ->where('created_at', '>=', now()->subDays(30))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'stats' => [
                'total_media' => $totalMedia,
                'total_albums' => $totalAlbums,
                'published_media' => $publishedMedia,
                'total_revenue' => $revenue,
            ],
            'recent_sales' => $recentSales,
            'top_media' => $topMedia,
            'media_by_type' => $mediaByType,
            'daily_revenue' => $dailyRevenue,
        ]);
    }

    public function sellerStats(Request $request)
    {
        $user = $request->user();

        $totalPurchases = Purchase::whereHas('media', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orWhereHas('album', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->count();

        $completedPurchases = Purchase::whereHas('media', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orWhereHas('album', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('payment_status', 'completed')
            ->count();

        $avgRating = Review::whereHas('media', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orWhereHas('album', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->avg('rating') ?? 0;

        return response()->json([
            'total_purchases' => $totalPurchases,
            'completed_purchases' => $completedPurchases,
            'average_rating' => round($avgRating, 1),
        ]);
    }
}