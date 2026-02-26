<?php

namespace Database\Seeders;

use App\Models\Child;
use App\Models\Family;
use App\Models\User;
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

    // ---------------------------------------------------------------------------
    // run()
    // ---------------------------------------------------------------------------

    public function run(): void
    {
        $this->call(SchoolRangeSeeder::class);
        $this->createTestUsers();
        $this->createFamiliesWithChildren();
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
            ]
        );

        $advisor = User::firstOrCreate(
            ['username' => 'family_advisor'],
            [
                'first_name' => 'Mary', 'last_name' => 'Helper',
                'email' => 'advisor@gfsd.test', 'password' => 'password', 'permission' => 7,
            ]
        );

        $coordinator = User::firstOrCreate(
            ['username' => 'coord_01'],
            [
                'first_name' => 'Pat', 'last_name' => 'Coordinator',
                'email' => 'coordinator@gfsd.test', 'password' => 'password', 'permission' => 8,
            ]
        );

        if (class_exists(\Spatie\Permission\Models\Role::class)) {
            if (method_exists($santa, 'syncRoles')) {
                $santa->syncRoles(['santa']);
                $advisor->syncRoles(['family']);
                $coordinator->syncRoles(['coordinator']);
            }
        }

        $this->command->info('Test users created: santa_admin / family_advisor / coord_01 (password: password)');
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

        // Generate 75 family definitions
        $totalFamilies = 75;
        $familiesWithNumbers = 50; // first 50 get numbers, last 25 are unassigned

        // Pre-assign family numbers to the first 50 families, spread across school ranges
        // Crossroads(1-99): 8 families, GFHS(100-199): 8, GFMS(200-299): 9, Monte Cristo(300-399): 9, Mountain Way(400-499): 10, Special(500-599): 6
        $familyNumbers = [];
        $rangeAssignments = [
            ['start' => 1, 'end' => 99, 'count' => 8],     // Crossroads
            ['start' => 100, 'end' => 199, 'count' => 8],   // GFHS
            ['start' => 200, 'end' => 299, 'count' => 9],   // GFMS
            ['start' => 300, 'end' => 399, 'count' => 9],   // Monte Cristo
            ['start' => 400, 'end' => 499, 'count' => 10],  // Mountain Way
            ['start' => 500, 'end' => 599, 'count' => 6],   // Special Case
        ];

        foreach ($rangeAssignments as $ra) {
            $step = max(1, intdiv($ra['end'] - $ra['start'], $ra['count'] + 1));
            for ($i = 0; $i < $ra['count']; $i++) {
                $familyNumbers[] = $ra['start'] + $step * ($i + 1);
            }
        }

        // Languages: ~70% English, ~25% Spanish, ~5% Other
        $languages = array_merge(
            array_fill(0, 52, 'English'),
            array_fill(0, 19, 'Spanish'),
            array_fill(0, 4, 'Other')
        );

        // Delivery status distribution
        $statuses = ['pending', 'pending', 'pending', 'delivered', 'delivered', 'in_transit', 'picked_up'];

        // Gift level pattern: ~30% none(0), ~20% partial(1), ~20% moderate(2), ~30% full(3)
        $giftLevelPattern = [0, 0, 0, 1, 1, 2, 2, 3, 3, 3];

        $childCounter = 0;

        // Deterministic seed for reproducible "random" data
        mt_srand(42);

        for ($fIdx = 0; $fIdx < $totalFamilies; $fIdx++) {
            $lang = $languages[$fIdx];
            $isSpanish = $lang === 'Spanish';

            $lastName = $isSpanish
                ? $this->pickIndex($this->lastNamesSpanish, $fIdx)
                : $this->pickIndex($this->lastNamesEnglish, $fIdx);

            $addr = $this->realAddresses[$fIdx % count($this->realAddresses)];
            $houseNum = 100 + ($fIdx * 47) % 2900; // spread house numbers
            $fullAddress = "{$houseNum} {$addr['street']}, {$addr['city']}, WA {$addr['zip']}";

            // Number of children: 1-5, avg ~2.8
            $childPattern = [2, 3, 2, 3, 4, 2, 3, 1, 3, 2, 4, 3, 2, 5, 3, 2, 3, 2, 4, 3];
            $numKids = $childPattern[$fIdx % count($childPattern)];

            $femaleAdults = mt_rand(0, 1) ? 1 : 2;
            $maleAdults = $femaleAdults === 2 ? 0 : mt_rand(0, 1);
            $numAdults = $femaleAdults + $maleAdults;

            // Decide delivery preference
            $hasNumber = $fIdx < $familiesWithNumbers;
            $deliveryPref = mt_rand(0, 2) === 0 ? 'Pickup' : 'Delivery';
            $deliveryDate = mt_rand(0, 1) ? 'December 18' : 'December 19';
            $needsDelivery = $deliveryPref === 'Delivery';

            // Some families have special needs
            $hasNeed = mt_rand(0, 3) === 0;
            $hasSevere = mt_rand(0, 7) === 0;
            $hasBaby = false;
            $hasPet = mt_rand(0, 4) === 0;

            $status = $hasNumber ? $this->pick($statuses) : null;
            $team = ($hasNumber && $needsDelivery) ? $this->pick($this->deliveryTeams) : null;
            $familyDone = $hasNumber && mt_rand(0, 4) === 0;

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
                'delivery_preference'    => $hasNumber ? $deliveryPref : null,
                'delivery_date'          => $hasNumber ? $deliveryDate : null,
                'delivery_time'          => ($hasNumber && $needsDelivery) ? $this->pick($this->deliveryTimes) : null,
                'delivery_reason'        => $needsDelivery ? $this->pick($this->deliveryReasons) : null,
                'delivery_team'          => $team,
                'delivery_status'        => $status,
                'need_for_help'          => $hasNeed ? $this->pick($this->needForHelpTexts) : null,
                'severe_need'            => $hasSevere ? $this->pick($this->severeNeedTexts) : null,
                'family_done'            => $familyDone,
                'family_number'          => $hasNumber ? $familyNumbers[$fIdx] : null,
                'latitude'               => $addr['lat'] + (($fIdx * 17 % 100) - 50) * 0.0001,
                'longitude'              => $addr['lng'] + (($fIdx * 31 % 100) - 50) * 0.0001,
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
        $this->command->info("Created {$totalFamilies} families with {$totalChildren} children.");
        $this->command->info("{$familiesWithNumbers} families have family numbers assigned, " . ($totalFamilies - $familiesWithNumbers) . " are unnumbered (ready for assignment).");
    }
}
