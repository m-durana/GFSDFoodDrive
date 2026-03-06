<?php

namespace Tests\Unit;

use App\Console\Commands\BackupDatabase;
use App\Enums\PackingStatus;
use App\Models\Family;
use App\Models\GroceryItem;
use App\Models\PackingItem;
use App\Models\PackingList;
use App\Models\Setting;
use App\Models\ShoppingAssignment;
use App\Models\User;
use App\Services\PackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class UIImprovementsUnitTest extends TestCase
{
    use RefreshDatabase;

    private PackingService $service;
    private User $coordinator;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::clearCache();
        $this->service = app(PackingService::class);
        $this->coordinator = User::create([
            'username' => 'coord_test', 'first_name' => 'Test', 'last_name' => 'Coordinator',
            'password' => 'password', 'permission' => 8,
        ]);
    }

    // --- Helpers ---

    private function seedWarehouseCategories(): void
    {
        $this->seed(\Database\Seeders\WarehouseCategorySeeder::class);
    }

    private function seedGroceryItems(): void
    {
        GroceryItem::create([
            'name' => 'Tuna', 'category' => 'canned', 'sort_order' => 1,
            'qty_1' => 1, 'qty_2' => 4, 'qty_3' => 4, 'qty_4' => 4,
            'qty_5' => 7, 'qty_6' => 7, 'qty_7' => 8, 'qty_8' => 15,
        ]);
        GroceryItem::create([
            'name' => 'Green Beans', 'category' => 'canned', 'sort_order' => 2,
            'qty_1' => 1, 'qty_2' => 2, 'qty_3' => 2, 'qty_4' => 3,
            'qty_5' => 4, 'qty_6' => 5, 'qty_7' => 6, 'qty_8' => 8,
        ]);
    }

    private function createFamily(array $overrides = []): Family
    {
        $defaults = [
            'family_name' => 'Test Family',
            'family_number' => Family::withoutGlobalScopes()->max('family_number') + 1,
            'number_of_family_members' => 4,
            'season_year' => Setting::get('season_year', date('Y')),
            'address' => '123 Test St',
            'phone1' => '555-0100',
            'female_adults' => 1,
            'male_adults' => 1,
            'other_adults' => 0,
            'number_of_adults' => 2,
            'infants' => 0,
            'young_children' => 0,
            'children_count' => 2,
            'tweens' => 0,
            'teenagers' => 0,
            'number_of_children' => 2,
        ];

        return Family::withoutGlobalScopes()->create(array_merge($defaults, $overrides));
    }

    private function createFamilyWithPackingList(string $status = 'pending', array $familyOverrides = []): array
    {
        $family = $this->createFamily($familyOverrides);
        $list = PackingList::withoutGlobalScopes()->create([
            'family_id' => $family->id,
            'season_year' => $family->season_year,
            'status' => $status,
        ]);

        return [$family, $list];
    }

    // =========================================================================
    // Section 1: PackingService::generateAllPackingLists with status filter
    // =========================================================================

    /** @test */
    public function generate_all_packing_lists_with_null_filter_generates_for_all_families(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $seasonYear = Setting::get('season_year', date('Y'));

        $family1 = $this->createFamily(['family_name' => 'Family A', 'family_number' => 1]);
        $family2 = $this->createFamily(['family_name' => 'Family B', 'family_number' => 2]);
        $family3 = $this->createFamily(['family_name' => 'Family C', 'family_number' => 3]);

        $count = $this->service->generateAllPackingLists(null, null);

        $this->assertEquals(3, $count);
        $this->assertEquals(3, PackingList::withoutGlobalScopes()->where('season_year', $seasonYear)->count());
    }

    /** @test */
    public function generate_all_packing_lists_with_pending_filter_only_regenerates_pending(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        // Create families with existing packing lists in different statuses
        [$familyPending, $listPending] = $this->createFamilyWithPackingList('pending');
        [$familyInProgress, $listInProgress] = $this->createFamilyWithPackingList('in_progress');
        [$familyComplete, $listComplete] = $this->createFamilyWithPackingList('complete');

        // generateAllPackingLists with 'pending' filter should only touch families with pending lists
        $count = $this->service->generateAllPackingLists(null, 'pending');

        // Only the family with a pending packing list matches
        $this->assertEquals(1, $count);
    }

    /** @test */
    public function generate_all_packing_lists_with_in_progress_filter_only_regenerates_in_progress(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        [$familyPending, $listPending] = $this->createFamilyWithPackingList('pending');
        [$familyInProgress, $listInProgress] = $this->createFamilyWithPackingList('in_progress');

        $count = $this->service->generateAllPackingLists(null, 'in_progress');

        // Only the family with an in_progress packing list matches
        $this->assertEquals(1, $count);
    }

    /** @test */
    public function generate_all_packing_lists_with_nonexistent_status_returns_zero(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $this->createFamily(['family_name' => 'Only Family', 'family_number' => 1]);

        // No packing lists exist at all, so filtering by any status yields 0
        $count = $this->service->generateAllPackingLists(null, 'verified');

        $this->assertEquals(0, $count);
    }

    /** @test */
    public function generate_all_packing_lists_default_null_filter_generates_for_all(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $this->createFamily(['family_name' => 'Family X', 'family_number' => 10]);
        $this->createFamily(['family_name' => 'Family Y', 'family_number' => 11]);

        // Calling with just season (default filter = null) generates for all
        $count = $this->service->generateAllPackingLists();

        $this->assertEquals(2, $count);
    }

    // =========================================================================
    // Section 2: ShoppingAssignment Model - New Split Types
    // =========================================================================

    /** @test */
    public function get_description_for_smart_split_returns_correct_format(): void
    {
        $assignment = new ShoppingAssignment([
            'split_type' => 'smart_split',
            'ninja_name' => 'TestNinja',
            'config' => [
                'group_number' => 1,
                'total_groups' => 3,
                'family_ids' => [10, 20, 30, 40, 50],
            ],
        ]);

        $description = $assignment->getDescription();

        $this->assertEquals('Smart split group 1/3 (5 families)', $description);
    }

    /** @test */
    public function get_description_for_subcategory_returns_correct_format(): void
    {
        $assignment = new ShoppingAssignment([
            'split_type' => 'subcategory',
            'ninja_name' => 'TestNinja',
            'config' => [
                'category_name' => 'canned',
                'item_ids' => [1, 2, 3],
            ],
        ]);

        $description = $assignment->getDescription();

        $this->assertEquals('Canned: 3 selected items', $description);
    }

    /** @test */
    public function get_description_for_deficit_returns_correct_string(): void
    {
        $assignment = new ShoppingAssignment([
            'split_type' => 'deficit',
            'ninja_name' => 'TestNinja',
        ]);

        $description = $assignment->getDescription();

        $this->assertEquals('Full deficit buy — all items needing purchase', $description);
    }

    /** @test */
    public function get_description_for_family_range_returns_correct_format(): void
    {
        $assignment = new ShoppingAssignment([
            'split_type' => 'family_range',
            'ninja_name' => 'TestNinja',
            'family_start' => 1,
            'family_end' => 50,
        ]);

        $description = $assignment->getDescription();

        $this->assertStringContainsString('1', $description);
        $this->assertStringContainsString('50', $description);
    }

    /** @test */
    public function get_display_name_returns_ninja_name_when_no_user(): void
    {
        $assignment = new ShoppingAssignment([
            'split_type' => 'deficit',
            'ninja_name' => 'SilentShopper',
        ]);

        // user relation is not loaded, user_id is null
        $this->assertEquals('SilentShopper', $assignment->getDisplayName());
    }

    /** @test */
    public function get_display_name_returns_user_full_name_when_user_set(): void
    {
        $user = User::create([
            'username' => 'shopper1', 'first_name' => 'Jane', 'last_name' => 'Doe',
            'password' => 'password', 'permission' => 8,
        ]);

        $assignment = ShoppingAssignment::create([
            'user_id' => $user->id,
            'ninja_name' => 'FallbackName',
            'split_type' => 'deficit',
        ]);

        $assignment->load('user');

        $this->assertEquals('Jane Doe', $assignment->getDisplayName());
    }

    /** @test */
    public function get_shopping_list_for_smart_split_with_empty_family_ids_returns_empty(): void
    {
        $assignment = ShoppingAssignment::create([
            'split_type' => 'smart_split',
            'ninja_name' => 'EmptyShopper',
            'config' => [
                'group_number' => 1,
                'total_groups' => 1,
                'family_ids' => [],
            ],
        ]);

        $list = $assignment->getShoppingList();

        $this->assertIsArray($list);
        $this->assertEmpty($list);
    }

    /** @test */
    public function get_shopping_list_for_subcategory_with_empty_item_ids_returns_empty(): void
    {
        $assignment = ShoppingAssignment::create([
            'split_type' => 'subcategory',
            'ninja_name' => 'SubcatShopper',
            'config' => [
                'category_name' => 'canned',
                'item_ids' => [],
            ],
        ]);

        $list = $assignment->getShoppingList();

        $this->assertIsArray($list);
        $this->assertEmpty($list);
    }

    /** @test */
    public function config_attribute_casts_to_and_from_array(): void
    {
        $configData = [
            'group_number' => 2,
            'total_groups' => 4,
            'family_ids' => [5, 10, 15],
        ];

        $assignment = ShoppingAssignment::create([
            'split_type' => 'smart_split',
            'ninja_name' => 'CastTest',
            'config' => $configData,
        ]);

        // Reload from database
        $fresh = ShoppingAssignment::find($assignment->id);

        $this->assertIsArray($fresh->config);
        $this->assertEquals(2, $fresh->config['group_number']);
        $this->assertEquals(4, $fresh->config['total_groups']);
        $this->assertEquals([5, 10, 15], $fresh->config['family_ids']);
    }

    // =========================================================================
    // Section 3: BackupDatabase Command
    // =========================================================================

    /** @test */
    public function backup_command_uses_setting_for_backup_path(): void
    {
        $dbPath = config('database.connections.sqlite.database');
        if ($dbPath === ':memory:') {
            $this->markTestSkipped('Backup tests require a physical SQLite file, not :memory:');
        }

        $customPath = storage_path('test_backups_' . uniqid());
        Setting::set('backup_path', $customPath);

        $this->artisan('backup:database')
            ->assertExitCode(0);

        $this->assertDirectoryExists($customPath);
        $files = glob("{$customPath}/backup_*");
        $this->assertNotEmpty($files, 'Backup file should be created in the custom path');

        foreach (glob("{$customPath}/backup_*") as $file) {
            @unlink($file);
        }
        @rmdir($customPath);
    }

    /** @test */
    public function backup_command_falls_back_to_storage_backups_when_no_setting(): void
    {
        $dbPath = config('database.connections.sqlite.database');
        if ($dbPath === ':memory:') {
            $this->markTestSkipped('Backup tests require a physical SQLite file, not :memory:');
        }

        Setting::clearCache();
        Setting::where('key', 'backup_path')->delete();

        $defaultPath = storage_path('backups');

        $this->artisan('backup:database')
            ->assertExitCode(0);

        $this->assertDirectoryExists($defaultPath);
        $files = glob("{$defaultPath}/backup_*");
        $this->assertNotEmpty($files, 'Backup file should be created in the default path');
    }

    /** @test */
    public function backup_command_fails_gracefully_with_memory_db(): void
    {
        $dbPath = config('database.connections.sqlite.database');
        if ($dbPath !== ':memory:') {
            $this->markTestSkipped('This test is only for :memory: SQLite');
        }

        $this->artisan('backup:database')
            ->assertExitCode(1);
    }

    /** @test */
    public function backup_command_skips_when_unchanged(): void
    {
        $dbPath = config('database.connections.sqlite.database');
        if ($dbPath === ':memory:') {
            $this->markTestSkipped('Backup tests require a physical SQLite file, not :memory:');
        }

        $backupDir = storage_path('test_skip_backups_' . uniqid());
        Setting::set('backup_path', $backupDir);

        $this->artisan('backup:database')
            ->assertExitCode(0);

        $filesAfterFirst = glob("{$backupDir}/backup_*");
        $countAfterFirst = count($filesAfterFirst);

        $this->artisan('backup:database')
            ->assertExitCode(0);

        $filesAfterSecond = glob("{$backupDir}/backup_*");
        $countAfterSecond = count($filesAfterSecond);

        $this->assertEquals($countAfterFirst, $countAfterSecond, 'Second backup should be skipped when DB unchanged');

        foreach (glob("{$backupDir}/backup_*") as $file) {
            @unlink($file);
        }
        @rmdir($backupDir);
    }

    /** @test */
    public function backup_command_force_flag_creates_new_backup_even_when_unchanged(): void
    {
        $dbPath = config('database.connections.sqlite.database');
        if ($dbPath === ':memory:') {
            $this->markTestSkipped('Backup tests require a physical SQLite file, not :memory:');
        }

        $backupDir = storage_path('test_force_backups_' . uniqid());
        Setting::set('backup_path', $backupDir);

        $this->artisan('backup:database')
            ->assertExitCode(0);

        $filesAfterFirst = glob("{$backupDir}/backup_*");
        $countAfterFirst = count($filesAfterFirst);

        usleep(1100000);

        $this->artisan('backup:database', ['--force' => true])
            ->assertExitCode(0);

        $filesAfterSecond = glob("{$backupDir}/backup_*");
        $countAfterSecond = count($filesAfterSecond);

        $this->assertGreaterThan($countAfterFirst, $countAfterSecond, '--force should create a new backup');

        foreach (glob("{$backupDir}/backup_*") as $file) {
            @unlink($file);
        }
        @rmdir($backupDir);
    }

    // =========================================================================
    // Section 4: Footer Component Data (Setting model)
    // =========================================================================

    /** @test */
    public function setting_get_returns_default_when_not_set(): void
    {
        Setting::clearCache();
        Setting::where('key', 'footer_text')->delete();

        $value = Setting::get('footer_text', 'Made in default');

        $this->assertEquals('Made in default', $value);
    }

    /** @test */
    public function setting_set_persists_and_get_returns_it(): void
    {
        Setting::clearCache();

        Setting::set('footer_text', 'Custom Footer');
        Setting::clearCache(); // Clear cache to force DB read

        $value = Setting::get('footer_text');

        $this->assertEquals('Custom Footer', $value);
    }

    /** @test */
    public function setting_get_returns_cached_value_on_repeated_calls(): void
    {
        Setting::clearCache();

        Setting::set('footer_text', 'Cached Value');
        Setting::clearCache();

        // First call loads from DB and caches
        $value1 = Setting::get('footer_text');
        // Second call should return cached value
        $value2 = Setting::get('footer_text');

        $this->assertEquals('Cached Value', $value1);
        $this->assertEquals('Cached Value', $value2);
    }

    /** @test */
    public function setting_set_updates_existing_value(): void
    {
        Setting::clearCache();

        Setting::set('footer_text', 'Original');
        Setting::set('footer_text', 'Updated');
        Setting::clearCache();

        $value = Setting::get('footer_text');

        $this->assertEquals('Updated', $value);
        // Should only have one record for this key
        $this->assertEquals(1, Setting::where('key', 'footer_text')->count());
    }

    // =========================================================================
    // Section 5: Site Footer Component Rendering
    // =========================================================================

    /** @test */
    public function site_footer_component_renders_without_errors_minimal_variant(): void
    {
        Setting::clearCache();

        $rendered = $this->blade('<x-site-footer />');

        $rendered->assertSee('GFSD Food Drive');
        $rendered->assertSee(date('Y'));
    }

    /** @test */
    public function site_footer_component_renders_with_custom_footer_text(): void
    {
        Setting::clearCache();
        Setting::set('footer_text', 'Custom Test Footer');

        $rendered = $this->blade('<x-site-footer />');

        $rendered->assertSee('Custom Test Footer');
    }

    /** @test */
    public function site_footer_component_renders_full_dark_variant(): void
    {
        Setting::clearCache();

        $rendered = $this->blade('<x-site-footer variant="full-dark" />');

        $rendered->assertSee('Granite Falls School District');
        $rendered->assertSee(date('Y'));
    }

    /** @test */
    public function site_footer_component_renders_dark_variant(): void
    {
        Setting::clearCache();

        $rendered = $this->blade('<x-site-footer variant="dark" />');

        $rendered->assertSee('GFSD Food &amp; Gift Drive', false);
    }

    /** @test */
    public function site_footer_component_renders_light_variant(): void
    {
        Setting::clearCache();

        $rendered = $this->blade('<x-site-footer variant="light" />');

        $rendered->assertSee('Organized by');
    }

    /** @test */
    public function site_footer_component_renders_border_variant(): void
    {
        Setting::clearCache();

        $rendered = $this->blade('<x-site-footer variant="border" />');

        $rendered->assertSee('Granite Falls School District Food Drive');
    }
}
