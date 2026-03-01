<?php

use App\Http\Controllers\AdoptionController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\CoordinatorController;
use App\Http\Controllers\DeliveryDayController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\FamilyStatusController;
use App\Http\Controllers\SantaController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\SeasonController;
use App\Http\Controllers\CommandCenterController;
use App\Http\Controllers\DeliveryRouteController;
use App\Http\Controllers\DeliveryTeamController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SelfServiceController;
use App\Http\Controllers\ShoppingController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

// Root route: show public homepage for everyone
Route::get('/', function () {
    $selfRegistrationEnabled = \App\Models\Setting::get('self_registration_enabled', false);
    $adoptionEnabled = \App\Models\Setting::get('adoption_enabled', true);
    return view('welcome', compact('selfRegistrationEnabled', 'adoptionEnabled'));
})->name('home');

// Guest routes (login)
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Dashboard redirect: sends user to their role-appropriate page
    Route::get('/dashboard', function () {
        $user = auth()->user();
        if ($user->isSanta()) {
            return redirect()->route('santa.index');
        }
        if ($user->isCoordinator()) {
            return redirect()->route('coordinator.index');
        }
        return redirect()->route('family.index');
    })->name('dashboard');
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
    Route::get('/reports', [SantaController::class, 'reports'])->name('reports');
    Route::get('/export', [SantaController::class, 'exportFamilies'])->name('export');
    Route::get('/volunteers', [SantaController::class, 'volunteers'])->name('volunteers');
    Route::post('/volunteers/assign', [SantaController::class, 'assignVolunteer'])->name('assignVolunteer');
    Route::delete('/volunteers/unassign/{family}', [SantaController::class, 'unassignVolunteer'])->name('unassignVolunteer');
    Route::get('/volunteers/{user}/list', [SantaController::class, 'volunteerList'])->name('volunteerList');
    Route::get('/shopping-list', [SantaController::class, 'shoppingList'])->name('shoppingList');
    Route::post('/shopping-list/items', [SantaController::class, 'storeGroceryItem'])->name('storeGroceryItem');
    Route::put('/shopping-list/items/{groceryItem}', [SantaController::class, 'updateGroceryItem'])->name('updateGroceryItem');
    Route::delete('/shopping-list/items/{groceryItem}', [SantaController::class, 'destroyGroceryItem'])->name('destroyGroceryItem');
    Route::post('/shopping-list/import', [SantaController::class, 'importGroceryItems'])->name('importGroceryItems');
    Route::get('/shopping-list/export-formula', [SantaController::class, 'exportGroceryFormula'])->name('exportGroceryFormula');
    Route::get('/shopping-day', [SantaController::class, 'shoppingDay'])->name('shoppingDay');
    Route::post('/shopping-day/assignments', [SantaController::class, 'createAssignment'])->name('createAssignment');
    Route::delete('/shopping-day/assignments/{assignment}', [SantaController::class, 'deleteAssignment'])->name('deleteAssignment');
    Route::get('/settings', [SantaController::class, 'settings'])->name('settings');
    Route::post('/settings', [SantaController::class, 'updateSettings'])->name('updateSettings');
    Route::post('/settings/test-email', [SantaController::class, 'testEmail'])->name('testEmail');
    Route::get('/users', [SantaController::class, 'users'])->name('users');
    Route::post('/users', [SantaController::class, 'storeUser'])->name('storeUser');
    Route::put('/users/{user}', [SantaController::class, 'updateUser'])->name('updateUser');
    Route::put('/users/{user}/reset-password', [SantaController::class, 'resetPassword'])->name('resetPassword');

    // Access Requests (OAuth approval flow)
    Route::post('/access-requests/{accessRequest}/approve', [SantaController::class, 'approveAccessRequest'])->name('approveAccessRequest');
    Route::post('/access-requests/{accessRequest}/deny', [SantaController::class, 'denyAccessRequest'])->name('denyAccessRequest');

    // Command Center
    Route::get('/command-center', [CommandCenterController::class, 'index'])->name('commandCenter');
    Route::get('/command-center/data', [CommandCenterController::class, 'data'])->name('commandCenter.data');

    // Delivery Teams
    Route::post('/delivery-teams', [DeliveryTeamController::class, 'store'])->name('deliveryTeams.store');
    Route::put('/delivery-teams/{team}', [DeliveryTeamController::class, 'update'])->name('deliveryTeams.update');
    Route::delete('/delivery-teams/{team}', [DeliveryTeamController::class, 'destroy'])->name('deliveryTeams.destroy');

    // Delivery Routes
    Route::get('/delivery-routes', [DeliveryRouteController::class, 'index'])->name('deliveryRoutes.index');
    Route::post('/delivery-routes', [DeliveryRouteController::class, 'store'])->name('deliveryRoutes.store');
    Route::delete('/delivery-routes/{deliveryRoute}', [DeliveryRouteController::class, 'destroy'])->name('deliveryRoutes.destroy');
    Route::post('/delivery-routes/optimize', [DeliveryRouteController::class, 'optimize'])->name('deliveryRoutes.optimize');
    Route::put('/delivery-routes/{deliveryRoute}/families', [DeliveryRouteController::class, 'updateFamilies'])->name('deliveryRoutes.updateFamilies');

    // Backups
    Route::get('/backups', [SantaController::class, 'backups'])->name('backups');
    Route::post('/backups/create', [SantaController::class, 'createBackup'])->name('createBackup');
    Route::get('/backups/download/{filename}', [SantaController::class, 'downloadBackup'])->name('downloadBackup');
    Route::post('/backups/rollback', [SantaController::class, 'rollbackBackup'])->name('rollbackBackup');

    // Season Archive & Import
    Route::get('/seasons', [SeasonController::class, 'index'])->name('seasons.index');
    Route::get('/seasons/import', [SeasonController::class, 'importForm'])->name('seasons.import');
    Route::post('/seasons/import/preview', [SeasonController::class, 'previewImport'])->name('seasons.previewImport');
    Route::post('/seasons/import/execute', [SeasonController::class, 'executeImport'])->name('seasons.executeImport');
    Route::get('/seasons/import/access-tables', [SeasonController::class, 'accessTables'])->name('seasons.accessTables');
    Route::post('/seasons/import/access-preview', [SeasonController::class, 'previewAccessTable'])->name('seasons.previewAccessTable');
    Route::post('/seasons/import/legacy', [SeasonController::class, 'importLegacy'])->name('seasons.importLegacy');
    Route::post('/seasons/import/all-access', [SeasonController::class, 'importAllAccess'])->name('seasons.importAllAccess');
    Route::post('/seasons/import/all-legacy', [SeasonController::class, 'importAllLegacy'])->name('seasons.importAllLegacy');
    Route::post('/seasons/archive', [SeasonController::class, 'archive'])->name('seasons.archive');
    Route::get('/seasons/{season}', [SeasonController::class, 'show'])->name('seasons.show');
    Route::get('/seasons/{season}/families', [SeasonController::class, 'families'])->name('seasons.families');
});

