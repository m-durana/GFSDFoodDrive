<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CoordinatorController;
use App\Http\Controllers\DeliveryDayController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\SantaController;
use App\Http\Controllers\SelfServiceController;
use Illuminate\Support\Facades\Route;

// Root route: redirect authenticated users to their dashboard, guests to login
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->isSanta()) {
            return redirect()->route('santa.index');
        }
        if ($user->isCoordinator()) {
            return redirect()->route('coordinator.index');
        }
        return redirect()->route('family.index');
    }
    return redirect()->route('login');
})->name('home');

// Guest routes (login)
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

// Family routes: accessible by Family and Santa roles
Route::middleware(['auth', 'permission:family,santa'])->prefix('family')->name('family.')->group(function () {
    Route::get('/', [FamilyController::class, 'index'])->name('index');
    Route::get('/add', [FamilyController::class, 'create'])->name('create');
    Route::post('/add', [FamilyController::class, 'store'])->name('store');
    Route::get('/{family}', [FamilyController::class, 'show'])->name('show');
    Route::get('/{family}/edit', [FamilyController::class, 'edit'])->name('edit');
    Route::put('/{family}', [FamilyController::class, 'update'])->name('update');
    Route::post('/{family}/children', [FamilyController::class, 'storeChild'])->name('storeChild');
    Route::put('/{family}/children/{child}', [FamilyController::class, 'updateChild'])->name('updateChild');
    Route::delete('/{family}/children/{child}', [FamilyController::class, 'destroyChild'])->name('destroyChild');
    Route::post('/{family}/toggle-done', [FamilyController::class, 'toggleDone'])->name('toggleDone');
});

// Santa routes: accessible only by Santa role
Route::middleware(['auth', 'permission:santa'])->prefix('santa')->name('santa.')->group(function () {
    Route::get('/', [SantaController::class, 'index'])->name('index');
    Route::get('/families', [SantaController::class, 'allFamilies'])->name('families');
    Route::get('/number-assignment', [SantaController::class, 'numberAssignment'])->name('numberAssignment');
    Route::post('/number-assignment', [SantaController::class, 'updateFamilyNumber'])->name('updateFamilyNumber');
    Route::post('/number-assignment/auto-assign', [SantaController::class, 'autoAssign'])->name('autoAssign');
    Route::get('/school-ranges', [SantaController::class, 'schoolRanges'])->name('schoolRanges');
    Route::post('/school-ranges', [SantaController::class, 'storeSchoolRange'])->name('storeSchoolRange');
    Route::put('/school-ranges/{schoolRange}', [SantaController::class, 'updateSchoolRange'])->name('updateSchoolRange');
    Route::delete('/school-ranges/{schoolRange}', [SantaController::class, 'destroySchoolRange'])->name('destroySchoolRange');
    Route::get('/gifts', [SantaController::class, 'gifts'])->name('gifts');
    Route::get('/settings', [SantaController::class, 'settings'])->name('settings');
    Route::post('/settings', [SantaController::class, 'updateSettings'])->name('updateSettings');
    Route::get('/users', [SantaController::class, 'users'])->name('users');
    Route::post('/users', [SantaController::class, 'storeUser'])->name('storeUser');
    Route::put('/users/{user}', [SantaController::class, 'updateUser'])->name('updateUser');
    Route::put('/users/{user}/reset-password', [SantaController::class, 'resetPassword'])->name('resetPassword');
});

// Coordinator routes: accessible by Coordinator and Santa roles
Route::middleware(['auth', 'permission:coordinator,santa'])->prefix('coordinator')->name('coordinator.')->group(function () {
    Route::get('/', [CoordinatorController::class, 'index'])->name('index');
    Route::get('/gift-tags', [CoordinatorController::class, 'giftTags'])->name('giftTags');
    Route::get('/family-summary', [CoordinatorController::class, 'familySummary'])->name('familySummary');
    Route::get('/delivery-day', [CoordinatorController::class, 'deliveryDay'])->name('deliveryDay');
});

// Self-service family registration (public when enabled by admin)
Route::get('/register-family', [SelfServiceController::class, 'create'])->name('self-service.create');
Route::post('/register-family', [SelfServiceController::class, 'store'])->name('self-service.store');
Route::get('/register-family/success', [SelfServiceController::class, 'success'])->name('self-service.success');

// Delivery Day routes: accessible by Santa role
Route::middleware(['auth', 'permission:santa'])->prefix('delivery-day')->name('delivery.')->group(function () {
    Route::get('/', [DeliveryDayController::class, 'index'])->name('index');
});
