<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CoordinatorController;
use App\Http\Controllers\DeliveryDayController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\SantaController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

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
    Route::post('/{family}/children', [FamilyController::class, 'storeChild'])->name('storeChild');
});

// Santa routes: accessible only by Santa role
Route::middleware(['auth', 'permission:santa'])->prefix('santa')->name('santa.')->group(function () {
    Route::get('/', [SantaController::class, 'index'])->name('index');
    Route::get('/families', [SantaController::class, 'allFamilies'])->name('families');
    Route::get('/number-assignment', [SantaController::class, 'numberAssignment'])->name('numberAssignment');
    Route::post('/number-assignment', [SantaController::class, 'updateFamilyNumber'])->name('updateFamilyNumber');
    Route::get('/users', [SantaController::class, 'users'])->name('users');
    Route::post('/users', [SantaController::class, 'storeUser'])->name('storeUser');
    Route::put('/users/{user}', [SantaController::class, 'updateUser'])->name('updateUser');
    Route::put('/users/{user}/reset-password', [SantaController::class, 'resetPassword'])->name('resetPassword');
});

// Coordinator routes: accessible by Coordinator and Santa roles
Route::middleware(['auth', 'permission:coordinator,santa'])->prefix('coordinator')->name('coordinator.')->group(function () {
    Route::get('/', [CoordinatorController::class, 'index'])->name('index');
});

// Delivery Day routes: accessible by Santa role
Route::middleware(['auth', 'permission:santa'])->prefix('delivery-day')->name('delivery.')->group(function () {
    Route::get('/', [DeliveryDayController::class, 'index'])->name('index');
});
