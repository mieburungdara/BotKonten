<?php

use Illuminate\Support\Facades\Route;

Route::post('/webhook/bot/{botId}', 'WebhookController@handle');

Route::get('/', function () {
    return view('webapp');
});

Route::get('/webapp', function () {
    return view('webapp');
});