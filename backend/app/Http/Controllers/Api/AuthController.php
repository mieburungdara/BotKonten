<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $telegramId = $request->input('telegram_id');
        
        if ($telegramId) {
            $user = User::where('telegram_id', $telegramId)->first();
            if (!$user) {
                $user = User::create([
                    'telegram_id' => $telegramId,
                    'anonymous_id' => 'anon_' . Str::random(16),
                    'name' => $request->input('name', 'User'),
                ]);
            }
        } else {
            $user = User::create([
                'anonymous_id' => 'anon_' . Str::random(16),
                'name' => $request->input('name', 'User'),
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        Log::info('User registered', ['user_id' => $user->id, 'anonymous_id' => $user->anonymous_id]);

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $telegramId = $request->input('telegram_id');
        
        if (!$telegramId) {
            return response()->json(['error' => 'Telegram ID required'], 400);
        }

        $user = User::where('telegram_id', $telegramId)->first();
        
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        Log::info('User logged in', ['user_id' => $user->id]);

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        
        $user->load(['badges', 'conversations']);

        return response()->json([
            'user' => $user,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $user->update($request->only(['name', 'language', 'theme']));

        Log::info('Profile updated', ['user_id' => $user->id]);

        return response()->json(['user' => $user]);
    }
}