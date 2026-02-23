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
    ];

    private array $lastNamesSpanish = [
        'Hernandez', 'Lopez', 'Martinez', 'Gonzalez', 'Rodriguez', 'Perez', 'Sanchez',
        'Ramirez', 'Torres', 'Flores', 'Rivera', 'Gomez', 'Diaz', 'Cruz', 'Morales',
    ];

    private array $childFirstNamesM = [
        'Liam', 'Noah', 'Oliver', 'Elijah', 'James', 'Aiden', 'Lucas', 'Mason',
        'Ethan', 'Logan', 'Jackson', 'Sebastian', 'Mateo', 'Jack', 'Owen',
        'Theodore', 'Asher', 'Henry', 'Leo', 'Julian', 'Wyatt', 'Christopher',
        'Sebastián', 'Diego', 'Andres', 'Marco', 'Gabriel',
    ];

    private array $childFirstNamesF = [
        'Olivia', 'Emma', 'Ava', 'Charlotte', 'Sophia', 'Amelia', 'Isabella', 'Mia',
        'Evelyn', 'Harper', 'Luna', 'Camila', 'Gianna', 'Elizabeth', 'Eleanor',
        'Ella', 'Abigail', 'Sofia', 'Avery', 'Scarlett', 'Emily', 'Aria', 'Penelope',
        'Chloe', 'Layla', 'Valentina', 'Lucia', 'Catalina',
    ];

    // ---------------------------------------------------------------------------
    // Address data pools
    // ---------------------------------------------------------------------------

    private array $streetNames = [
        'Maple Ave', 'Oak Street', 'Cedar Lane', 'Pine Drive', 'Elm Court',
        'Birch Way', 'Willow Road', 'Spruce Circle', 'Aspen Blvd', 'Fir Street',
        'Mountain View Dr', 'Valley Road', 'Hillside Ave', 'Riverside Dr', 'Lakeview Ct',
        'Clearwater Lane', 'Sunridge Way', 'Forest Grove Rd', 'Meadow Creek Dr', 'Rolling Hills Blvd',
        'Granite Falls Rd', 'Cascade Ave', 'Evergreen Way', 'Summit Drive', 'Canyon Road',
    ];

    private array $cities = [
        'Granite Falls', 'Granite Falls', 'Granite Falls', 'Granite Falls', // weighted toward local
        'Lake Stevens', 'Snohomish', 'Monroe',
    ];

    // ---------------------------------------------------------------------------
    // Delivery data pools
    // ---------------------------------------------------------------------------

    private array $deliveryTimes = [
        '9:00 AM - 11:00 AM',
        '11:00 AM - 1:00 PM',
        '1:00 PM - 3:00 PM',
        '3:00 PM - 5:00 PM',
        'Morning',
        'Afternoon',
        'Anytime',
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
        'Large dog, please knock loudly',
        '2 cats, indoor only',
        'Friendly dog, will bark',
        'Dog on chain in backyard',
        'No pets',
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

    // ---------------------------------------------------------------------------
    // Child data pools
    // ---------------------------------------------------------------------------

    private array $schools = [
        'Mountain Way', 'Mountain Way', 'Mountain Way', // weighted
        'Monte Cristo', 'Monte Cristo',
        'GFMS', 'GFMS',
        'GFHS', 'GFHS',
        'Crossroads',
        'None/Other',
    ];

    private array $clothesSizes = [
        // Toddler/infant
        '12M', '18M', '24M', '2T', '3T', '4T',
        // Children
        'XS (4-5)', 'S (6-7)', 'M (8-10)', 'L (10-12)',
        // Teen / adult sizes
        'XS', 'S', 'M', 'L', 'XL',
        // Numeric
        '4', '5', '6', '7', '8', '10', '12', '14', '16',
    ];

    private array $clothingStyles = [
        'Casual, comfortable clothes',
        'Loves bright colors',
        'Prefers sporty / athletic wear',
        'Likes hoodies and sweatpants',
        'Into Minecraft / gaming themes',
        'Loves dinosaurs and animals',
        'Prefers dresses and skirts',
        'Likes anything Disney',
        'Into superheroes (Marvel/DC)',
        'Prefers neutral colors, nothing too flashy',
        'Loves LOL Dolls and Barbie themes',
        'Into outdoor / camping style',
    ];

    private array $clothingOptions = [
        'Shirts, pants, socks',
        'Pants and shirts only — has enough socks',
        'Coat / jacket needed most',
        'Shoes (size 6Y), pants, shirts',
        'Underwear and socks most needed',
        'Winter coat, boots, warm layers',
        'Anything warm — very limited wardrobe',
        'PJs, shirts, pants',
        'Jeans and hoodies',
    ];

    private array $toyIdeas = [
        'LEGO sets, anything Minecraft',
        'Barbies, art supplies, craft kits',
        'Hot Wheels, remote control cars',
        'Board games, puzzles',
        'Stuffed animals, pretend play kitchen',
        'Sports equipment (basketball, soccer ball)',
        'Books, educational toys',
        'Slime kits, science experiments',
        'Baby toys — stacking rings, soft blocks',
        'LOL Surprise dolls, nail polish sets',
        'Nerf guns, outdoor toys',
        'Video games (Nintendo Switch)',
        'Musical instruments — small keyboard, ukulele',
        'Art supplies — markers, sketchbooks',
        'Anything Pokemon or Star Wars',
    ];

    private array $giftPreferences = [
        'No preference, anything appreciated',
        'Prefer practical gifts (clothing, books)',
        'Loves hands-on activities and crafts',
        'Prefers toys over clothing',
        'Clothing preferred over toys',
        'Books and educational materials appreciated',
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
        'The Henderson Family',
        'Grace Community Church',
        'Boeing Employees Group',
        'Granite Falls Rotary',
        'The Kowalski Family',
        'Mountain View PTA',
        'Snohomish County Teachers',
        'The Nakamura Family',
        'VFW Post 3617',
        'Frontier Bank Staff',
    ];

    private array $giftsReceivedTexts = [
        'Received coat, 2 shirts, pants, shoes',
        'LEGO City set, book, shirt',
        'Barbie Dream House, 2 outfits',
        'Basketball, shoes (size 7), 3 shirts',
        'Books, art kit, warm pajamas',
        'Remote control car, pants, 2 shirts, socks',
        'Stuffed animals, dress, shoes',
        'Board games, hoodie, jeans',
    ];

    private array $whereIsTagValues = ['at store', 'with volunteer', 'returned'];

    // ---------------------------------------------------------------------------
    // Helper: pick a random element from an array
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
        // Ensure school ranges exist before we do anything else
        $this->call(SchoolRangeSeeder::class);

        $this->createTestUsers();
        $this->createFamiliesWithChildren();
    }

    // ---------------------------------------------------------------------------
    // Test users
    // ---------------------------------------------------------------------------

    private function createTestUsers(): void
    {
        // Santa (admin) — permission 9
        $santa = User::firstOrCreate(
            ['username' => 'santa_admin'],
            [
                'first_name' => 'Nick',
                'last_name'  => 'Claus',
                'email'      => 'santa@gfsd.test',
                'password'   => 'password',
                'permission' => 9,
            ]
        );

        // Family advisor — permission 7
        $advisor = User::firstOrCreate(
            ['username' => 'family_advisor'],
            [
                'first_name' => 'Mary',
                'last_name'  => 'Helper',
                'email'      => 'advisor@gfsd.test',
                'password'   => 'password',
                'permission' => 7,
            ]
        );

        // Coordinator — permission 8
        $coordinator = User::firstOrCreate(
            ['username' => 'coord_01'],
            [
                'first_name' => 'Pat',
                'last_name'  => 'Coordinator',
                'email'      => 'coordinator@gfsd.test',
                'password'   => 'password',
                'permission' => 8,
            ]
        );

        // Assign Spatie roles if available
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
    // Families + children
    // ---------------------------------------------------------------------------

    private function createFamiliesWithChildren(): void
    {
        $familyUser = User::where('username', 'family_advisor')->first();

        // -----------------------------------------------------------------------
        // Family definitions
        // Each entry: [language, delivery_pref, date, adults_f, adults_m, children_data[], options]
        // options keys: family_number, delivery_reason, pet_info, severe_need, need_for_help,
        //               needs_baby_supplies, has_crhs_children, has_gfhs_children,
        //               delivery_status, delivery_team, family_done
        // -----------------------------------------------------------------------

        $familyDefs = [
            // --- 15 families WITH family numbers ---

            // 0: Crossroads range (1-99)
            [
                'last_name'   => 'Williams',
                'language'    => 'English',
                'address_num' => '412',
                'street'      => 'Maple Ave',
                'city'        => 'Granite Falls',
                'phone1'      => '360-691-4201',
                'phone2'      => '360-691-4202',
                'email'       => 'williams@example.com',
                'adults_f'    => 1, 'adults_m' => 1,
                'children'    => [
                    ['gender' => 'M', 'age' => '8',  'school' => 'Crossroads'],
                    ['gender' => 'F', 'age' => '11', 'school' => 'Crossroads'],
                    ['gender' => 'M', 'age' => '5',  'school' => 'Mountain Way'],
                ],
                'opts' => [
                    'family_number'   => 14,
                    'delivery_status' => 'delivered',
                    'delivery_team'   => 'Team A',
                    'family_done'     => true,
                    'has_crhs_children' => false,
                ],
            ],

            // 1: Crossroads range
            [
                'last_name'   => 'Hernandez',
                'language'    => 'Spanish',
                'address_num' => '218',
                'street'      => 'Oak Street',
                'city'        => 'Granite Falls',
                'phone1'      => '360-691-5510',
                'phone2'      => null,
                'email'       => null,
                'adults_f'    => 1, 'adults_m' => 0,
                'children'    => [
                    ['gender' => 'F', 'age' => '3',  'school' => 'None/Other'],
                    ['gender' => 'M', 'age' => '6',  'school' => 'Mountain Way'],
                    ['gender' => 'F', 'age' => '10', 'school' => 'Crossroads'],
                ],
                'opts' => [
                    'family_number'      => 27,
                    'needs_baby_supplies'=> true,
                    'need_for_help'      => 'Single mother of three, lost job in October',
                    'delivery_preference'=> 'Delivery',
                    'delivery_date'      => 'December 18',
                    'delivery_time'      => '11:00 AM - 1:00 PM',
                    'delivery_reason'    => 'No transportation',
                    'delivery_status'    => 'delivered',
                    'delivery_team'      => 'Team B',
                    'family_done'        => true,
                ],
            ],

            // 2: Crossroads range
            [
                'last_name'   => 'Brown',
                'language'    => 'English',
                'address_num' => '804',
                'street'      => 'Cedar Lane',
                'city'        => 'Granite Falls',
                'phone1'      => '360-691-7734',
                'phone2'      => '206-555-0138',
                'email'       => 'jbrown@example.com',
                'adults_f'    => 2, 'adults_m' => 0,
                'children'    => [
                    ['gender' => 'M', 'age' => '7', 'school' => 'Mountain Way'],
                    ['gender' => 'F', 'age' => '9', 'school' => 'Crossroads'],
                ],
                'opts' => [
                    'family_number'   => 52,
                    'delivery_status' => 'pending',
                    'pet_information' => 'Friendly dog, will bark',
                ],
            ],

            // 3: High School range (100-199)
            [
                'last_name'   => 'Johnson',
                'language'    => 'English',
                'address_num' => '1103',
                'street'      => 'Pine Drive',
                'city'        => 'Granite Falls',
                'phone1'      => '360-691-2290',
                'phone2'      => null,
                'email'       => 'tjohnson@example.com',
                'adults_f'    => 1, 'adults_m' => 1,
                'children'    => [
                    ['gender' => 'F', 'age' => '15', 'school' => 'GFHS'],
                    ['gender' => 'M', 'age' => '17', 'school' => 'GFHS'],
                ],
                'opts' => [
                    'family_number'   => 108,
                    'has_gfhs_children' => true,
                    'delivery_preference' => 'Pickup',
                    'delivery_date'   => 'December 19',
                    'delivery_time'   => '10:00 AM - 12:00 PM',
                    'delivery_status' => 'picked_up',
                    'family_done'     => true,
                ],
            ],

            // 4: High School range
            [
                'last_name'   => 'Martinez',
                'language'    => 'Spanish',
                'address_num' => '320',
                'street'      => 'Elm Court',
                'city'        => 'Granite Falls',
                'phone1'      => '360-691-8847',
                'phone2'      => '360-691-8848',
                'email'       => null,
                'adults_f'    => 1, 'adults_m' => 1,
                'children'    => [
                    ['gender' => 'M', 'age' => '14', 'school' => 'GFHS'],
                    ['gender' => 'F', 'age' => '12', 'school' => 'GFMS'],
                    ['gender' => 'M', 'age' => '9',  'school' => 'Crossroads'],
                    ['gender' => 'F', 'age' => '6',  'school' => 'Mountain Way'],
                ],
                'opts' => [
                    'family_number'   => 137,
                    'has_gfhs_children' => true,
                    'severe_need'     => 'Father in hospital, no income coming in',
                    'need_for_help'   => 'Father disabled, mother is sole provider',
                    'delivery_preference' => 'Delivery',
                    'delivery_date'   => 'December 18',
                    'delivery_time'   => '1:00 PM - 3:00 PM',
                    'delivery_reason' => 'Single parent, cannot leave children',
                    'delivery_status' => 'in_transit',
                    'delivery_team'   => 'Team C',
                ],
            ],

            // 5: High School range
            [
                'last_name'   => 'Thompson',
                'language'    => 'English',
                'address_num' => '567',
                'street'      => 'Birch Way',
                'city'        => 'Lake Stevens',
                'phone1'      => '425-334-9901',
                'phone2'      => null,
                'email'       => 'sthompson@example.com',
                'adults_f'    => 1, 'adults_m' => 1,
                'children'    => [
                    ['gender' => 'M', 'age' => '16', 'school' => 'GFHS'],
                    ['gender' => 'F', 'age' => '13', 'school' => 'GFMS'],
                ],
                'opts' => [
                    'family_number'   => 162,
                    'has_gfhs_children' => true,
                    'delivery_preference' => 'Pickup',
                    'delivery_date'   => 'December 18',
                    'delivery_status' => 'pending',
                ],
            ],

            // 6: Middle School range (200-299)
            [
                'last_name'   => 'Garcia',
                'language'    => 'Spanish',
                'address_num' => '774',
                'street'      => 'Willow Road',
                'city'        => 'Granite Falls',
                'phone1'      => '360-691-3357',
                'phone2'      => null,
                'email'       => null,
                'adults_f'    => 1, 'adults_m' => 0,
                'children'    => [
                    ['gender' => 'F', 'age' => '11', 'school' => 'GFMS'],
                    ['gender' => 'M', 'age' => '8',  'school' => 'Crossroads'],
                    ['gender' => 'F', 'age' => '2',  'school' => 'None/Other'],
                ],
                'opts' => [
                    'family_number'      => 211,
                    'needs_baby_supplies'=> true,
                    'need_for_help'      => 'Recently laid off, father working two part-time jobs',
                    'severe_need'        => 'Facing eviction next month, utilities shut off',
                    'delivery_preference'=> 'Delivery',
                    'delivery_date'      => 'December 18',
                    'delivery_time'      => '9:00 AM - 11:00 AM',
                    'delivery_reason'    => 'No transportation',
                    'delivery_status'    => 'delivered',
                    'delivery_team'      => 'Team D',
                    'family_done'        => true,
                ],
            ],

            // 7: Middle School range
            [
                'last_name'   => 'Davis',
                'language'    => 'English',
                'address_num' => '1520',
                'street'      => 'Spruce Circle',
                'city'        => 'Granite Falls',
                'phone1'      => '360-691-4490',
                'phone2'      => '360-691-4491',
                'email'       => 'rdavis@example.com',
                'adults_f'    => 1, 'adults_m' => 1,
                'children'    => [
                    ['gender' => 'M', 'age' => '13', 'school' => 'GFMS'],
                    ['gender' => 'F', 'age' => '10', 'school' => 'Crossroads'],
                    ['gender' => 'M', 'age' => '7',  'school' => 'Mountain Way'],
                    ['gender' => 'F', 'age' => '4',  'school' => 'Mountain Way'],
                ],
                'opts' => [
                    'family_number'   => 248,
                    'delivery_status' => 'pending',
                    'pet_information' => 'Large dog, please knock loudly',
                ],
            ],

            // 8: Middle School range
            [
                'last_name'   => 'Lopez',
                'language'    => 'Spanish',
                'address_num' => '98',
                'street'      => 'Aspen Blvd',
                'city'        => 'Granite Falls',
                'phone1'      => '360-691-6612',
                'phone2'      => null,
                'email'       => null,
                'adults_f'    => 1, 'adults_m' => 1,
                'children'    => [
                    ['gender' => 'M', 'age' => '12', 'school' => 'GFMS'],
                    ['gender' => 'F', 'age' => '9',  'school' => 'Crossroads'],
                    ['gender' => 'M', 'age' => '5',  'school' => 'Mountain Way'],
                ],
                'opts' => [
                    'family_number'   => 275,
                    'delivery_preference' => 'Delivery',
                    'delivery_date'   => 'December 19',
                    'delivery_time'   => '3:00 PM - 5:00 PM',
                    'delivery_reason' => 'Works during pickup hours',
                    'delivery_status' => 'in_transit',
                    'delivery_team'   => 'Team A',
                ],
            ],

            // 9: Monte Cristo range (300-399)
            [
                'last_name'   => 'Wilson',
                'language'    => 'English',
                'address_num' => '2211',
                'street'      => 'Fir Street',
                'city'        => 'Granite Falls',
                'phone1'      => '360-691-8801',
                'phone2'      => '360-691-8802',
                'email'       => 'kwilson@example.com',
                'adults_f'    => 1, 'adults_m' => 1,
                'children'    => [
                    ['gender' => 'F', 'age' => '6',  'school' => 'Monte Cristo'],
                    ['gender' => 'M', 'age' => '8',  'school' => 'Monte Cristo'],
                    ['gender' => 'F', 'age' => '14', 'school' => 'GFHS'],
                ],
                'opts' => [
                    'family_number'   => 312,
                    'has_gfhs_children' => true,
                    'delivery_preference' => 'Pickup',
                    'delivery_date'   => 'December 19',
                    'delivery_status' => 'picked_up',
                    'family_done'     => true,
                ],
            ],

            // 10: Monte Cristo range
            [
                'last_name'   => 'Anderson',
                'language'    => 'Other',
                'address_num' => '445',
                'street'      => 'Mountain View Dr',
                'city'        => 'Granite Falls',
                'phone1'      => '360-691-3345',
                'phone2'      => null,
                'email'       => null,
                'adults_f'    => 1, 'adults_m' => 0,
                'children'    => [
                    ['gender' => 'M', 'age' => '4',  'school' => 'None/Other'],
                    ['gender' => 'F', 'age' => '7',  'school' => 'Monte Cristo'],
                    ['gender' => 'M', 'age' => '1',  'school' => 'None/Other'],
                ],
                'opts' => [
                    'family_number'      => 358,
                    'needs_baby_supplies'=> true,
                    'need_for_help'      => 'Both parents working minimum wage, barely covering rent',
                    'delivery_preference'=> 'Delivery',
                    'delivery_date'      => 'December 18',
                    'delivery_time'      => '1:00 PM - 3:00 PM',
                    'delivery_reason'    => 'No transportation',
                    'delivery_status'    => 'pending',
                ],
            ],

            // 11: Monte Cristo range
            [
                'last_name'   => 'Taylor',
                'language'    => 'English',
                'address_num' => '678',
                'street'      => 'Valley Road',
                'city'        => 'Granite Falls',
                'phone1'      => '360-691-2276',
                'phone2'      => '360-691-2277',
                'email'       => 'btaylor@example.com',
                'adults_f'    => 1, 'adults_m' => 1,
                'children'    => [
                    ['gender' => 'F', 'age' => '9',  'school' => 'Monte Cristo'],
                    ['gender' => 'M', 'age' => '11', 'school' => 'Monte Cristo'],
                ],
                'opts' => [
                    'family_number'   => 387,
                    'delivery_status' => 'pending',
                ],
            ],

            // 12: Mountain Way range (400-499)
            [
                'last_name'   => 'White',
                'language'    => 'English',
                'address_num' => '1334',
                'street'      => 'Hillside Ave',
                'city'        => 'Granite Falls',
                'phone1'      => '360-691-5544',
                'phone2'      => null,
                'email'       => 'dwhite@example.com',
                'adults_f'    => 1, 'adults_m' => 1,
                'children'    => [
                    ['gender' => 'M', 'age' => '5', 'school' => 'Mountain Way'],
                    ['gender' => 'F', 'age' => '7', 'school' => 'Mountain Way'],
                    ['gender' => 'M', 'age' => '3', 'school' => 'None/Other'],
                ],
                'opts' => [
                    'family_number'   => 415,
                    'delivery_preference' => 'Delivery',
                    'delivery_date'   => 'December 18',
                    'delivery_time'   => '9:00 AM - 11:00 AM',
                    'delivery_reason' => 'Mobility issues, cannot come to school',
                    'delivery_status' => 'delivered',
                    'delivery_team'   => 'Team E',
                    'family_done'     => true,
                    'pet_information' => '2 cats, indoor only',
                ],
            ],

            // 13: Mountain Way range
            [
                'last_name'   => 'Rodriguez',
                'language'    => 'Spanish',
                'address_num' => '892',
                'street'      => 'Riverside Dr',
                'city'        => 'Granite Falls',
                'phone1'      => '360-691-7723',
                'phone2'      => '360-691-7724',
                'email'       => null,
                'adults_f'    => 1, 'adults_m' => 1,
                'children'    => [
                    ['gender' => 'F', 'age' => '6',  'school' => 'Mountain Way'],
                    ['gender' => 'M', 'age' => '8',  'school' => 'Mountain Way'],
                    ['gender' => 'F', 'age' => '10', 'school' => 'Mountain Way'],
                    ['gender' => 'M', 'age' => '0',  'school' => 'None/Other'],
                ],
                'opts' => [
                    'family_number'      => 451,
                    'needs_baby_supplies'=> true,
                    'need_for_help'      => 'Parents recently immigrated, limited work authorization',
                    'delivery_preference'=> 'Delivery',
                    'delivery_date'      => 'December 19',
                    'delivery_time'      => '11:00 AM - 1:00 PM',
                    'delivery_reason'    => 'No transportation',
                    'delivery_status'    => 'in_transit',
                    'delivery_team'      => 'Team B',
                ],
            ],

            // 14: Mountain Way range
            [
                'last_name'   => 'Jackson',
                'language'    => 'English',
                'address_num' => '2056',
                'street'      => 'Lakeview Ct',
                'city'        => 'Lake Stevens',
                'phone1'      => '425-334-6612',
                'phone2'      => null,
                'email'       => 'cjackson@example.com',
                'adults_f'    => 2, 'adults_m' => 0,
                'children'    => [
                    ['gender' => 'M', 'age' => '7',  'school' => 'Mountain Way'],
                    ['gender' => 'F', 'age' => '9',  'school' => 'Mountain Way'],
                ],
                'opts' => [
                    'family_number'   => 488,
                    'delivery_preference' => 'Pickup',
                    'delivery_date'   => 'December 18',
                    'delivery_status' => 'pending',
                ],
            ],

            // --- 10 families WITHOUT family numbers ---

            // 15: No number
            [
                'last_name'   => 'Miller',
                'language'    => 'English',
                'address_num' => '334',
                'street'      => 'Clearwater Lane',
                'city'        => 'Granite Falls',
                'phone1'      => '360-691-1123',
                'phone2'      => null,
                'email'       => 'tmiller@example.com',
                'adults_f'    => 1, 'adults_m' => 1,
                'children'    => [
                    ['gender' => 'F', 'age' => '5',  'school' => 'Mountain Way'],
                    ['gender' => 'M', 'age' => '8',  'school' => 'Crossroads'],
                    ['gender' => 'F', 'age' => '12', 'school' => 'GFMS'],
                ],
                'opts' => [],
            ],

            // 16: No number
            [
                'last_name'   => 'Gonzalez',
                'language'    => 'Spanish',
                'address_num' => '756',
                'street'      => 'Sunridge Way',
                'city'        => 'Granite Falls',
                'phone1'      => '360-691-9988',
                'phone2'      => '360-691-9989',
                'email'       => null,
                'adults_f'    => 1, 'adults_m' => 0,
                'children'    => [
                    ['gender' => 'M', 'age' => '3',  'school' => 'None/Other'],
                    ['gender' => 'F', 'age' => '6',  'school' => 'Mountain Way'],
                    ['gender' => 'M', 'age' => '10', 'school' => 'Crossroads'],
                    ['gender' => 'F', 'age' => '13', 'school' => 'GFMS'],
                ],
                'opts' => [
                    'need_for_help' => 'Grandmother raising grandchildren on fixed income',
                    'severe_need'   => 'Domestic violence situation, recently in shelter',
                ],
            ],

            // 17: No number
            [
                'last_name'   => 'Moore',
                'language'    => 'English',
                'address_num' => '1890',
                'street'      => 'Forest Grove Rd',
                'city'        => 'Granite Falls',
                'phone1'      => '360-691-3312',
                'phone2'      => null,
                'email'       => 'amoore@example.com',
                'adults_f'    => 1, 'adults_m' => 1,
                'children'    => [
                    ['gender' => 'F', 'age' => '4',  'school' => 'Mountain Way'],
                    ['gender' => 'M', 'age' => '6',  'school' => 'Mountain Way'],
                ],
                'opts' => [
                    'delivery_preference' => 'Pickup',
                    'delivery_date'       => 'December 19',
                ],
            ],

            // 18: No number
            [
                'last_name'   => 'Torres',
                'language'    => 'Spanish',
                'address_num' => '503',
                'street'      => 'Meadow Creek Dr',
                'city'        => 'Granite Falls',
                'phone1'      => '360-691-7756',
                'phone2'      => null,
                'email'       => null,
                'adults_f'    => 1, 'adults_m' => 1,
                'children'    => [
                    ['gender' => 'M', 'age' => '7',  'school' => 'Monte Cristo'],
                    ['gender' => 'F', 'age' => '5',  'school' => 'Monte Cristo'],
                    ['gender' => 'M', 'age' => '2',  'school' => 'None/Other'],
                ],
                'opts' => [
                    'needs_baby_supplies' => true,
                    'delivery_preference' => 'Delivery',
                    'delivery_date'       => 'December 18',
                    'delivery_time'       => '11:00 AM - 1:00 PM',
                    'delivery_reason'     => 'No transportation',
                ],
            ],

            // 19: No number
            [
                'last_name'   => 'Harris',
                'language'    => 'English',
                'address_num' => '2340',
                'street'      => 'Rolling Hills Blvd',
                'city'        => 'Snohomish',
                'phone1'      => '360-568-4490',
                'phone2'      => '360-568-4491',
                'email'       => 'charris@example.com',
                'adults_f'    => 1, 'adults_m' => 1,
                'children'    => [
                    ['gender' => 'F', 'age' => '14', 'school' => 'GFHS'],
                    ['gender' => 'M', 'age' => '16', 'school' => 'GFHS'],
                ],
                'opts' => [
                    'has_gfhs_children'  => true,
                    'delivery_preference'=> 'Pickup',
                    'delivery_date'      => 'December 18',
                ],
            ],

            // 20: No number
            [
                'last_name'   => 'Nguyen',
                'language'    => 'Other',
                'address_num' => '188',
                'street'      => 'Granite Falls Rd',
                'city'        => 'Granite Falls',
                'phone1'      => '360-691-6634',
                'phone2'      => null,
                'email'       => null,
                'adults_f'    => 1, 'adults_m' => 1,
                'children'    => [
                    ['gender' => 'M', 'age' => '9',  'school' => 'Crossroads'],
                    ['gender' => 'F', 'age' => '11', 'school' => 'Crossroads'],
                    ['gender' => 'M', 'age' => '6',  'school' => 'Mountain Way'],
                ],
                'opts' => [
                    'need_for_help' => 'Both parents working minimum wage, barely covering rent',
                ],
            ],

            // 21: No number
            [
                'last_name'   => 'Lewis',
                'language'    => 'English',
                'address_num' => '910',
                'street'      => 'Cascade Ave',
                'city'        => 'Granite Falls',
                'phone1'      => '360-691-5529',
                'phone2'      => '360-691-5530',
                'email'       => 'mlewis@example.com',
                'adults_f'    => 2, 'adults_m' => 0,
                'children'    => [
                    ['gender' => 'F', 'age' => '6',  'school' => 'Mountain Way'],
                    ['gender' => 'F', 'age' => '8',  'school' => 'Crossroads'],
                ],
                'opts' => [],
            ],

            // 22: No number
            [
                'last_name'   => 'Ramirez',
                'language'    => 'Spanish',
                'address_num' => '641',
                'street'      => 'Evergreen Way',
                'city'        => 'Granite Falls',
                'phone1'      => '360-691-8843',
                'phone2'      => null,
                'email'       => null,
                'adults_f'    => 1, 'adults_m' => 1,
                'children'    => [
                    ['gender' => 'M', 'age' => '5',  'school' => 'Mountain Way'],
                    ['gender' => 'F', 'age' => '8',  'school' => 'Monte Cristo'],
                    ['gender' => 'M', 'age' => '12', 'school' => 'GFMS'],
                    ['gender' => 'F', 'age' => '15', 'school' => 'GFHS'],
                ],
                'opts' => [
                    'has_gfhs_children' => true,
                    'need_for_help'     => 'Father disabled, mother is sole provider',
                    'delivery_preference'=> 'Delivery',
                    'delivery_date'     => 'December 19',
                    'delivery_time'     => '3:00 PM - 5:00 PM',
                    'delivery_reason'   => 'Works night shift, available mornings only',
                ],
            ],

            // 23: No number
            [
                'last_name'   => 'Robinson',
                'language'    => 'English',
                'address_num' => '1717',
                'street'      => 'Summit Drive',
                'city'        => 'Monroe',
                'phone1'      => '360-794-3312',
                'phone2'      => null,
                'email'       => 'trobinson@example.com',
                'adults_f'    => 1, 'adults_m' => 1,
                'children'    => [
                    ['gender' => 'M', 'age' => '11', 'school' => 'GFMS'],
                    ['gender' => 'F', 'age' => '13', 'school' => 'GFMS'],
                ],
                'opts' => [],
            ],

            // 24: No number
            [
                'last_name'   => 'Flores',
                'language'    => 'Spanish',
                'address_num' => '388',
                'street'      => 'Canyon Road',
                'city'        => 'Granite Falls',
                'phone1'      => '360-691-4417',
                'phone2'      => '360-691-4418',
                'email'       => null,
                'adults_f'    => 1, 'adults_m' => 0,
                'children'    => [
                    ['gender' => 'F', 'age' => '1',  'school' => 'None/Other'],
                    ['gender' => 'M', 'age' => '4',  'school' => 'None/Other'],
                    ['gender' => 'F', 'age' => '7',  'school' => 'Monte Cristo'],
                ],
                'opts' => [
                    'needs_baby_supplies' => true,
                    'need_for_help'       => 'Single mother of three, lost job in October',
                    'severe_need'         => 'House fire destroyed most belongings two months ago',
                ],
            ],
        ];

        // -----------------------------------------------------------------------
        // Child detail pools: indexed by child position for deterministic data
        // -----------------------------------------------------------------------

        $clothesSizesByAge = [
            // age => typical size
            '0'  => '12M',  '1'  => '18M', '2'  => '2T',  '3'  => '3T',
            '4'  => '4T',   '5'  => 'XS (4-5)', '6'  => 'S (6-7)',
            '7'  => 'S (6-7)', '8' => 'M (8-10)', '9' => 'M (8-10)',
            '10' => 'L (10-12)', '11' => 'L (10-12)', '12' => 'L (10-12)',
            '13' => 'S', '14' => 'M', '15' => 'M', '16' => 'L', '17' => 'L',
        ];

        // Adoptable child pool (indices of children that will be adopted)
        // tracked globally across families
        $childCounter   = 0;
        $adoptedIndices = [2, 5, 8, 11, 15, 19, 23, 28, 33, 38];
        $mergedIndices  = [0, 3, 6, 9, 12, 16, 20, 25, 30, 35, 40];
        $tagIndices     = [1, 4, 7, 10, 13, 17, 21, 26, 31, 36];
        $giftsIndices   = [2, 5, 9, 14, 18, 22, 27, 32, 37];

        foreach ($familyDefs as $idx => $def) {
            $opts = $def['opts'];

            $femaleAdults = $def['adults_f'];
            $maleAdults   = $def['adults_m'];
            $numAdults    = $femaleAdults + $maleAdults;

            // Count children by age group
            $infants      = 0;
            $youngChildren = 0;
            $childrenCount = 0;
            $tweens       = 0;
            $teenagers    = 0;

            foreach ($def['children'] as $child) {
                $age = (int) $child['age'];
                if ($age <= 1)       $infants++;
                elseif ($age <= 5)   $youngChildren++;
                elseif ($age <= 9)   $childrenCount++;
                elseif ($age <= 12)  $tweens++;
                else                 $teenagers++;
            }

            $numChildren = $infants + $youngChildren + $childrenCount + $tweens + $teenagers;
            $numMembers  = $numAdults + $numChildren;

            // Build first name from language pool
            $lang = $def['language'];
            $firstNamePool = match ($lang) {
                'Spanish' => $this->firstNamesSpanish,
                default   => $this->firstNamesEnglish,
            };
            $lastName  = $def['last_name'];
            $firstName = $this->pickIndex($firstNamePool, $idx);

            $familyData = [
                'user_id'                => $familyUser->id,
                'family_name'            => $lastName,
                'address'                => $def['address_num'] . ' ' . $def['street'] . ', ' . $def['city'] . ', WA',
                'phone1'                 => $def['phone1'],
                'phone2'                 => $def['phone2'],
                'email'                  => $def['email'],
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
                'has_crhs_children'      => $opts['has_crhs_children'] ?? false,
                'has_gfhs_children'      => $opts['has_gfhs_children'] ?? false,
                'needs_baby_supplies'    => $opts['needs_baby_supplies'] ?? false,
                'pet_information'        => $opts['pet_information'] ?? null,
                'delivery_preference'    => $opts['delivery_preference'] ?? null,
                'delivery_date'          => $opts['delivery_date'] ?? null,
                'delivery_time'          => $opts['delivery_time'] ?? null,
                'delivery_reason'        => $opts['delivery_reason'] ?? null,
                'delivery_team'          => $opts['delivery_team'] ?? null,
                'delivery_status'        => $opts['delivery_status'] ?? null,
                'need_for_help'          => $opts['need_for_help'] ?? null,
                'severe_need'            => $opts['severe_need'] ?? null,
                'family_done'            => $opts['family_done'] ?? false,
                'family_number'          => $opts['family_number'] ?? null,
            ];

            $family = Family::create($familyData);

            // -------------------------------------------------------------------
            // Children for this family
            // -------------------------------------------------------------------

            foreach ($def['children'] as $childDef) {
                $age        = $childDef['age'];
                $gender     = $childDef['gender'];
                $school     = $childDef['school'];
                $ageInt     = (int) $age;
                $clothesSize = $clothesSizesByAge[$age] ?? 'M';

                $isAdopted  = in_array($childCounter, $adoptedIndices, true);
                $isMerged   = in_array($childCounter, $mergedIndices, true);
                $hasTag     = in_array($childCounter, $tagIndices, true);
                $hasGifts   = in_array($childCounter, $giftsIndices, true);

                $styleIdx   = ($childCounter + $idx) % count($this->clothingStyles);
                $optionIdx  = ($childCounter + $idx * 2) % count($this->clothingOptions);
                $toyIdx     = ($childCounter + $idx * 3) % count($this->toyIdeas);
                $prefIdx    = ($childCounter + $idx) % count($this->giftPreferences);
                $sizesIdx   = ($childCounter + $idx * 4) % count($this->allSizesOptions);

                // Gift level: cycle 0,1,2,3,0,1... skewed toward 0 and 3
                $giftLevelMap = [0, 0, 1, 2, 3, 3, 0, 1, 3, 2, 0, 3, 1, 0, 2, 3];
                $giftLevel    = $giftLevelMap[$childCounter % count($giftLevelMap)];

                $childData = [
                    'family_id'           => $family->id,
                    'gender'              => $gender,
                    'age'                 => $age,
                    'school'              => $school,
                    'clothes_size'        => $clothesSize,
                    'clothing_styles'     => $this->clothingStyles[$styleIdx],
                    'clothing_options'    => $this->clothingOptions[$optionIdx],
                    'toy_ideas'           => ($ageInt >= 1) ? $this->toyIdeas[$toyIdx] : 'Infant toys — rattles, soft blocks',
                    'gift_preferences'    => $this->giftPreferences[$prefIdx],
                    'all_sizes'           => $this->allSizesOptions[$sizesIdx],
                    'mail_merged'         => $isMerged,
                    'gifts_received'      => $hasGifts ? $this->giftsReceivedTexts[$childCounter % count($this->giftsReceivedTexts)] : null,
                    'gift_level'          => $giftLevel,
                    'where_is_tag'        => $hasTag ? $this->whereIsTagValues[$childCounter % count($this->whereIsTagValues)] : null,
                    'adopter_name'        => $isAdopted ? $this->adopterNames[$childCounter % count($this->adopterNames)] : null,
                    'adopter_contact_info'=> $isAdopted ? '425-555-' . str_pad((string)(1000 + $childCounter), 4, '0', STR_PAD_LEFT) : null,
                ];

                Child::create($childData);
                $childCounter++;
            }
        }

        $totalChildren = Child::count();
        $this->command->info("Created 25 families with {$totalChildren} children.");
        $this->command->info('15 families have family numbers assigned, 10 are unnumbered (ready for assignment).');
    }
}
