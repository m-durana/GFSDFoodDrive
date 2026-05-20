/**
 * Visual sweep setup helper.
 *
 * Runs after the global reset to:
 *  - turn on feature flags (adopt-a-tag, self-registration, packing system,
 *    drivers can see phone)
 *  - read out the tokens / PINs we need to drive token-bearer flows
 *    (driver route token + PIN, adopter token, family status token,
 *    shopping assignment token, child scan signed URL)
 *
 * Uses `php artisan tinker --execute` so we don't need a dedicated endpoint.
 * Cached on disk between projects so we only pay the cost once per reset.
 */

import { execFileSync } from 'node:child_process';
import { existsSync, mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { dirname, resolve } from 'node:path';

const PHP =
  process.env.PHP_BIN ??
  'C:\\Users\\mirod\\AppData\\Local\\Programs\\PHP\\current\\php.exe';

export type SweepFixtures = {
  driverToken: string;
  driverPin: string;
  driverRouteId: number;
  adopterToken: string;
  adopterChildId: number;
  familyStatusToken: string;
  familyStatusFamilyId: number;
  shoppingAssignmentToken: string | null;
  shoppingChecklistFamilyNumber: number;
  scanSignedUrl: string;
  packingListId: number | null;
  giftDropoffChildId: number;
  familyEditId: number;
  childEditId: number;
  warehouseItemId: number;
  deliveryRouteIdForAdmin: number;
};

const CACHE_PATH = resolve(
  process.cwd(),
  'test-results',
  'visual-sweep-fixtures.json'
);

const TINKER_SCRIPT = `
use App\\Models\\Setting;
use App\\Models\\Family;
use App\\Models\\Child;
use App\\Models\\DeliveryRoute;
use App\\Models\\ShoppingAssignment;
use App\\Models\\WarehouseItem;
use App\\Models\\PackingList;
use Illuminate\\Support\\Facades\\URL;
use Illuminate\\Support\\Str;

// --- Feature flags ---------------------------------------------------------
Setting::set('adopt_a_tag_enabled', '1');
Setting::set('self_registration_enabled', true);
Setting::set('packing_system_enabled', '1');
Setting::set('packing_enabled', '1');
Setting::set('drivers_can_see_phone', '1');
Setting::set('shopping_enabled', '1');
Setting::set('family_status_enabled', '1');

// --- Token bearers ---------------------------------------------------------
$route = DeliveryRoute::orderBy('id')->first();
$child = Child::orderBy('id')->first();
$famForStatus = Family::orderBy('id')->first();

// Pick a family with at least one dietary-flag-ish child for shopping
$famForShop = Family::whereNotNull('family_number')->orderBy('family_number')->first();

// Adoption: claim an unclaimed child so we have an adopter token
$adoptable = Child::whereNull('adoption_token')->orderBy('id')->first();
if ($adoptable) {
    $adoptable->adoption_token = Str::random(40);
    $adoptable->adopter_name = 'Visual Sweep Adopter';
    $adoptable->adopter_email = 'sweep@example.com';
    $adoptable->adopter_phone = '425-555-0199';
    $adoptable->adopted_at = now();
    $adoptable->save();
}

$shoppingAssignment = ShoppingAssignment::whereNotNull('token')->orderBy('id')->first();
if (!$shoppingAssignment) {
    $shoppingAssignment = ShoppingAssignment::orderBy('id')->first();
    if ($shoppingAssignment && empty($shoppingAssignment->token)) {
        $shoppingAssignment->token = Str::random(40);
        $shoppingAssignment->save();
    }
}

// Gift drop-off needs a child whose family has tags
$dropoffChild = Child::whereHas('family', fn($q) => $q->whereNotNull('family_number'))->orderBy('id')->first();

// Pick a family without adoption for editing screens (avoid PII screenshots from sensitive cases)
$familyToEdit = Family::whereNotNull('family_number')->orderBy('id')->first();
$childToEdit = Child::orderBy('id')->first();

$warehouseItem = WarehouseItem::orderBy('id')->first();

// Try to generate a packing list if none exists
$packingList = PackingList::orderBy('id')->first();
if (!$packingList && $familyToEdit) {
    try {
        $svc = app(\\App\\Services\\PackingService::class);
        $packingList = $svc->generatePackingList($familyToEdit);
    } catch (\\Throwable $e) {
        // skip — packing list may need different generator
    }
}

$scanUrl = $child ? URL::signedRoute('scan.show', ['child' => $child->id]) : '';

$out = [
    'driverToken' => $route?->access_token ?? '',
    'driverPin' => $route?->driver_pin ?? '',
    'driverRouteId' => $route?->id ?? 0,
    'adopterToken' => $adoptable?->adoption_token ?? '',
    'adopterChildId' => $adoptable?->id ?? 0,
    'familyStatusToken' => $famForStatus?->status_token ?? '',
    'familyStatusFamilyId' => $famForStatus?->id ?? 0,
    'shoppingAssignmentToken' => $shoppingAssignment?->token ?? null,
    'shoppingChecklistFamilyNumber' => (int)($famForShop?->family_number ?? 0),
    'scanSignedUrl' => $scanUrl,
    'packingListId' => $packingList?->id ?? null,
    'giftDropoffChildId' => $dropoffChild?->id ?? 0,
    'familyEditId' => $familyToEdit?->id ?? 0,
    'childEditId' => $childToEdit?->id ?? 0,
    'warehouseItemId' => $warehouseItem?->id ?? 0,
    'deliveryRouteIdForAdmin' => $route?->id ?? 0,
];

echo "===VSWEEP===" . json_encode($out) . "===/VSWEEP===\\n";
`;

export function prepareVisualSweepFixtures(force = false): SweepFixtures {
  if (!force && existsSync(CACHE_PATH)) {
    return JSON.parse(readFileSync(CACHE_PATH, 'utf-8')) as SweepFixtures;
  }

  let stdout = '';
  try {
    stdout = execFileSync(
      PHP,
      ['artisan', 'tinker', '--env=e2e', '--execute', TINKER_SCRIPT],
      {
        cwd: process.cwd(),
        encoding: 'utf-8',
        env: { ...process.env, APP_ENV: 'e2e' },
        timeout: 120_000,
      }
    );
  } catch (e: any) {
    throw new Error(
      `Visual-sweep prep tinker failed: ${e?.message}\nstdout: ${e?.stdout ?? ''}\nstderr: ${e?.stderr ?? ''}`
    );
  }

  const match = stdout.match(/===VSWEEP===(.*)===\/VSWEEP===/s);
  if (!match) {
    throw new Error(`Could not parse VSWEEP block from tinker output:\n${stdout}`);
  }
  const fx = JSON.parse(match[1]) as SweepFixtures;
  mkdirSync(dirname(CACHE_PATH), { recursive: true });
  writeFileSync(CACHE_PATH, JSON.stringify(fx, null, 2));
  return fx;
}
