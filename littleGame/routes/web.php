<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'app' => 'laravel-api-offers',
        'status' => 'ok',
    ]);
});
