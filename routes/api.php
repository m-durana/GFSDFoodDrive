<?php

use App\Http\Controllers\PackingApiController;
use App\Http\Controllers\ShoppingApiController;
use App\Http\Middleware\IdempotentRequest;
use App\Http\Middleware\PackingSystemEnabled;
use Illuminate\Support\Facades\Route;

Route::prefix('shopping/{token}')->group(function () {
    Route::get('/', [ShoppingApiController::class, 'show'])->middleware('throttle:public-token-read');
    Route::post('/check', [ShoppingApiController::class, 'toggle'])->middleware('throttle:public-token-write');
});

// Packing API — authenticated staff + feature flag on.
// QR-token "bearer" access is limited to the read-only /{qrToken} show endpoint.
Route::prefix('packing')->middleware(PackingSystemEnabled::class)->group(function () {
    // Authenticated endpoints (session-based). Defined before /{qrToken} so the
    // static segments ("stats", "sessions") are not captured as tokens.
    Route::middleware(['web', 'auth'])->group(function () {
        Route::get('/stats', [PackingApiController::class, 'stats'])->name('api.packing.stats');

        Route::post('/sessions/clock-in', [PackingApiController::class, 'clockIn'])->name('api.packing.clockIn');
        Route::post('/sessions/clock-out', [PackingApiController::class, 'clockOut'])->name('api.packing.clockOut');
        Route::get('/sessions/active', [PackingApiController::class, 'activeSession'])->name('api.packing.activeSession');

        // REL-07: idempotency wrapper on every mutating action so the offline-scanner
        // drain can safely retry. Header-driven; passthrough when no header sent.
        Route::middleware(IdempotentRequest::class)->group(function () {
            Route::post('/{list}/scan', [PackingApiController::class, 'scan'])->name('api.packing.scan');
            Route::post('/{list}/item/{packingItem}/pack', [PackingApiController::class, 'quickPack'])->name('api.packing.quickPack');
            Route::post('/{list}/item/{packingItem}/substitute', [PackingApiController::class, 'substitute'])->name('api.packing.substitute');
            Route::post('/{list}/complete', [PackingApiController::class, 'complete'])->name('api.packing.complete');
            Route::post('/{list}/verify', [PackingApiController::class, 'verify'])->name('api.packing.verify');
        });

        Route::get('/{list}/item/{packingItem}/substitutes', [PackingApiController::class, 'substitutes'])->name('api.packing.substitutes');
    });

    // Read-only load by QR token — the token itself is the credential.
    Route::get('/{qrToken}', [PackingApiController::class, 'show'])->name('api.packing.show');
});
