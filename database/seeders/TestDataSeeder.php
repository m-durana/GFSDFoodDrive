<?php

namespace Database\Seeders;

use App\Enums\DeliveryStatus;
use App\Enums\TransactionType;
use App\Models\Child;
use App\Models\DeliveryRoute;
use App\Models\DeliveryTeam;
use App\Models\Family;
use App\Models\GroceryItem;
use App\Models\Setting;
use App\Models\ShoppingAssignment;
use App\Models\User;
use App\Models\WarehouseCategory;
use App\Models\WarehouseItem;
use App\Models\WarehouseTransaction;
use App\Services\RoutePlanningService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;


class TestDataSeeder extends Seeder
{
    // ---------------------------------------------------------------------------
    // Name data pools
    // ---------------------------------------------------------------------------

    private array $firstNamesEnglish = [
        'James', 'Emily', 'Robert', 'Sarah', 'Michael', 'Jessica', 'David', 'Ashley',
        'Daniel', 'Amanda', 'Christopher', 'Megan', 'Matthew', 'Lauren', 'Joshua', 'Stephanie',
        'Andrew', 'Nicole', 'Ryan', 'Brittany', 'Brandon', 'Samantha', 'Tyler', 'Rachel',
        'Kevin', 'Hannah', 'Justin', 'Kayla', 'Jonathan', 'Amber',
    ];

    private array $firstNamesSpanish = [
        'Carlos', 'Maria', 'Luis', 'Ana', 'Jose', 'Rosa', 'Miguel', 'Elena',
        'Juan', 'Carmen', 'Pedro', 'Lucia', 'Antonio', 'Sofia', 'Francisco', 'Isabella',
        'Manuel', 'Valentina', 'Jorge', 'Gabriela',
    ];

    private array $lastNamesEnglish = [
        'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis',
        'Wilson', 'Taylor', 'Anderson', 'Thomas', 'Jackson', 'White', 'Harris', 'Martin',
        'Thompson', 'Young', 'Lee', 'Walker', 'Allen', 'Hall', 'Wright', 'Scott', 'Green',
        'Baker', 'Adams', 'Nelson', 'Hill', 'King', 'Campbell', 'Mitchell', 'Roberts',
        'Carter', 'Phillips', 'Evans', 'Turner', 'Parker', 'Collins', 'Edwards', 'Stewart',
        'Morris', 'Murphy', 'Cook', 'Rogers', 'Morgan', 'Peterson', 'Cooper', 'Reed', 'Bailey',
    ];

    private array $lastNamesSpanish = [
        'Hernandez', 'Lopez', 'Martinez', 'Gonzalez', 'Rodriguez', 'Perez', 'Sanchez',
        'Ramirez', 'Torres', 'Flores', 'Rivera', 'Gomez', 'Diaz', 'Cruz', 'Morales',
        'Castillo', 'Reyes', 'Ortiz', 'Vargas', 'Mendoza',
    ];

    private array $childFirstNamesM = [
        'Liam', 'Noah', 'Oliver', 'Elijah', 'James', 'Aiden', 'Lucas', 'Mason',
        'Ethan', 'Logan', 'Jackson', 'Sebastian', 'Mateo', 'Jack', 'Owen',
        'Theodore', 'Asher', 'Henry', 'Leo', 'Julian', 'Wyatt', 'Christopher',
        'Sebastian', 'Diego', 'Andres', 'Marco', 'Gabriel',
    ];

    private array $childFirstNamesF = [
        'Olivia', 'Emma', 'Ava', 'Charlotte', 'Sophia', 'Amelia', 'Isabella', 'Mia',
        'Evelyn', 'Harper', 'Luna', 'Camila', 'Gianna', 'Elizabeth', 'Eleanor',
        'Ella', 'Abigail', 'Sofia', 'Avery', 'Scarlett', 'Emily', 'Aria', 'Penelope',
        'Chloe', 'Layla', 'Valentina', 'Lucia', 'Catalina',
    ];

    // ---------------------------------------------------------------------------
    // Real Granite Falls area addresses with pre-computed lat/lng
    // ---------------------------------------------------------------------------

