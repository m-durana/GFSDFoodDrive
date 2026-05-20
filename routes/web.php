<?php

use App\Http\Controllers\AdoptionController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\AnalyticsController;
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
use App\Http\Controllers\GiftBankController;
use App\Http\Controllers\PackingController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\Santa\RolesController as SantaRolesController;
use Illuminate\Support\Facades\Route;

// E2E test harness: reset DB to a known seeded state. Guarded to non-prod
// + requires X-E2E-Token header. Loaded by routes/web.php so it shares session
// middleware, but the env guard prevents accidental prod exposure.
if (! app()->environment('production')) {
    Route::post('/__e2e/reset', function (\Illuminate\Http\Request $request) {
        $expected = env('E2E_RESET_TOKEN', 'e2e-local-token');
        if ($request->header('X-E2E-Token') !== $expected) {
            abort(403, 'Bad E2E token');
        }
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        // Serialize concurrent resets. Two Playwright agents calling this
        // simultaneously would otherwise race on migrate:fresh's drop/recreate
        // and return 500. Use an atomic file-based lock so it works regardless
        // of cache driver (file/array/redis).
        $lockPath = storage_path('framework/e2e-reset.lock');
        if (! is_dir(dirname($lockPath))) {
            @mkdir(dirname($lockPath), 0775, true);
        }
        $fh = @fopen($lockPath, 'c');
        if ($fh === false) {
            abort(500, 'Could not open e2e reset lock file');
        }
        // Wait up to 60s for the lock; if another reset is in flight we want
        // to queue, not 500.
        $acquired = false;
        $deadline = microtime(true) + 60.0;
        while (microtime(true) < $deadline) {
            if (flock($fh, LOCK_EX | LOCK_NB)) {
                $acquired = true;
                break;
            }
            usleep(100_000); // 100ms
        }
        if (! $acquired) {
            fclose($fh);
            return response()->json([
                'ok' => false,
                'error' => 'Another reset in progress; timed out waiting for lock',
            ], 429);
        }

        try {
            \Illuminate\Support\Facades\Artisan::call('migrate:fresh', [
                '--seed' => true,
                '--force' => true,
            ]);
            return response()->json([
                'ok' => true,
                'output' => \Illuminate\Support\Facades\Artisan::output(),
            ]);
        } finally {
            flock($fh, LOCK_UN);
            fclose($fh);
        }
    })->withoutMiddleware([
        \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ]);
}

