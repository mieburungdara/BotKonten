<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', 'Api\AuthController@register');
    Route::post('/login', 'Api\AuthController@login');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/profile', 'Api\AuthController@profile');
    Route::put('/user/profile', 'Api\AuthController@updateProfile');

    Route::prefix('media')->group(function () {
        Route::get('/', 'Api\MediaController@index');
        Route::post('/', 'Api\MediaController@store');
        Route::get('/my', 'Api\MediaController@myMedia');
        Route::get('/{id}', 'Api\MediaController@show');
        Route::put('/{id}', 'Api\MediaController@update');
        Route::delete('/{id}', 'Api\MediaController@destroy');
        Route::post('/{id}/publish', 'Api\MediaController@publish');
    });

    Route::prefix('drafts')->group(function () {
        Route::get('/', 'Api\DraftController@index');
        Route::post('/', 'Api\DraftController@store');
        Route::get('/{id}', 'Api\DraftController@show');
        Route::put('/{id}', 'Api\DraftController@update');
        Route::delete('/{id}', 'Api\DraftController@destroy');
        Route::post('/{id}/publish', 'Api\DraftController@publish');
        Route::post('/publish-multiple', 'Api\DraftController@publishMultiple');
    });

    Route::prefix('albums')->group(function () {
        Route::get('/', 'Api\AlbumController@index');
        Route::post('/', 'Api\AlbumController@store');
        Route::get('/my', 'Api\AlbumController@myAlbums');
        Route::get('/{id}', 'Api\AlbumController@show');
        Route::put('/{id}', 'Api\AlbumController@update');
        Route::delete('/{id}', 'Api\AlbumController@destroy');
    });

    Route::prefix('bots')->group(function () {
        Route::get('/', 'Api\BotController@index');
        Route::post('/', 'Api\BotController@store');
        Route::get('/{id}', 'Api\BotController@show');
        Route::put('/{id}', 'Api\BotController@update');
        Route::delete('/{id}', 'Api\BotController@destroy');
        Route::post('/{id}/webhook', 'Api\BotController@setWebhook');
    });

    Route::prefix('payment')->group(function () {
        Route::post('/checkout', 'Api\PaymentController@checkout');
        Route::post('/{id}/simulate', 'Api\PaymentController@simulatePayment');
        Route::get('/purchases', 'Api\PaymentController@myPurchases');
        Route::get('/history', 'Api\PaymentController@purchaseHistory');
    });

    Route::prefix('reviews')->group(function () {
        Route::post('/', 'Api\ReviewController@store');
        Route::put('/{id}', 'Api\ReviewController@update');
        Route::delete('/{id}', 'Api\ReviewController@destroy');
        Route::get('/media/{mediaId}', 'Api\ReviewController@mediaReviews');
        Route::get('/album/{albumId}', 'Api\ReviewController@albumReviews');
    });

    Route::prefix('wishlist')->group(function () {
        Route::get('/', 'Api\WishlistController@index');
        Route::post('/', 'Api\WishlistController@store');
        Route::delete('/{id}', 'Api\WishlistController@destroy');
        Route::get('/check', 'Api\WishlistController@check');
    });

    Route::prefix('support')->group(function () {
        Route::get('/', 'Api\ConversationController@index');
        Route::post('/', 'Api\ConversationController@store');
        Route::get('/{id}', 'Api\ConversationController@show');
        Route::post('/{id}/message', 'Api\ConversationController@storeMessage');
        Route::post('/{id}/close', 'Api\ConversationController@close');
    });

    Route::prefix('analytics')->group(function () {
        Route::get('/dashboard', 'Api\AnalyticsController@dashboard');
        Route::get('/seller', 'Api\AnalyticsController@sellerStats');
    });

    Route::prefix('notifications')->group(function () {
        Route::get('/', 'Api\NotificationController@index');
        Route::get('/unread', 'Api\NotificationController@unread');
        Route::post('/', 'Api\NotificationController@store');
        Route::post('/{id}/read', 'Api\NotificationController@markAsRead');
        Route::post('/read-all', 'Api\NotificationController@markAllAsRead');
        Route::delete('/{id}', 'Api\NotificationController@destroy');
        Route::post('/clear', 'Api\NotificationController@clearAll');
    });

    Route::prefix('share')->group(function () {
        Route::get('/{type}/{id}', 'Api\ShareController@generateShareLink');
        Route::get('/track/{code}', 'Api\ShareController@trackShare');
    });

    Route::prefix('badges')->group(function () {
        Route::get('/', 'Api\BadgeController@index');
        Route::get('/user', 'Api\BadgeController@userBadges');
        Route::post('/check', 'Api\BadgeController@checkAchievements');
        Route::get('/leaderboard', 'Api\BadgeController@leaderboard');
    });
});

Route::get('/bots', 'Api\BotController@index');
Route::get('/media', 'Api\MediaController@index');
Route::get('/media/{id}', 'Api\MediaController@show');
Route::get('/albums', 'Api\AlbumController@index');
Route::get('/albums/{id}', 'Api\AlbumController@show');