// Family status token regeneration (coordinator+)
Route::middleware(['auth', 'permission:coordinator,santa'])->group(function () {
    Route::post('/family/{family}/regenerate-status', [FamilyStatusController::class, 'regenerateToken'])->name('family.regenerateStatus');
});

// Coordinator routes: accessible by Coordinator and Santa roles
Route::middleware(['auth', 'permission:coordinator,santa'])->prefix('coordinator')->name('coordinator.')->group(function () {
    Route::get('/', [CoordinatorController::class, 'index'])->name('index');
    Route::get('/gift-tags', [CoordinatorController::class, 'giftTags'])->name('giftTags');
    Route::get('/family-summary', [CoordinatorController::class, 'familySummary'])->name('familySummary');
    Route::get('/delivery-day', [CoordinatorController::class, 'deliveryDay'])->name('deliveryDay');
});

// QR Code scan routes (public, secured by signed URLs)
Route::get('/scan/{child}', [ScanController::class, 'show'])->name('scan.show')->middleware('signed');
Route::put('/scan/{child}', [ScanController::class, 'update'])->name('scan.update');

// Mobile shopping companion (public routes for volunteers/NINJAs)
Route::get('/shopping/a/{token}', [ShoppingController::class, 'assignmentByToken'])->name('shopping.assignment');
Route::get('/shopping/{family_number}', [ShoppingController::class, 'checklist'])->name('shopping.checklist');

// Google OAuth routes
Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');
Route::get('/auth/google/request', [GoogleController::class, 'requestAccess'])->name('auth.google.request');
Route::post('/auth/google/request', [GoogleController::class, 'submitRequest'])->name('auth.google.submitRequest');

// Family Status Page (public when enabled)
Route::get('/family-status/{token}', [FamilyStatusController::class, 'show'])->name('family.status');

// Adopt-a-Tag Portal (public when enabled)
Route::get('/adopt', [AdoptionController::class, 'index'])->name('adopt.index');
Route::get('/adopt/mine/{token}', [AdoptionController::class, 'confirmation'])->name('adopt.confirmation');
Route::post('/adopt/mine/{token}/delivered', [AdoptionController::class, 'markDelivered'])->name('adopt.markDelivered');
Route::get('/adopt/{child}', [AdoptionController::class, 'show'])->name('adopt.show');
Route::post('/adopt/{child}/claim', [AdoptionController::class, 'claim'])->name('adopt.claim')->middleware('throttle:5,1');

// Driver route view (public, token-secured)
Route::get('/delivery/route/{token}', [DeliveryRouteController::class, 'driverView'])->name('delivery.driverView');
Route::post('/delivery/route/{token}/complete/{family}', [DeliveryRouteController::class, 'completeStop'])->name('delivery.completeStop');
Route::get('/delivery/route/{token}/data', [DeliveryRouteController::class, 'routeData'])->name('delivery.routeData');
Route::post('/delivery/route/{token}/location', [DeliveryRouteController::class, 'updateDriverLocation'])->name('delivery.updateDriverLocation');
Route::post('/delivery/route/{token}/heading/{family}', [DeliveryRouteController::class, 'markHeading'])->name('delivery.markHeading');

