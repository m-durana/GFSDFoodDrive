<?php

use App\Http\Controllers\PackingApiController;
use App\Http\Controllers\ShoppingApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('shopping/{token}')->group(function () {
    Route::get('/', [ShoppingApiController::class, 'show']);
    Route::post('/check', [ShoppingApiController::class, 'toggle']);
});

// Packing API — QR token provides access (no auth required for most endpoints)
Route::prefix('packing')->group(function () {
    Route::get('/stats', [PackingApiController::class, 'stats'])->name('api.packing.stats');

    // Session endpoints (require auth via web session)
    Route::middleware('web', 'auth')->group(function () {
        Route::post('/sessions/clock-in', [PackingApiController::class, 'clockIn'])->name('api.packing.clockIn');
        Route::post('/sessions/clock-out', [PackingApiController::class, 'clockOut'])->name('api.packing.clockOut');
        Route::get('/sessions/active', [PackingApiController::class, 'activeSession'])->name('api.packing.activeSession');
    });

    Route::get('/{qrToken}', [PackingApiController::class, 'show'])->name('api.packing.show');
    Route::post('/{list}/scan', [PackingApiController::class, 'scan'])->name('api.packing.scan');
    Route::post('/{list}/item/{packingItem}/pack', [PackingApiController::class, 'quickPack'])->name('api.packing.quickPack');
    Route::get('/{list}/item/{packingItem}/substitutes', [PackingApiController::class, 'substitutes'])->name('api.packing.substitutes');
    Route::post('/{list}/item/{packingItem}/substitute', [PackingApiController::class, 'substitute'])->name('api.packing.substitute');
    Route::post('/{list}/complete', [PackingApiController::class, 'complete'])->name('api.packing.complete');
    Route::post('/{list}/verify', [PackingApiController::class, 'verify'])->name('api.packing.verify');
});
