<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\User;
use App\Models\Media;
use App\Models\Purchase;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BadgeController extends Controller
{
    public function index(Request $request)
    {
        $badges = Badge::all();

        return response()->json(['badges' => $badges]);
    }

    public function userBadges(Request $request)
    {
        $user = $request->user();

        $userBadges = $user->badges()->withPivot('earned_at')->get();

        return response()->json(['badges' => $userBadges]);
    }

    public function checkAchievements(Request $request)
    {
        $user = $request->user();

        $earnedBadges = [];

        // Check all badges and award if conditions are met
        $badges = Badge::all();

        foreach ($badges as $badge) {
            if (!$user->badges()->where('badge_id', $badge->id)->exists()) {
                if ($this->checkBadgeCondition($user, $badge)) {
                    $user->badges()->attach($badge->id, ['earned_at' => now()]);
                    $earnedBadges[] = $badge;

                    // Send notification for earned badge
                    \App\Models\Notification::create([
                        'user_id' => $user->id,
                        'type' => 'badge_earned',
                        'title' => 'Badge Baru!',
                        'message' => "Selamat! Anda telah mendapatkan badge: {$badge->name}\n\n{$badge->description}",
                        'data' => ['badge_id' => $badge->id],
                    ]);

                    Log::info('Badge earned', ['user_id' => $user->id, 'badge_id' => $badge->id]);
                }
            }
        }

        return response()->json(['earned_badges' => $earnedBadges]);
    }

    private function checkBadgeCondition(User $user, Badge $badge): bool
    {
        switch ($badge->type) {
            case 'first_sale':
                return Purchase::whereHas('media', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->exists();

            case 'top_seller':
                $totalSales = Purchase::whereHas('media', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->count();
                return $totalSales >= $badge->threshold;

            case 'high_rated':
                $avgRating = Review::whereHas('media', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->avg('rating') ?? 0;
                return $avgRating >= $badge->threshold;

            case 'content_creator':
                return Media::where('user_id', $user->id)->count() >= $badge->threshold;

            case 'reviewer':
                return Review::where('user_id', $user->id)->count() >= $badge->threshold;

            default:
                return false;
        }
    }

    public function leaderboard(Request $request)
    {
        $type = $request->get('type', 'sales'); // sales, rating, media_count

        $query = User::select('users.*')
            ->withCount(['media', 'reviews']);

        switch ($type) {
            case 'sales':
                $query->withCount(['media as sales_count' => function ($query) {
                    $query->join('purchases', 'media.id', '=', 'purchases.media_id');
                }])
                ->orderBy('sales_count', 'desc');
                break;

            case 'rating':
                $query->join('reviews', 'users.id', '=', 'reviews.user_id')
                    ->selectRaw('users.*, AVG(reviews.rating) as avg_rating')
                    ->groupBy('users.id')
                    ->orderBy('avg_rating', 'desc');
                break;

            case 'media_count':
                $query->orderBy('media_count', 'desc');
                break;

            default:
                $query->orderBy('media_count', 'desc');
        }

        $leaderboard = $query->limit(10)->get();

        return response()->json(['leaderboard' => $leaderboard, 'type' => $type]);
    }
}