    private array $realAddresses = [
        // Granite Falls (98252)
        ['street' => 'S Granite Ave',       'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0849, 'lng' => -121.9687],
        ['street' => 'N Alder Ave',         'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0870, 'lng' => -121.9690],
        ['street' => 'Stanley St',          'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0855, 'lng' => -121.9665],
        ['street' => 'Pioneer St',          'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0862, 'lng' => -121.9710],
        ['street' => 'Quarry Rd',           'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0820, 'lng' => -121.9600],
        ['street' => 'Robe Menzel Rd',      'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0780, 'lng' => -121.9520],
        ['street' => 'Jordan Rd',           'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0900, 'lng' => -121.9750],
        ['street' => 'Mountain Loop Hwy',   'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0830, 'lng' => -121.9450],
        ['street' => 'Menzel Lake Rd',      'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0750, 'lng' => -121.9580],
        ['street' => 'Cascade Ave NE',      'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0875, 'lng' => -121.9630],
        ['street' => 'Galena St',           'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0840, 'lng' => -121.9700],
        ['street' => 'Union Ave',           'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0858, 'lng' => -121.9675],
        ['street' => 'Waite Mill Rd',       'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0810, 'lng' => -121.9550],
        ['street' => '142nd St NE',         'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0890, 'lng' => -121.9610],
        ['street' => 'Granite Ave',         'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0845, 'lng' => -121.9695],
        ['street' => 'E Pioneer St',        'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0860, 'lng' => -121.9650],
        ['street' => 'W Stanley St',        'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0852, 'lng' => -121.9720],
        ['street' => 'Alder Ave N',         'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0868, 'lng' => -121.9680],
        ['street' => 'E Galena St',         'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0843, 'lng' => -121.9660],
        ['street' => 'S Alder Ave',         'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0835, 'lng' => -121.9692],
        ['street' => 'N Granite Ave',       'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0878, 'lng' => -121.9685],
        ['street' => 'Wabash Ave',          'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0847, 'lng' => -121.9640],
        ['street' => 'Saratoga Ave',        'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0865, 'lng' => -121.9715],
        ['street' => 'Railroad Ave',        'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0850, 'lng' => -121.9705],
        ['street' => 'Robe Menzel Rd E',    'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0795, 'lng' => -121.9510],
        ['street' => 'Mountain Loop Hwy N', 'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0910, 'lng' => -121.9430],
        ['street' => 'Jordan Rd N',         'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0915, 'lng' => -121.9760],
        ['street' => 'Quarry Rd E',         'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0825, 'lng' => -121.9580],
        ['street' => 'Union Ave N',         'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0873, 'lng' => -121.9670],
        ['street' => 'Pioneer St E',        'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0856, 'lng' => -121.9645],
        ['street' => 'Cascade Ave',         'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0880, 'lng' => -121.9625],
        ['street' => 'Menzel Lake Rd E',    'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0760, 'lng' => -121.9570],
        ['street' => 'Waite Mill Rd N',     'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0818, 'lng' => -121.9545],
        ['street' => 'Granite Ave S',       'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0838, 'lng' => -121.9698],
        ['street' => 'Galena St W',         'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0842, 'lng' => -121.9725],
        ['street' => 'Stanley St E',        'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0854, 'lng' => -121.9655],
        ['street' => '142nd St NE N',       'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0895, 'lng' => -121.9605],
        ['street' => 'Railroad Ave S',      'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0833, 'lng' => -121.9708],
        ['street' => 'Saratoga Ave N',      'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0870, 'lng' => -121.9718],
        ['street' => 'Wabash Ave S',        'city' => 'Granite Falls', 'zip' => '98252', 'lat' => 48.0840, 'lng' => -121.9635],
        // Lake Stevens (98258)
        ['street' => '91st Ave NE',         'city' => 'Lake Stevens', 'zip' => '98258', 'lat' => 48.0150, 'lng' => -122.0640],
        ['street' => '20th St NE',          'city' => 'Lake Stevens', 'zip' => '98258', 'lat' => 48.0180, 'lng' => -122.0700],
        ['street' => 'Hartford Dr',         'city' => 'Lake Stevens', 'zip' => '98258', 'lat' => 48.0200, 'lng' => -122.0580],
        ['street' => 'Grade Rd',            'city' => 'Lake Stevens', 'zip' => '98258', 'lat' => 48.0130, 'lng' => -122.0720],
        ['street' => 'Callow Rd',           'city' => 'Lake Stevens', 'zip' => '98258', 'lat' => 48.0250, 'lng' => -122.0550],
        ['street' => '91st Ave SE',         'city' => 'Lake Stevens', 'zip' => '98258', 'lat' => 48.0100, 'lng' => -122.0650],
        ['street' => '24th St NE',          'city' => 'Lake Stevens', 'zip' => '98258', 'lat' => 48.0190, 'lng' => -122.0680],
        ['street' => 'Hartford Dr N',       'city' => 'Lake Stevens', 'zip' => '98258', 'lat' => 48.0220, 'lng' => -122.0590],
        ['street' => 'Grade Rd N',          'city' => 'Lake Stevens', 'zip' => '98258', 'lat' => 48.0140, 'lng' => -122.0730],
        ['street' => 'Callow Rd S',         'city' => 'Lake Stevens', 'zip' => '98258', 'lat' => 48.0260, 'lng' => -122.0540],
        // Snohomish (98290)
        ['street' => 'Avenue D',            'city' => 'Snohomish',    'zip' => '98290', 'lat' => 47.9127, 'lng' => -122.0987],
        ['street' => 'Maple Ave',           'city' => 'Snohomish',    'zip' => '98290', 'lat' => 47.9140, 'lng' => -122.0960],
        ['street' => 'Pine Ave',            'city' => 'Snohomish',    'zip' => '98290', 'lat' => 47.9135, 'lng' => -122.0975],
        ['street' => 'Ludwig Rd',           'city' => 'Snohomish',    'zip' => '98290', 'lat' => 47.9150, 'lng' => -122.0940],
        ['street' => 'Avenue D N',          'city' => 'Snohomish',    'zip' => '98290', 'lat' => 47.9160, 'lng' => -122.0990],
        // Monroe (98272)
        ['street' => 'Main St',             'city' => 'Monroe',       'zip' => '98272', 'lat' => 47.8554, 'lng' => -121.9710],
        ['street' => 'Lewis St',            'city' => 'Monroe',       'zip' => '98272', 'lat' => 47.8560, 'lng' => -121.9730],
        ['street' => 'Blakely Ave',         'city' => 'Monroe',       'zip' => '98272', 'lat' => 47.8548, 'lng' => -121.9700],
        ['street' => 'Old Owen Rd',         'city' => 'Monroe',       'zip' => '98272', 'lat' => 47.8580, 'lng' => -121.9750],
        ['street' => 'Main St S',           'city' => 'Monroe',       'zip' => '98272', 'lat' => 47.8540, 'lng' => -121.9720],
    ];

    // ---------------------------------------------------------------------------
    // Delivery / child data pools
    // ---------------------------------------------------------------------------

    private array $deliveryTimes = [
        '9:00 AM - 11:00 AM', '11:00 AM - 1:00 PM', '1:00 PM - 3:00 PM',
        '3:00 PM - 5:00 PM', 'Morning', 'Afternoon', 'Anytime',
    ];

    private array $deliveryReasons = [
        'Lives in apartment complex, no porch',
        'Works during pickup hours',
        'No transportation',
        'Mobility issues, cannot come to school',
        'Single parent, cannot leave children',
        'Works night shift, available mornings only',
    ];

    private array $petInfos = [
        'Large dog, please knock loudly', '2 cats, indoor only',
        'Friendly dog, will bark', 'Dog on chain in backyard', 'No pets',
    ];

    private array $needForHelpTexts = [
        'Recently laid off, father working two part-time jobs',
        'Single mother of three, lost job in October',
        'Father disabled, mother is sole provider',
        'Family recovering from house fire in September',
        'Parents recently immigrated, limited work authorization',
        'Both parents working minimum wage, barely covering rent',
        'Grandmother raising grandchildren on fixed income',
    ];

    private array $severeNeedTexts = [
        'Facing eviction next month, utilities shut off',
        'Father in hospital, no income coming in',
        'Domestic violence situation, recently in shelter',
        'House fire destroyed most belongings two months ago',
    ];

    private array $deliveryTeams = ['Team A', 'Team B', 'Team C', 'Team D', 'Team E'];

    private array $schools = [
        'Mountain Way', 'Mountain Way', 'Mountain Way',
        'Monte Cristo', 'Monte Cristo',
        'GFMS', 'GFMS',
        'GFHS', 'GFHS',
        'Crossroads',
        'None/Other',
    ];

    private array $clothingStyles = [
        'Casual, comfortable clothes', 'Loves bright colors',
        'Prefers sporty / athletic wear', 'Likes hoodies and sweatpants',
        'Into Minecraft / gaming themes', 'Loves dinosaurs and animals',
        'Prefers dresses and skirts', 'Likes anything Disney',
        'Into superheroes (Marvel/DC)', 'Prefers neutral colors, nothing too flashy',
        'Loves LOL Dolls and Barbie themes', 'Into outdoor / camping style',
    ];

    private array $clothingOptions = [
        'Shirts, pants, socks', 'Pants and shirts only — has enough socks',
        'Coat / jacket needed most', 'Shoes (size 6Y), pants, shirts',
        'Underwear and socks most needed', 'Winter coat, boots, warm layers',
        'Anything warm — very limited wardrobe', 'PJs, shirts, pants', 'Jeans and hoodies',
    ];

    private array $toyIdeas = [
        'LEGO sets, anything Minecraft', 'Barbies, art supplies, craft kits',
        'Hot Wheels, remote control cars', 'Board games, puzzles',
        'Stuffed animals, pretend play kitchen', 'Sports equipment (basketball, soccer ball)',
        'Books, educational toys', 'Slime kits, science experiments',
        'Baby toys — stacking rings, soft blocks', 'LOL Surprise dolls, nail polish sets',
        'Nerf guns, outdoor toys', 'Video games (Nintendo Switch)',
        'Musical instruments — small keyboard, ukulele',
        'Art supplies — markers, sketchbooks', 'Anything Pokemon or Star Wars',
    ];

    private array $giftPreferences = [
        'No preference, anything appreciated', 'Prefer practical gifts (clothing, books)',
        'Loves hands-on activities and crafts', 'Prefers toys over clothing',
        'Clothing preferred over toys', 'Books and educational materials appreciated',
        'Mix of toys and clothing',
    ];

    private array $allSizesOptions = [
        'Shirt: M, Pants: 8, Shoes: 3Y, Underwear: M',
        'Shirt: L, Pants: 10, Shoes: 5Y, Socks: M',
        'Shirt: S, Pants: 6, Shoes: 2Y',
        'Shirt: XL, Pants: 14, Shoes: 8',
        'Shirt: 4T, Pants: 4T, Shoes: 9T, Underwear: 4T',
        'Shirt: 2T, Pants: 2T, Shoes: 6T',
        'Shirt: XS, Pants: 5, Shoes: 13',
        'Shirt: M, Pants: 12, Shoes: 6, Jacket: L',
        'Onesie: 18M, Shoes: 4T',
    ];

    private array $adopterNames = [
        'The Henderson Family', 'Grace Community Church', 'Boeing Employees Group',
        'Granite Falls Rotary', 'The Kowalski Family', 'Mountain View PTA',
        'Snohomish County Teachers', 'The Nakamura Family', 'VFW Post 3617',
        'Frontier Bank Staff',
    ];

    private array $giftsReceivedTexts = [
        'Received coat, 2 shirts, pants, shoes', 'LEGO City set, book, shirt',
        'Barbie Dream House, 2 outfits', 'Basketball, shoes (size 7), 3 shirts',
        'Books, art kit, warm pajamas', 'Remote control car, pants, 2 shirts, socks',
        'Stuffed animals, dress, shoes', 'Board games, hoodie, jeans',
    ];

    private array $whereIsTagValues = ['at store', 'with volunteer', 'returned'];

    // ---------------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------------

    private function pick(array $arr): mixed
    {
        return $arr[array_rand($arr)];
    }

    private function pickIndex(array $arr, int $index): mixed
    {
        return $arr[$index % count($arr)];
    }

    private function generateFamilyNumbers(int $familiesWithNumbers): array
    {
        $seasonYear = (int) Setting::get('season_year', date('Y'));
        $existingNumbers = Family::withoutGlobalScopes()
            ->where('season_year', $seasonYear)
            ->whereNotNull('family_number')
            ->pluck('family_number')
            ->map(fn($n) => (int) $n)
            ->all();

        $existingLookup = array_fill_keys($existingNumbers, true);
        $familyNumbers = [];

        $rangeAssignments = [
            ['start' => 1, 'end' => 99, 'weight' => 8],     // Crossroads
            ['start' => 100, 'end' => 199, 'weight' => 8],   // GFHS
            ['start' => 200, 'end' => 299, 'weight' => 9],   // GFMS
            ['start' => 300, 'end' => 399, 'weight' => 9],   // Monte Cristo
            ['start' => 400, 'end' => 499, 'weight' => 10],  // Mountain Way
            ['start' => 500, 'end' => 599, 'weight' => 6],   // Special Case
        ];

        $totalWeight = array_sum(array_column($rangeAssignments, 'weight'));
        $counts = [];
        $countTotal = 0;
        foreach ($rangeAssignments as $ra) {
            $count = max(1, (int) round(($familiesWithNumbers * $ra['weight']) / $totalWeight));
            $counts[] = $count;
            $countTotal += $count;
        }
        while ($countTotal < $familiesWithNumbers) {
            $counts[($countTotal % count($counts))] += 1;
            $countTotal++;
        }
        while ($countTotal > $familiesWithNumbers) {
            for ($i = 0; $i < count($counts) && $countTotal > $familiesWithNumbers; $i++) {
                if ($counts[$i] > 1) {
                    $counts[$i] -= 1;
                    $countTotal--;
                }
            }
        }

        foreach ($rangeAssignments as $idx => $ra) {
            $count = $counts[$idx];
            $available = [];
            for ($n = $ra['start']; $n <= $ra['end']; $n++) {
                if (!isset($existingLookup[$n])) {
                    $available[] = $n;
                }
            }

            if (empty($available)) {
                continue;
            }

            $step = max(1, intdiv(count($available), $count));
            for ($i = 0; $i < $count && ($i * $step) < count($available); $i++) {
                $familyNumbers[] = $available[$i * $step];
            }

            // Fill any shortfall from remaining available numbers
            if (count($familyNumbers) < array_sum(array_slice($counts, 0, $idx + 1))) {
                foreach ($available as $n) {
                    if (count($familyNumbers) >= array_sum(array_slice($counts, 0, $idx + 1))) {
                        break;
                    }
                    if (!in_array($n, $familyNumbers, true)) {
                        $familyNumbers[] = $n;
                    }
                }
            }
        }

        // If we still need more numbers, append unique values above the existing ranges
        if (count($familyNumbers) < $familiesWithNumbers) {
            $maxExisting = empty($existingNumbers) ? 599 : max($existingNumbers);
            $next = max(1000, $maxExisting + 1);
            while (count($familyNumbers) < $familiesWithNumbers) {
                if (!isset($existingLookup[$next]) && !in_array($next, $familyNumbers, true)) {
                    $familyNumbers[] = $next;
                }
                $next++;
            }
        }

        return $familyNumbers;
    }

    // ---------------------------------------------------------------------------
    // run()
    // ---------------------------------------------------------------------------

    public function run(): void
    {
        $this->call(SchoolRangeSeeder::class);
        $this->createTestUsers();
        $this->createDeliveryTeams();
        $this->createFamiliesWithChildren();
        $this->createSampleRoutes();
        $this->seedWarehouseData();
    }

    private function createDeliveryTeams(): void
    {
        $teamColors = ['#dc2626', '#2563eb', '#16a34a', '#9333ea', '#f97316'];
        foreach ($this->deliveryTeams as $i => $name) {
            DeliveryTeam::firstOrCreate(
                ['name' => $name],
                ['color' => $teamColors[$i % count($teamColors)]]
            );
        }
        $this->command->info('Delivery teams created: ' . implode(', ', $this->deliveryTeams));
    }

    // ---------------------------------------------------------------------------
    // Test users
    // ---------------------------------------------------------------------------

    private function createTestUsers(): void
    {
        $santa = User::firstOrCreate(
            ['username' => 'santa_admin'],
            [
                'first_name' => 'Nick', 'last_name' => 'Claus',
                'email' => 'santa@gfsd.test', 'password' => 'password', 'permission' => 9,
                'position' => 'System Engineer', 'show_on_website' => true,
            ]
        );

        $advisor = User::firstOrCreate(
            ['username' => 'family_advisor'],
            [
                'first_name' => 'Mary', 'last_name' => 'Helper',
                'email' => 'advisor@gfsd.test', 'password' => 'password', 'permission' => 7,
                'school_source' => 'Mountain Way',
            ]
        );

        $coordinator = User::firstOrCreate(
            ['username' => 'coord_01'],
            [
                'first_name' => 'Pat', 'last_name' => 'Coordinator',
                'email' => 'coordinator@gfsd.test', 'password' => 'password', 'permission' => 8,
                'position' => 'Activities Coordinator', 'school_source' => 'GFHS', 'show_on_website' => true,
            ]
        );

        if (class_exists(\Spatie\Permission\Models\Role::class)) {
            if (method_exists($santa, 'syncRoles')) {
                $santa->syncRoles(['santa']);
                $advisor->syncRoles(['family']);
                $coordinator->syncRoles(['coordinator']);
            }
        }

        $driverNames = [
            ['username' => 'driver_alex', 'first' => 'Alex', 'last' => 'Driver'],
            ['username' => 'driver_jamie', 'first' => 'Jamie', 'last' => 'Driver'],
            ['username' => 'driver_morgan', 'first' => 'Morgan', 'last' => 'Driver'],
            ['username' => 'driver_taylor', 'first' => 'Taylor', 'last' => 'Driver'],
            ['username' => 'driver_riley', 'first' => 'Riley', 'last' => 'Driver'],
        ];

        foreach ($driverNames as $i => $d) {
            $driver = User::firstOrCreate(
                ['username' => $d['username']],
                [
                    'first_name' => $d['first'], 'last_name' => $d['last'],
                    'email' => strtolower($d['username']) . '@gfsd.test',
                    'password' => 'password',
                    'permission' => 8,
                ]
            );
            if (class_exists(\Spatie\Permission\Models\Role::class) && method_exists($driver, 'syncRoles')) {
                $driver->syncRoles(['coordinator']);
            }
        }

        // Additional users
        $extraUsers = [
            ['username' => 'santa_backup', 'first_name' => 'Holly', 'last_name' => 'Jolly', 'email' => 'santa2@gfsd.test', 'permission' => 9, 'role' => 'santa', 'position' => 'Business Operator'],
            ['username' => 'coord_02', 'first_name' => 'Jordan', 'last_name' => 'Bell', 'email' => 'coord2@gfsd.test', 'permission' => 8, 'role' => 'coordinator', 'position' => 'Giving Tree Coordinator', 'school_source' => 'GFMS'],
            ['username' => 'coord_03', 'first_name' => 'Casey', 'last_name' => 'Park', 'email' => 'coord3@gfsd.test', 'permission' => 8, 'role' => 'coordinator', 'position' => 'Food Manager', 'school_source' => 'Monte Cristo'],
            ['username' => 'advisor_02', 'first_name' => 'Lisa', 'last_name' => 'Nguyen', 'email' => 'advisor2@gfsd.test', 'permission' => 7, 'role' => 'family', 'school_source' => 'Crossroads'],
            ['username' => 'advisor_03', 'first_name' => 'Tom', 'last_name' => 'Garcia', 'email' => 'advisor3@gfsd.test', 'permission' => 7, 'role' => 'family', 'school_source' => 'GFHS'],
            ['username' => 'warehouse_01', 'first_name' => 'Sam', 'last_name' => 'Warehouse', 'email' => 'warehouse@gfsd.test', 'permission' => 8, 'role' => 'coordinator', 'position' => 'NINJA'],
        ];

        foreach ($extraUsers as $eu) {
            $user = User::firstOrCreate(
                ['username' => $eu['username']],
                array_filter([
                    'first_name' => $eu['first_name'], 'last_name' => $eu['last_name'],
                    'email' => $eu['email'], 'password' => 'password', 'permission' => $eu['permission'],
                    'position' => $eu['position'] ?? null, 'school_source' => $eu['school_source'] ?? null,
                ], fn($v) => $v !== null)
            );
            if (class_exists(\Spatie\Permission\Models\Role::class) && method_exists($user, 'syncRoles')) {
                $user->syncRoles([$eu['role']]);
            }
        }

        $this->command->info('Test users created (password: password for all): santa_admin, santa_backup, family_advisor, advisor_02, advisor_03, coord_01-03, warehouse_01, 5 drivers');
    }

    // ---------------------------------------------------------------------------
    // Families + children — 75 families, ~210 children
    // ---------------------------------------------------------------------------

    private function createFamiliesWithChildren(): void
    {
        $familyUser = User::where('username', 'family_advisor')->first();

        // School -> typical family number range (matches SchoolRangeSeeder defaults)
        // Crossroads: 1-99, GFHS: 100-199, GFMS: 200-299, Monte Cristo: 300-399, Mountain Way: 400-499, Special: 500-599

        // Map for deterministic school assignment based on age
        $schoolByAge = function (int $age): string {
            if ($age <= 2) return 'None/Other';
            if ($age <= 5) return $this->pick(['Mountain Way', 'Monte Cristo', 'None/Other']);
            if ($age <= 9) return $this->pick(['Mountain Way', 'Monte Cristo', 'Crossroads']);
            if ($age <= 12) return $this->pick(['GFMS', 'Crossroads', 'Monte Cristo']);
            if ($age <= 14) return $this->pick(['GFMS', 'GFHS']);
            return 'GFHS';
        };

        $clothesSizesByAge = [
            0 => '12M', 1 => '18M', 2 => '2T', 3 => '3T', 4 => '4T',
            5 => 'XS (4-5)', 6 => 'S (6-7)', 7 => 'S (6-7)', 8 => 'M (8-10)',
            9 => 'M (8-10)', 10 => 'L (10-12)', 11 => 'L (10-12)', 12 => 'L (10-12)',
            13 => 'S', 14 => 'M', 15 => 'M', 16 => 'L', 17 => 'L',
        ];

        // Generate 150 family definitions — all get numbers
        $totalFamilies = 150;
        $familyNumbers = $this->generateFamilyNumbers($totalFamilies);

        // Languages: ~70% English, ~25% Spanish, ~5% Other
        $languages = array_merge(
            array_fill(0, 105, 'English'),
            array_fill(0, 35, 'Spanish'),
            array_fill(0, 10, 'Other')
        );

        // Delivery status distribution
        $statuses = ['pending', 'pending', 'pending', 'pending', 'pending', 'in_transit', 'delivered'];

        // Gift level pattern: ~30% none(0), ~20% partial(1), ~20% moderate(2), ~30% full(3)
        $giftLevelPattern = [0, 0, 0, 1, 1, 2, 2, 3, 3, 3];

        $childCounter = 0;

        // Deterministic seed for reproducible "random" data
        mt_srand(42);

        for ($fIdx = 0; $fIdx < $totalFamilies; $fIdx++) {
            $lang = $languages[$fIdx % count($languages)];
            $isSpanish = $lang === 'Spanish';

            $lastName = $isSpanish
                ? $this->pickIndex($this->lastNamesSpanish, $fIdx)
                : $this->pickIndex($this->lastNamesEnglish, $fIdx);

            $addr = $this->realAddresses[$fIdx % count($this->realAddresses)];
            // Generate deterministic house number so each family has a unique address
            $houseNum = (($fIdx * 47 + 100) % 900) + 100;
            $fullAddress = "{$houseNum} {$addr['street']}, {$addr['city']}, WA {$addr['zip']}";
            // Jitter lat/lng so families at same base address don't stack on the map
            $latJitter = (($fIdx * 13) % 100 - 50) / 100000; // ±0.0005 degrees (~55m)
            $lngJitter = (($fIdx * 17) % 100 - 50) / 100000;
            $lat = $addr['lat'] + $latJitter;
            $lng = $addr['lng'] + $lngJitter;

            // Number of children: 1-5, avg ~2.8
            $childPattern = [2, 3, 2, 3, 4, 2, 3, 1, 3, 2, 4, 3, 2, 5, 3, 2, 3, 2, 4, 3];
            $numKids = $childPattern[$fIdx % count($childPattern)];

            $femaleAdults = mt_rand(0, 1) ? 1 : 2;
            $maleAdults = $femaleAdults === 2 ? 0 : mt_rand(0, 1);
            $numAdults = $femaleAdults + $maleAdults;

            // Decide delivery preference
            $deliveryPref = mt_rand(0, 4) === 0 ? 'Pickup' : 'Delivery';
            $seasonYear = (int) Setting::get('season_year', date('Y'));
            $deliveryDate = mt_rand(0, 1)
                ? Carbon::create($seasonYear, 12, 18)->toDateString()
                : Carbon::create($seasonYear, 12, 19)->toDateString();
            $needsDelivery = $deliveryPref === 'Delivery';

            // Some families have special needs
            $hasNeed = mt_rand(0, 3) === 0;
            $hasSevere = mt_rand(0, 7) === 0;
            $hasBaby = false;
            $hasPet = mt_rand(0, 4) === 0;

            $status = $this->pick($statuses);
            $teamName = $needsDelivery ? $this->pick($this->deliveryTeams) : null;
            $teamModel = $teamName ? DeliveryTeam::where('name', $teamName)->first() : null;
            $familyDone = mt_rand(0, 4) === 0;

            // Generate children ages
            $childrenDefs = [];
            $infants = 0; $youngChildren = 0; $childrenCount = 0; $tweens = 0; $teenagers = 0;

            for ($c = 0; $c < $numKids; $c++) {
                // Ages weighted toward 3-12 range
                $agePool = [0, 1, 2, 3, 4, 5, 5, 6, 6, 7, 7, 8, 8, 9, 9, 10, 10, 11, 12, 13, 14, 15, 16, 17];
                $age = $agePool[($fIdx * 7 + $c * 13) % count($agePool)];
                $genderPool = ['Male', 'Female', 'Male', 'Female', 'Male', 'Female', 'Male', 'Female', 'Male', 'Other'];
                $gender = $genderPool[($fIdx + $c) % count($genderPool)];
                $school = $schoolByAge($age);

                if ($age <= 1) { $infants++; $hasBaby = true; }
                elseif ($age <= 5) $youngChildren++;
                elseif ($age <= 9) $childrenCount++;
                elseif ($age <= 12) $tweens++;
                else $teenagers++;

                $childrenDefs[] = ['age' => $age, 'gender' => $gender, 'school' => $school];
            }

            $numChildren = count($childrenDefs);
            $numMembers = $numAdults + $numChildren;

            $familyData = [
                'user_id'                => $familyUser->id,
                'family_name'            => $lastName,
                'address'                => $fullAddress,
                'phone1'                 => '360-691-' . str_pad((string)(1000 + $fIdx * 37) % 10000, 4, '0', STR_PAD_LEFT),
                'phone2'                 => mt_rand(0, 2) === 0 ? '360-691-' . str_pad((string)(5000 + $fIdx * 23) % 10000, 4, '0', STR_PAD_LEFT) : null,
                'email'                  => mt_rand(0, 1) ? strtolower($lastName) . $fIdx . '@example.com' : null,
                'preferred_language'     => $lang,
                'female_adults'          => $femaleAdults,
                'male_adults'            => $maleAdults,
                'number_of_adults'       => $numAdults,
                'infants'                => $infants,
                'young_children'         => $youngChildren,
                'children_count'         => $childrenCount,
                'tweens'                 => $tweens,
                'teenagers'              => $teenagers,
                'number_of_children'     => $numChildren,
                'number_of_family_members' => $numMembers,
                'has_crhs_children'      => false,
                'has_gfhs_children'      => $teenagers > 0,
                'needs_baby_supplies'    => $hasBaby,
                'pet_information'        => $hasPet ? $this->pick($this->petInfos) : null,
                'delivery_preference'    => $deliveryPref,
                'delivery_date'          => $deliveryDate,
                'delivery_time'          => $needsDelivery ? $this->pick($this->deliveryTimes) : null,
                'delivery_reason'        => $needsDelivery ? $this->pick($this->deliveryReasons) : null,
                'delivery_team'          => $teamName,
                'delivery_team_id'       => $teamModel?->id,
                'delivery_status'        => $status,
                'need_for_help'          => $hasNeed ? $this->pick($this->needForHelpTexts) : null,
                'severe_need'            => $hasSevere ? $this->pick($this->severeNeedTexts) : null,
                'is_severe_need'         => $hasSevere,
                'family_done'            => $familyDone,
                'family_number'          => $familyNumbers[$fIdx],
                'latitude'               => $lat,
                'longitude'              => $lng,
            ];

            $family = Family::create($familyData);

            // Create children
            foreach ($childrenDefs as $c => $childDef) {
                $age = $childDef['age'];
                $gender = $childDef['gender'];
                $clothesSize = $clothesSizesByAge[$age] ?? 'M';

                $giftLevel = $giftLevelPattern[$childCounter % count($giftLevelPattern)];
                $isMerged = $childCounter % 5 < 2;
                $isAdopted = $childCounter % 8 === 0;
                $hasGifts = $giftLevel >= 2;
                $hasTag = $childCounter % 6 === 0;

                Child::create([
                    'family_id'            => $family->id,
                    'gender'               => $gender,
                    'age'                  => (string) $age,
                    'school'               => $childDef['school'],
                    'clothes_size'         => $clothesSize,
                    'clothing_styles'      => $this->pickIndex($this->clothingStyles, $childCounter),
                    'clothing_options'     => $this->pickIndex($this->clothingOptions, $childCounter),
                    'toy_ideas'            => $age >= 1 ? $this->pickIndex($this->toyIdeas, $childCounter) : 'Infant toys — rattles, soft blocks',
                    'gift_preferences'     => $this->pickIndex($this->giftPreferences, $childCounter),
                    'all_sizes'            => $this->pickIndex($this->allSizesOptions, $childCounter),
                    'mail_merged'          => $isMerged,
                    'gifts_received'       => $hasGifts ? $this->pickIndex($this->giftsReceivedTexts, $childCounter) : null,
                    'gift_level'           => $giftLevel,
                    'where_is_tag'         => $hasTag ? $this->pickIndex($this->whereIsTagValues, $childCounter) : null,
                    'adopter_name'         => $isAdopted ? $this->pickIndex($this->adopterNames, $childCounter) : null,
                    'adopter_email'        => $isAdopted ? strtolower(str_replace(' ', '', $this->pickIndex($this->adopterNames, $childCounter))) . '@example.com' : null,
                    'adopter_phone'        => $isAdopted && $childCounter % 3 === 0 ? '425-555-' . str_pad((string)(1000 + $childCounter), 4, '0', STR_PAD_LEFT) : null,
                    'adopter_contact_info' => $isAdopted ? '425-555-' . str_pad((string)(1000 + $childCounter), 4, '0', STR_PAD_LEFT) : null,
                ]);

                $childCounter++;
            }
        }

        // Reset mt_srand
        mt_srand();

        $totalChildren = Child::count();
        $this->command->info("Created {$totalFamilies} families with {$totalChildren} children (all families have numbers assigned).");
    }

    // ---------------------------------------------------------------------------
    // Delivery routes (seed a few to test driver view + delivery day)
    // ---------------------------------------------------------------------------

    private function createSampleRoutes(): void
    {
        $driverUsernames = ['driver_alex', 'driver_jamie', 'driver_morgan', 'driver_taylor', 'driver_riley'];
        $routePlanning = app(RoutePlanningService::class);

        $eligible = Family::whereNotNull('family_number')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereNull('delivery_route_id')
            ->where(function ($q) {
                $q->where('delivery_status', DeliveryStatus::Pending)
                    ->orWhereNull('delivery_status');
            })
            ->where(function ($q) {
                $q->where('delivery_preference', 'like', '%deliver%')
                    ->orWhereNull('delivery_preference');
            })
            ->orderBy('latitude') // geographic ordering for coherent routes
            ->get();

        if ($eligible->isEmpty()) {
            return;
        }

        $chunks = $eligible->chunk(9);
        foreach ($chunks as $i => $chunk) {
            $driverUsername = $driverUsernames[$i % count($driverUsernames)];
            $driverUser = User::where('username', $driverUsername)->first();
            $driverName = $driverUser ? "{$driverUser->first_name} {$driverUser->last_name}" : "Driver " . ($i + 1);
            $startLat = $chunk->first()->latitude;
            $startLng = $chunk->first()->longitude;

            $route = DeliveryRoute::create([
                'name' => $driverName,
                'driver_name' => $driverName,
                'driver_user_id' => $driverUser?->id,
                'start_lat' => $startLat,
                'start_lng' => $startLng,
                'stop_count' => $chunk->count(),
            ]);

            foreach ($chunk as $idx => $family) {
                $family->update([
                    'delivery_route_id' => $route->id,
                    'route_order' => $idx + 1,
                    'delivery_status' => DeliveryStatus::Pending,
                ]);
            }

            try {
                $routePlanning->optimizeRoute($route, (float) $startLat, (float) $startLng);
            } catch (\Exception $e) {
                // ORS may not be configured; continue without optimization
            }
        }

        $this->command->info('Created ' . $chunks->count() . ' delivery routes from ' . $eligible->count() . ' eligible families.');
    }

    // ---------------------------------------------------------------------------
    // Warehouse data: items, stock, transactions, shopping assignments
    // ---------------------------------------------------------------------------

    private function seedWarehouseData(): void
    {
        // Ensure warehouse categories exist
        $this->call(WarehouseCategorySeeder::class);

        $seasonYear = (int) Setting::get('season_year', date('Y'));
        $santaUser = User::where('username', 'santa_admin')->first();

        // Define warehouse items per category (keyed by category name from WarehouseCategorySeeder)
        $itemsPerCategory = [
            'Canned Goods' => [
                ['name' => 'Canned Corn (15oz)', 'barcode' => 'CAN-001'],
                ['name' => 'Canned Green Beans (14.5oz)', 'barcode' => 'CAN-002'],
                ['name' => 'Canned Tomato Sauce (8oz)', 'barcode' => 'CAN-003'],
                ['name' => 'Canned Chicken (12.5oz)', 'barcode' => 'CAN-004'],
                ['name' => 'Canned Pears (15oz)', 'barcode' => 'CAN-005'],
            ],
            'Pasta/Rice/Grains' => [
                ['name' => 'Spaghetti (16oz)', 'barcode' => 'PAS-001'],
                ['name' => 'Macaroni (16oz)', 'barcode' => 'PAS-002'],
                ['name' => 'White Rice (2lb)', 'barcode' => 'PAS-003'],
                ['name' => 'Instant Oatmeal (10pk)', 'barcode' => 'PAS-004'],
            ],
            'Breakfast Items' => [
                ['name' => 'Cheerios (12oz)', 'barcode' => 'BRK-001'],
                ['name' => 'Frosted Flakes (13.5oz)', 'barcode' => 'BRK-002'],
                ['name' => 'Pancake Mix (32oz)', 'barcode' => 'BRK-003'],
                ['name' => 'Maple Syrup (12oz)', 'barcode' => 'BRK-004'],
            ],
            'Hygiene Bundle' => [
                ['name' => 'Toothbrush 2-Pack', 'barcode' => 'HYG-001'],
                ['name' => 'Toothpaste (6oz)', 'barcode' => 'HYG-002'],
                ['name' => 'Bar Soap (3-Pack)', 'barcode' => 'HYG-003'],
                ['name' => 'Shampoo (12oz)', 'barcode' => 'HYG-004'],
                ['name' => 'Deodorant', 'barcode' => 'HYG-005'],
            ],
            'Dairy/Refrigerated' => [
                ['name' => 'Whole Milk (1 gal)', 'barcode' => 'DAI-001'],
                ['name' => 'Butter (1lb)', 'barcode' => 'DAI-002'],
                ['name' => 'Cheddar Cheese Block (8oz)', 'barcode' => 'DAI-003'],
            ],
            'Diapers' => [
                ['name' => 'Diapers Size 3 (27ct)', 'barcode' => 'DIAP-001'],
                ['name' => 'Diapers Size 4 (22ct)', 'barcode' => 'DIAP-002'],
                ['name' => 'Diapers Size 5 (19ct)', 'barcode' => 'DIAP-003'],
            ],
            'Formula' => [
                ['name' => 'Infant Formula (12.5oz)', 'barcode' => 'FORM-001'],
                ['name' => 'Toddler Formula (20oz)', 'barcode' => 'FORM-002'],
            ],
            'General Gifts' => [
                ['name' => 'Coloring Book & Crayon Set', 'barcode' => 'GEN-001'],
                ['name' => 'Board Game - Family', 'barcode' => 'GEN-002'],
                ['name' => 'Stuffed Animal (Plush Bear)', 'barcode' => 'GEN-003'],
                ['name' => 'LEGO Classic Set', 'barcode' => 'GEN-004'],
                ['name' => 'Kids Book Bundle (3pk)', 'barcode' => 'GEN-005'],
            ],
            'Condiments/Sauces' => [
                ['name' => 'Ketchup (20oz)', 'barcode' => 'COND-001'],
                ['name' => 'Peanut Butter (16oz)', 'barcode' => 'COND-002'],
                ['name' => 'Grape Jelly (18oz)', 'barcode' => 'COND-003'],
            ],
            'Snacks' => [
                ['name' => 'Goldfish Crackers (6.6oz)', 'barcode' => 'SNK-001'],
                ['name' => 'Granola Bars (6ct)', 'barcode' => 'SNK-002'],
                ['name' => 'Fruit Snacks (10ct)', 'barcode' => 'SNK-003'],
                ['name' => 'Pretzels (16oz)', 'barcode' => 'SNK-004'],
            ],
            'Personal Care' => [
                ['name' => 'Tissues (160ct)', 'barcode' => 'CARE-001'],
                ['name' => 'Hand Soap (7.5oz)', 'barcode' => 'CARE-002'],
                ['name' => 'Band-Aids (20ct)', 'barcode' => 'CARE-003'],
            ],
        ];

        $zones = ['A', 'B', 'C', 'D'];
        $shelves = ['1', '2', '3'];
        $bins = ['L', 'M', 'R'];

        $createdItems = [];
        $itemCounter = 0;

        foreach ($itemsPerCategory as $categoryName => $items) {
            $category = WarehouseCategory::where('name', $categoryName)->first();
            if (!$category) {
                continue;
            }

            foreach ($items as $itemDef) {
                $warehouseItem = WarehouseItem::firstOrCreate(
                    ['barcode' => $itemDef['barcode']],
                    [
                        'category_id' => $category->id,
                        'name' => $itemDef['name'],
                        'barcode' => $itemDef['barcode'],
                        'is_generic' => false,
                        'active' => true,
                        'location_zone' => $zones[$itemCounter % count($zones)],
                        'location_shelf' => $shelves[$itemCounter % count($shelves)],
                        'location_bin' => $bins[$itemCounter % count($bins)],
                    ]
                );

                $createdItems[] = ['item' => $warehouseItem, 'category' => $category];
                $itemCounter++;
            }
        }

        $this->command->info("Created {$itemCounter} warehouse items across " . count($itemsPerCategory) . " categories.");

        // Create warehouse transactions (receipts and some distributions)
        $txnCount = 0;
        $sources = ['Costco Donation', 'Community Drive', 'Walmart Partnership', 'Individual Donor', 'Fred Meyer'];
        $donorNames = ['Boeing Employees', 'Granite Falls Rotary', 'Mountain View PTA', 'Local Business Alliance', null];

        foreach ($createdItems as $idx => $entry) {
            $item = $entry['item'];
            $category = $entry['category'];

            // Receipt transaction: 10-50 items
            $receiptQty = 10 + ($idx * 7) % 41;
            WarehouseTransaction::create([
                'season_year' => $seasonYear,
                'item_id' => $item->id,
                'category_id' => $category->id,
                'transaction_type' => TransactionType::In,
                'quantity' => $receiptQty,
                'source' => $sources[$idx % count($sources)],
                'donor_name' => $donorNames[$idx % count($donorNames)],
                'notes' => 'Initial stock receipt',
                'scanned_by' => $santaUser?->id,
                'scanned_at' => now()->subDays(rand(1, 14)),
            ]);
            $txnCount++;

            // Some items get a second receipt
            if ($idx % 3 === 0) {
                $addlQty = 5 + ($idx * 3) % 20;
                WarehouseTransaction::create([
                    'season_year' => $seasonYear,
                    'item_id' => $item->id,
                    'category_id' => $category->id,
                    'transaction_type' => TransactionType::In,
                    'quantity' => $addlQty,
                    'source' => 'Additional Donation',
                    'notes' => 'Second batch received',
                    'scanned_by' => $santaUser?->id,
                    'scanned_at' => now()->subDays(rand(1, 7)),
                ]);
                $txnCount++;
            }

            // Distribution transactions for ~40% of items
            if ($idx % 5 < 2) {
                $distQty = 2 + ($idx * 5) % 8;
                $family = Family::whereNotNull('family_number')->inRandomOrder()->first();
                WarehouseTransaction::create([
                    'season_year' => $seasonYear,
                    'item_id' => $item->id,
                    'category_id' => $category->id,
                    'family_id' => $family?->id,
                    'transaction_type' => TransactionType::Out,
                    'quantity' => $distQty,
                    'notes' => 'Distributed to family',
                    'scanned_by' => $santaUser?->id,
                    'scanned_at' => now()->subDays(rand(0, 5)),
                ]);
                $txnCount++;
            }
        }

        $this->command->info("Created {$txnCount} warehouse transactions (receipts & distributions).");

        // Create shopping assignments with different split types
        $families = Family::whereNotNull('family_number')->orderBy('family_number')->get();
        $maxFamily = $families->max('family_number') ?? 100;

        $assignmentDefs = [
            [
                'ninja_name' => 'Team Alpha',
                'split_type' => 'family_range',
                'family_start' => 1,
                'family_end' => (int) ceil($maxFamily / 3),
            ],
            [
                'ninja_name' => 'Team Beta',
                'split_type' => 'family_range',
                'family_start' => (int) ceil($maxFamily / 3) + 1,
                'family_end' => (int) ceil($maxFamily * 2 / 3),
            ],
            [
                'ninja_name' => 'Category Shopper',
                'split_type' => 'category',
                'categories' => ['canned', 'dry'],
            ],
            [
                'ninja_name' => 'Deficit Buyer',
                'split_type' => 'deficit',
            ],
        ];

        // Add a subcategory assignment if grocery items exist
        $sampleGroceryCategory = GroceryItem::select('category')->distinct()->first();
        if ($sampleGroceryCategory) {
            $sampleItems = GroceryItem::where('category', $sampleGroceryCategory->category)
                ->limit(3)
                ->pluck('id')
                ->toArray();

            if (!empty($sampleItems)) {
                $assignmentDefs[] = [
                    'ninja_name' => 'Subcategory Specialist',
                    'split_type' => 'subcategory',
                    'config' => [
                        'category_name' => $sampleGroceryCategory->category,
                        'item_ids' => $sampleItems,
                    ],
                ];
            }
        }

        foreach ($assignmentDefs as $def) {
            ShoppingAssignment::create(array_merge([
                'notes' => 'Seeded test assignment',
            ], $def));
        }

        $this->command->info('Created ' . count($assignmentDefs) . ' shopping assignments with various split types.');
    }
}
