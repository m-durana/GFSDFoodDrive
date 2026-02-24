<?php

use App\Http\Controllers\ShoppingApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('shopping/{token}')->group(function () {
    Route::get('/', [ShoppingApiController::class, 'show']);
    Route::post('/check', [ShoppingApiController::class, 'toggle']);
});