// Root route: show public homepage for everyone.
// REL-06: public-locale middleware picks en/es per ?lang=, cookie, or
// Accept-Language so the landing page renders in the visitor's language.
Route::middleware('public-locale')->get('/', function () {
    $selfRegistrationEnabled = \App\Models\Setting::get('self_registration_enabled', false);
    $adoptionEnabled = \App\Models\Setting::get('adopt_a_tag_enabled', '0') === '1';
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
    Route::put('/profile', [ProfileController::class, 'update'])
        ->middleware('throttle:30,1')
        ->name('profile.update');

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

    // REL-46c: Santa-editable Spatie roles.
    Route::get('/roles', [SantaRolesController::class, 'index'])->name('roles.index');
    Route::post('/roles', [SantaRolesController::class, 'store'])->name('roles.store');
    Route::put('/roles/{role}', [SantaRolesController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [SantaRolesController::class, 'destroy'])->name('roles.destroy');
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
    // Shopping Hub (consolidated view)
    Route::get('/shopping', [SantaController::class, 'shopping'])->name('shopping');

    // Old routes → redirects (preserves bookmarks)
    Route::get('/shopping-list', fn(\Illuminate\Http\Request $r) =>
        redirect()->route('santa.shopping', array_merge($r->query(), ['tab' => 'formulas']))
    )->name('shoppingList');
    Route::get('/shopping-day', fn() =>
        redirect()->route('santa.shopping', ['tab' => 'assignments'])
    )->name('shoppingDay');

    // Shopping item management (POST/PUT/DELETE unchanged)
    Route::post('/shopping-list/items', [SantaController::class, 'storeGroceryItem'])->name('storeGroceryItem');
    Route::put('/shopping-list/items/{groceryItem}', [SantaController::class, 'updateGroceryItem'])->name('updateGroceryItem');
    Route::delete('/shopping-list/items/{groceryItem}', [SantaController::class, 'destroyGroceryItem'])->name('destroyGroceryItem');
    Route::post('/shopping-list/import', [SantaController::class, 'importGroceryItems'])->name('importGroceryItems');
    Route::get('/shopping-list/export-formula', [SantaController::class, 'exportGroceryFormula'])->name('exportGroceryFormula');
    Route::post('/shopping-day/assignments', [SantaController::class, 'createAssignment'])->name('createAssignment');
    Route::delete('/shopping-day/assignments/{assignment}', [SantaController::class, 'deleteAssignment'])->name('deleteAssignment');
    Route::get('/settings', [SantaController::class, 'settings'])->name('settings');
    Route::post('/settings', [SantaController::class, 'updateSettings'])->name('updateSettings');
    Route::post('/settings/test-email', [SantaController::class, 'testEmail'])->name('testEmail');
    Route::get('/users', [SantaController::class, 'users'])->name('users');
    Route::post('/users', [SantaController::class, 'storeUser'])->name('storeUser');
    Route::put('/users/{user}', [SantaController::class, 'updateUser'])->name('updateUser');
    Route::put('/users/{user}/reset-password', [SantaController::class, 'resetPassword'])->name('resetPassword');
    Route::post('/users/bulk-update', [SantaController::class, 'bulkUpdateUsers'])->name('bulkUpdateUsers');
    Route::delete('/users/{user}', [SantaController::class, 'deleteUser'])->name('deleteUser');
    Route::post('/users/{user}/randomize-avatar', [SantaController::class, 'randomizeUserAvatar'])->name('randomizeUserAvatar');

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
    Route::post('/delivery-routes/{deliveryRoute}/recalculate', [DeliveryRouteController::class, 'recalculate'])->name('deliveryRoutes.recalculate');

    // Backups
    Route::get('/backups', [SantaController::class, 'backups'])->name('backups');
    Route::post('/backups/create', [SantaController::class, 'createBackup'])->name('createBackup');
    Route::get('/backups/download/{filename}', [SantaController::class, 'downloadBackup'])->name('downloadBackup');
    Route::post('/backups/rollback', [SantaController::class, 'rollbackBackup'])->name('rollbackBackup');

    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
    Route::get('/analytics/export', [AnalyticsController::class, 'export'])->name('analytics.export');

    // Season Archive & Import
    Route::get('/seasons', [SeasonController::class, 'index'])->name('seasons.index');
    Route::get('/seasons/import', [SeasonController::class, 'importForm'])->name('seasons.import');
    Route::post('/seasons/import/preview', [SeasonController::class, 'previewImport'])->name('seasons.previewImport');
    Route::post('/seasons/import/execute', [SeasonController::class, 'executeImport'])->name('seasons.executeImport');
    Route::get('/seasons/import/status/{key}', [SeasonController::class, 'importStatus'])->name('seasons.importStatus');
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

    // PDF generators (REL-46a): system-section only. Santa + System Coordinator
    // implicitly pass the section gate; regular Coordinators are blocked unless
    // Santa has remapped their position to include `system` in the section map.
    Route::middleware('section:system')->group(function () {
        Route::get('/gift-tags', [CoordinatorController::class, 'giftTags'])->name('giftTags');
        Route::get('/family-summary', [CoordinatorController::class, 'familySummary'])->name('familySummary');
        Route::get('/delivery-day', [CoordinatorController::class, 'deliveryDay'])->name('deliveryDay');
        Route::get('/pdf-status/{jobKey}', [CoordinatorController::class, 'pdfStatus'])->name('pdfStatus');
        Route::get('/pdf-download/{jobKey}', [CoordinatorController::class, 'pdfDownload'])->name('pdfDownload');
    });
});

// QR Code scan routes (public, secured by signed URLs)
Route::middleware('signed')->group(function () {
    Route::get('/scan/{child}', [ScanController::class, 'show'])->name('scan.show');
    Route::put('/scan/{child}', [ScanController::class, 'update'])->name('scan.update')->middleware('throttle:30,1');
});

// REL-06: every public/token-bearer surface below picks en/es via the
// public-locale middleware. Staff/Santa pages stay English.
Route::middleware('public-locale')->group(function () {

// Mobile shopping companion (public routes for volunteers/NINJAs)
Route::get('/shopping/a/{token}', [ShoppingController::class, 'assignmentByToken'])->name('shopping.assignment');
Route::get('/shopping/{family_number}', [ShoppingController::class, 'checklist'])->name('shopping.checklist');

// Google OAuth routes
Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');
Route::get('/auth/google/request', [GoogleController::class, 'requestAccess'])->name('auth.google.request');
Route::post('/auth/google/request', [GoogleController::class, 'submitRequest'])->middleware('throttle:public-form-submit')->name('auth.google.submitRequest');

// Family Status Page (public when enabled)
Route::get('/family-status/{token}', [FamilyStatusController::class, 'show'])->middleware('throttle:public-token-read')->name('family.status');

// Adopt-a-Tag Portal (public when enabled)
Route::get('/adopt', [AdoptionController::class, 'index'])->name('adopt.index');
Route::get('/adopt/mine/{token}', [AdoptionController::class, 'confirmation'])->name('adopt.confirmation');
Route::post('/adopt/mine/{token}/delivered', [AdoptionController::class, 'markDelivered'])->name('adopt.markDelivered');
Route::get('/adopt/{child}', [AdoptionController::class, 'show'])->name('adopt.show');
Route::post('/adopt/{child}/claim', [AdoptionController::class, 'claim'])->name('adopt.claim')->middleware('throttle:5,1');

// Driver route view (public, token-secured)
Route::get('/delivery/route/{token}', [DeliveryRouteController::class, 'driverView'])->name('delivery.driverView');
Route::post('/delivery/route/{token}/verify', [DeliveryRouteController::class, 'verifyDriverPin'])
    ->middleware('throttle:5,1')
    ->name('delivery.verifyDriverPin');
Route::post('/delivery/route/{token}/complete/{stopOrder}', [DeliveryRouteController::class, 'completeStop'])->middleware('throttle:public-token-write')->name('delivery.completeStop');
Route::get('/delivery/route/{token}/data', [DeliveryRouteController::class, 'routeData'])->middleware('throttle:public-token-read')->name('delivery.routeData');
Route::post('/delivery/route/{token}/location', [DeliveryRouteController::class, 'updateDriverLocation'])->middleware('throttle:driver-location')->name('delivery.updateDriverLocation');
Route::post('/delivery/route/{token}/heading/{stopOrder}', [DeliveryRouteController::class, 'markHeading'])->middleware('throttle:public-token-write')->name('delivery.markHeading');
Route::post('/delivery/route/{token}/returning', [DeliveryRouteController::class, 'markReturning'])->middleware('throttle:public-token-write')->name('delivery.markReturning');

// Self-service family registration (public when enabled by admin)
Route::get('/register-family', [SelfServiceController::class, 'create'])->name('self-service.create');
Route::post('/register-family', [SelfServiceController::class, 'store'])->middleware('throttle:public-form-submit')->name('self-service.store');
Route::get('/register-family/success', [SelfServiceController::class, 'success'])->name('self-service.success');

}); // end REL-06 public-locale group

// Warehouse routes: accessible by Coordinator and Santa roles.
// Within the group, individual subgroups are scoped to specific sections
// (Santa + System Coordinator always pass — see CoordinatorSection middleware).
Route::middleware(['auth', 'permission:coordinator,santa'])->prefix('warehouse')->name('warehouse.')->group(function () {

    // Generic warehouse views (index, inventory, barcode lookup) — open to
    // anyone in food OR giving-tree, since both sections walk into the building.
    Route::middleware('section:food,giving-tree')->group(function () {
        Route::get('/', [WarehouseController::class, 'index'])->name('index');
        Route::get('/inventory', [WarehouseController::class, 'inventory'])->name('inventory');
        Route::get('/transactions', [WarehouseController::class, 'transactions'])->name('transactions');
        Route::get('/barcode/{barcode}', [WarehouseController::class, 'lookupBarcode'])->name('barcode.lookup');
        Route::get('/items/{item}', [WarehouseController::class, 'itemDetail'])->name('item.detail');
        Route::put('/items/{item}/location', [WarehouseController::class, 'updateItemLocation'])->name('item.location');
        Route::delete('/items/{item}/remove', [WarehouseController::class, 'removeItem'])->name('item.remove');
    });

    // Food intake + kiosk — Food section only.
    Route::middleware('section:food')->group(function () {
        Route::get('/receive', [WarehouseController::class, 'receive'])->name('receive');
        Route::post('/receive', [WarehouseController::class, 'store'])->name('store');
        Route::get('/kiosk', [WarehouseController::class, 'kiosk'])->name('kiosk');
    });

    // Gift drop-off + gift kiosk + gift intake + child gifts — Giving Tree only.
    Route::middleware('section:giving-tree')->group(function () {
        Route::get('/gift-dropoff/{child}', [WarehouseController::class, 'giftDropoff'])->name('gift.dropoff');
        Route::post('/gift-dropoff/{child}', [WarehouseController::class, 'confirmGiftDropoff'])->name('gift.dropoff.confirm');
        Route::get('/kiosk/gifts', [WarehouseController::class, 'giftKiosk'])->name('kiosk.gifts');
        Route::get('/gifts-intake', [WarehouseController::class, 'giftsIntake'])->name('gifts-intake');
        Route::get('/child/{child}/gifts', [WarehouseController::class, 'childGifts'])->name('child.gifts');

        // Gift Bank — also Giving Tree.
        Route::get('/gift-bank', [GiftBankController::class, 'index'])->name('gift-bank');
        Route::post('/gift-bank', [GiftBankController::class, 'store'])->name('gift-bank.store');
        Route::post('/gift-bank/{item}/assign/{child}', [GiftBankController::class, 'assign'])->name('gift-bank.assign');
        Route::post('/gift-bank/{item}/unassign', [GiftBankController::class, 'unassign'])->name('gift-bank.unassign');
        Route::delete('/gift-bank/{item}', [GiftBankController::class, 'destroy'])->name('gift-bank.destroy');
        Route::get('/gift-bank/suggestions/{child}', [GiftBankController::class, 'suggestions'])->name('gift-bank.suggestions');
    });
});

// Mobile scanner (public — QR token on packing list provides access)
Route::middleware(\App\Http\Middleware\PackingSystemEnabled::class)->group(function () {
    Route::get('/warehouse/mobile-scan', [WarehouseController::class, 'mobileScan'])->name('warehouse.mobile-scan');
});

// Help/Wiki routes (accessible by all authenticated users)
Route::middleware('auth')->group(function () {
    Route::get('/help', [HelpController::class, 'index'])->name('help.index');
    Route::get('/help/{topic}', [HelpController::class, 'show'])->name('help.show');
});

// Delivery Day map & location: Coordinator field-leads + Santa (C-06).
// Open to any Coordinator (field-lead duty is cross-functional); PII redaction
// in the controller protects names/addresses for non-PII coordinators.
Route::middleware(['auth', 'permission:coordinator,santa'])->prefix('delivery-day')->name('delivery.')->group(function () {
    Route::get('/map', [DeliveryDayController::class, 'map'])->name('map');
    Route::get('/map-data', [DeliveryDayController::class, 'mapData'])->name('mapData');
    Route::post('/location', [DeliveryDayController::class, 'updateLocation'])->name('updateLocation');
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
    Route::get('/track', [DeliveryDayController::class, 'track'])->name('track');
    Route::post('/quick-assign', [DeliveryDayController::class, 'quickAssign'])->name('quickAssign');
    Route::post('/routes/{deliveryRoute}/add-families', [DeliveryDayController::class, 'addFamiliesToRoute'])->name('addFamiliesToRoute');
    Route::post('/routes/{deliveryRoute}/mark-returning', [DeliveryDayController::class, 'markRouteReturning'])->name('markRouteReturning');
});

// Packing list routes — Packing section.
Route::middleware(['auth', 'permission:coordinator,santa', 'section:packing', \App\Http\Middleware\PackingSystemEnabled::class])->prefix('santa/packing')->name('packing.')->group(function () {
    Route::get('/', [PackingController::class, 'index'])->name('index');
    Route::get('/dashboard', [PackingController::class, 'dashboard'])->name('dashboard');
    Route::get('/summary', [PackingController::class, 'summary'])->name('summary');
    Route::post('/generate', [PackingController::class, 'generate'])->name('generate');
    Route::post('/print-batch', [PackingController::class, 'printBatch'])->name('printBatch');
    Route::get('/verify-station', [PackingController::class, 'verifyStation'])->name('verifyStation');
    Route::get('/{packingList}', [PackingController::class, 'show'])->name('show');
    Route::get('/{packingList}/print', [PackingController::class, 'print'])->name('print');
    Route::post('/{packingList}/refresh', [PackingController::class, 'refreshList'])->name('refresh');
    Route::post('/{packingList}/verify', [PackingController::class, 'verify'])->name('verify');
    Route::post('/{packingList}/notes', [PackingController::class, 'updateNotes'])->name('updateNotes');
    Route::post('/{packingList}/item/{packingItem}/pack', [PackingController::class, 'markItemPacked'])->name('packItem');
    Route::post('/family/{family}/generate', [PackingController::class, 'generateSingle'])->name('generateSingle');
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
