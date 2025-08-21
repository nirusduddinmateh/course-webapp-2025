<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/health', function () {
    return response()->json([
        'ok' => true,
        'time' => now()->toDateTimeString(),
        'tz' => config('app.timezone'),
    ]);
});