// Self-service family registration (public when enabled by admin)
Route::get('/register-family', [SelfServiceController::class, 'create'])->name('self-service.create');
Route::post('/register-family', [SelfServiceController::class, 'store'])->name('self-service.store');
Route::get('/register-family/success', [SelfServiceController::class, 'success'])->name('self-service.success');

// Warehouse routes: accessible by Coordinator and Santa roles
Route::middleware(['auth', 'permission:coordinator,santa'])->prefix('warehouse')->name('warehouse.')->group(function () {
    Route::get('/', [WarehouseController::class, 'index'])->name('index');
    Route::get('/receive', [WarehouseController::class, 'receive'])->name('receive');
    Route::post('/receive', [WarehouseController::class, 'store'])->name('store');
    Route::get('/inventory', [WarehouseController::class, 'inventory'])->name('inventory');
    Route::get('/transactions', [WarehouseController::class, 'transactions'])->name('transactions');
    Route::get('/barcode/{barcode}', [WarehouseController::class, 'lookupBarcode'])->name('barcode.lookup');
    Route::get('/gift-dropoff/{child}', [WarehouseController::class, 'giftDropoff'])->name('gift.dropoff');
    Route::post('/gift-dropoff/{child}', [WarehouseController::class, 'confirmGiftDropoff'])->name('gift.dropoff.confirm');
    Route::get('/kiosk', [WarehouseController::class, 'kiosk'])->name('kiosk');
    Route::get('/mobile-scan', [WarehouseController::class, 'mobileScan'])->name('mobile-scan');
    Route::get('/gifts-intake', [WarehouseController::class, 'giftsIntake'])->name('gifts-intake');
});

// Help/Wiki routes (accessible by all authenticated users)
Route::middleware('auth')->group(function () {
    Route::get('/help', [HelpController::class, 'index'])->name('help.index');
    Route::get('/help/{topic}', [HelpController::class, 'show'])->name('help.show');
});

// Delivery Day routes: accessible by Santa role
Route::middleware(['auth', 'permission:santa'])->prefix('delivery-day')->name('delivery.')->group(function () {
    Route::get('/', [DeliveryDayController::class, 'index'])->name('index');
    Route::put('/{family}/status', [DeliveryDayController::class, 'updateStatus'])->name('updateStatus');
    Route::patch('/{family}/status-ajax', [DeliveryDayController::class, 'updateStatusAjax'])->name('updateStatusAjax');
    Route::put('/{family}/team', [DeliveryDayController::class, 'updateTeam'])->name('updateTeam');
    Route::post('/bulk-assign-team', [DeliveryDayController::class, 'bulkAssignTeam'])->name('bulkAssignTeam');
    Route::post('/{family}/log', [DeliveryDayController::class, 'addLog'])->name('addLog');
    Route::get('/logs', [DeliveryDayController::class, 'logs'])->name('logs');
    Route::get('/map', [DeliveryDayController::class, 'map'])->name('map');
    Route::get('/map-data', [DeliveryDayController::class, 'mapData'])->name('mapData');
    Route::post('/location', [DeliveryDayController::class, 'updateLocation'])->name('updateLocation');
    Route::get('/track', [DeliveryDayController::class, 'track'])->name('track');
    Route::post('/quick-assign', [DeliveryDayController::class, 'quickAssign'])->name('quickAssign');
    Route::post('/routes/{deliveryRoute}/add-families', [DeliveryDayController::class, 'addFamiliesToRoute'])->name('addFamiliesToRoute');
    Route::post('/routes/{deliveryRoute}/mark-picked-up', [DeliveryDayController::class, 'markRoutePickedUp'])->name('markRoutePickedUp');
});

// Santa duplicate detection routes
Route::middleware(['auth', 'permission:santa'])->prefix('santa')->name('santa.')->group(function () {
    Route::get('/duplicates', [SantaController::class, 'duplicates'])->name('duplicates');
    Route::post('/duplicates/dismiss', [SantaController::class, 'dismissDuplicate'])->name('dismissDuplicate');
    Route::post('/duplicates/merge', [SantaController::class, 'mergeFamilies'])->name('mergeFamilies');
    Route::post('/geocode-families', [SantaController::class, 'geocodeFamilies'])->name('geocodeFamilies');
    Route::get('/adoptions', [AdoptionController::class, 'adminDashboard'])->name('adoptions');
    Route::post('/adoptions/{child}/release', [AdoptionController::class, 'release'])->name('releaseAdoption');
    Route::post('/adoptions/{child}/complete', [AdoptionController::class, 'complete'])->name('completeAdoption');
});
