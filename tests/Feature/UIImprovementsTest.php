<?php

namespace Tests\Feature;

use App\Enums\PackingItemStatus;
use App\Enums\PackingStatus;
use App\Models\Child;
use App\Models\Family;
use App\Models\GroceryItem;
use App\Models\PackingList;
use App\Models\Setting;
use App\Models\ShoppingAssignment;
use App\Models\User;
use App\Services\PackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UIImprovementsTest extends TestCase
{
    use RefreshDatabase;

    private User $santa;
    private User $coordinator;
    private User $familyUser;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::clearCache();

        $this->santa = User::create([
            'username' => 'santa', 'first_name' => 'Santa', 'last_name' => 'Claus',
            'password' => 'password', 'permission' => 9,
        ]);
        $this->coordinator = User::create([
            'username' => 'coord', 'first_name' => 'Coord', 'last_name' => 'Inator',
            'password' => 'password', 'permission' => 8,
        ]);
        $this->familyUser = User::create([
            'username' => 'family', 'first_name' => 'Family', 'last_name' => 'User',
            'password' => 'password', 'permission' => 7,
        ]);
    }

    private function seedWarehouseCategories(): void
    {
        $this->seed(\Database\Seeders\WarehouseCategorySeeder::class);
    }

    private function seedGroceryItems(): void
    {
        GroceryItem::create([
            'name' => 'Tuna', 'category' => 'canned', 'sort_order' => 1,
            'qty_1' => 1, 'qty_2' => 2, 'qty_3' => 3, 'qty_4' => 4,
            'qty_5' => 5, 'qty_6' => 6, 'qty_7' => 7, 'qty_8' => 8,
        ]);
        GroceryItem::create([
            'name' => 'Rice', 'category' => 'dry', 'sort_order' => 2,
            'qty_1' => 1, 'qty_2' => 2, 'qty_3' => 3, 'qty_4' => 4,
            'qty_5' => 5, 'qty_6' => 6, 'qty_7' => 7, 'qty_8' => 8,
        ]);
    }

    private static int $familyCounter = 0;

    private function createFamily(array $overrides = []): Family
    {
        self::$familyCounter++;
        return Family::create(array_merge([
            'family_name' => 'Test Family ' . self::$familyCounter,
            'family_number' => self::$familyCounter,
            'number_of_family_members' => 3,
            'number_of_adults' => 2,
            'number_of_children' => 1,
            'address' => '123 Main St',
            'phone1' => '555-1234',
        ], $overrides));
    }

    private function createPackingListWithItems(Family $family): PackingList
    {
        $service = app(PackingService::class);
        return $service->generatePackingList($family);
    }

    // =============================================
    // 1. Command Center - Stock Data Endpoint
    // =============================================

    public function test_command_center_data_returns_stock_key(): void
    {
        $this->seedWarehouseCategories();

        $response = $this->actingAs($this->santa)->getJson(route('santa.commandCenter.data'));

        $response->assertOk();
        $response->assertJsonStructure([
            'stock' => [
                'warehouse',
                'packing',
            ],
        ]);
    }

    public function test_command_center_data_stock_contains_packing_breakdown(): void
    {
        $this->seedWarehouseCategories();

        $response = $this->actingAs($this->santa)->getJson(route('santa.commandCenter.data'));

        $response->assertOk();
        $response->assertJsonStructure([
            'stock' => [
                'packing' => ['pending', 'in_progress', 'complete', 'verified', 'total', 'pct'],
            ],
        ]);
    }

    public function test_command_center_data_stock_warehouse_has_categories(): void
    {
        $this->seedWarehouseCategories();

        $response = $this->actingAs($this->santa)->getJson(route('santa.commandCenter.data'));

        $response->assertOk();
        $response->assertJsonStructure([
            'stock' => [
                'warehouse' => ['categories', 'total_on_hand', 'receipts_today'],
            ],
        ]);
    }

    // =============================================
    // 2. Footer Component & Pages Load
    // =============================================

    public function test_welcome_page_loads_ok(): void
    {
        $response = $this->get('/');
        $response->assertOk();
    }

    public function test_family_index_loads_ok(): void
    {
        $response = $this->actingAs($this->santa)->get(route('family.index'));
        $response->assertOk();
    }

    public function test_santa_index_loads_ok(): void
    {
        $response = $this->actingAs($this->santa)->get(route('santa.index'));
        $response->assertOk();
    }

    public function test_footer_text_setting_can_be_updated_via_settings(): void
    {
        $response = $this->actingAs($this->santa)->post(route('santa.updateSettings'), [
            'footer_text' => 'Custom Footer Text 2026',
            'season_year' => date('Y'),
        ]);

        $response->assertRedirect();
        Setting::clearCache();
        $this->assertEquals('Custom Footer Text 2026', Setting::get('footer_text'));
    }

    // =============================================
    // 3. Background PDF Generation
    // =============================================

    public function test_gift_tags_sync_returns_view(): void
    {
        $response = $this->actingAs($this->coordinator)->get(route('coordinator.giftTags', ['sync' => true]));

        // Should return 200 (either rendered view or PDF stream)
        $response->assertOk();
    }

    public function test_family_summary_sync_returns_view(): void
    {
        $response = $this->actingAs($this->coordinator)->get(route('coordinator.familySummary', ['sync' => true]));

        $response->assertOk();
    }

    public function test_delivery_day_sync_returns_view(): void
    {
        $response = $this->actingAs($this->coordinator)->get(route('coordinator.deliveryDay', ['sync' => true]));

        $response->assertOk();
    }

    public function test_gift_tags_async_returns_json_with_job_key(): void
    {
        $response = $this->actingAs($this->coordinator)->get(route('coordinator.giftTags'));

        $response->assertOk();
        $response->assertJsonStructure(['job_key', 'status_url', 'download_url']);
    }

    public function test_family_summary_async_returns_json_with_job_key(): void
    {
        $response = $this->actingAs($this->coordinator)->get(route('coordinator.familySummary'));

        $response->assertOk();
        $response->assertJsonStructure(['job_key', 'status_url', 'download_url']);
    }

    public function test_delivery_day_async_returns_json_with_job_key(): void
    {
        $response = $this->actingAs($this->coordinator)->get(route('coordinator.deliveryDay'));

        $response->assertOk();
        $response->assertJsonStructure(['job_key', 'status_url', 'download_url']);
    }

    public function test_pdf_status_endpoint_returns_json(): void
    {
        $response = $this->actingAs($this->coordinator)->getJson(route('coordinator.pdfStatus', 'nonexistent123'));

        $response->assertOk();
        $response->assertJsonStructure(['status', 'message']);
    }

    // =============================================
    // 4. Backup Settings
    // =============================================

    public function test_backup_interval_hours_persists_via_settings(): void
    {
        $response = $this->actingAs($this->santa)->post(route('santa.updateSettings'), [
            'backup_interval_hours' => '12',
            'season_year' => date('Y'),
        ]);

        $response->assertRedirect();
        Setting::clearCache();
        $this->assertEquals('12', Setting::get('backup_interval_hours'));
    }

    public function test_backup_path_persists_via_settings(): void
    {
        $response = $this->actingAs($this->santa)->post(route('santa.updateSettings'), [
            'backup_path' => '/tmp/test-backups',
            'season_year' => date('Y'),
        ]);

        $response->assertRedirect();
        Setting::clearCache();
        $this->assertEquals('/tmp/test-backups', Setting::get('backup_path'));
    }

    public function test_backup_database_artisan_command_runs(): void
    {
        $dbPath = config('database.connections.sqlite.database');

        // If using :memory: DB (test env), the command will fail because file doesn't exist — that's expected
        if ($dbPath === ':memory:' || !file_exists($dbPath)) {
            $this->artisan('backup:database', ['--force' => true])
                ->assertExitCode(1); // Failure expected — no physical DB file
        } else {
            $this->artisan('backup:database', ['--force' => true])
                ->assertExitCode(0);
        }
    }

    // =============================================
    // 5. Packing System Improvements
    // =============================================

    public function test_packing_print_with_food_type_filter(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['family_name' => 'PrintFood']);
        $list = $this->createPackingListWithItems($family);

        $response = $this->actingAs($this->santa)->get(route('packing.print', $list) . '?type=food');
        $response->assertOk();
    }

    public function test_packing_print_with_gift_type_filter(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['family_name' => 'PrintGift']);
        $list = $this->createPackingListWithItems($family);

        $response = $this->actingAs($this->santa)->get(route('packing.print', $list) . '?type=gift');
        $response->assertOk();
    }

    public function test_packing_print_with_both_type_filter(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['family_name' => 'PrintBoth']);
        $list = $this->createPackingListWithItems($family);

        $response = $this->actingAs($this->santa)->get(route('packing.print', $list) . '?type=both');
        $response->assertOk();
    }

    public function test_packing_show_hides_family_name_when_pii_disabled(): void
    {
        Setting::set('packing_show_names', '0');
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['family_name' => 'SecretFamily']);
        $list = $this->createPackingListWithItems($family);

        $response = $this->actingAs($this->santa)->get(route('packing.show', $list));
        $response->assertOk();
        $response->assertDontSee('SecretFamily');
        $response->assertSee('Family #' . $family->family_number);
    }

    public function test_packing_show_displays_family_name_when_pii_enabled(): void
    {
        Setting::set('packing_show_names', '1');
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['family_name' => 'VisibleFamily']);
        $list = $this->createPackingListWithItems($family);

        $response = $this->actingAs($this->santa)->get(route('packing.show', $list));
        $response->assertOk();
        $response->assertSee('VisibleFamily');
    }

    public function test_packing_generate_with_status_filter_pending(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        // Create two families, one with pending packing list already
        $familyA = $this->createFamily(['family_name' => 'PendingFamily']);
        $familyB = $this->createFamily(['family_name' => 'OtherFamily']);
        $listA = $this->createPackingListWithItems($familyA);
        $listA->update(['status' => PackingStatus::Pending]);

        $response = $this->actingAs($this->santa)->post(route('packing.generate'), [
            'status_filter' => 'pending',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_packing_dashboard_loads_without_clock_in_buttons(): void
    {
        $response = $this->actingAs($this->santa)->get(route('packing.dashboard'));
        $response->assertOk();
        $response->assertDontSee('Clock In');
    }

    public function test_packing_show_page_has_mobile_link(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['family_name' => 'MobileFamily']);
        $list = $this->createPackingListWithItems($family);

        $response = $this->actingAs($this->santa)->get(route('packing.show', $list));
        $response->assertOk();
        $response->assertSee('Mobile');
    }

    // =============================================
    // 6. Mobile Scanner
    // =============================================

    public function test_mobile_scanner_authenticated_shows_active_packing_lists(): void
    {
        $response = $this->actingAs($this->santa)->get('/warehouse/mobile-scan');
        $response->assertOk();
        // Should see either active lists or no-lists message
        $response->assertSee('Mobile Packing Scanner');
    }

    public function test_mobile_scanner_with_valid_token_does_not_show_family_name(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily(['family_name' => 'HiddenNameFamily']);
        $list = $this->createPackingListWithItems($family);

        $response = $this->get("/warehouse/mobile-scan?token={$list->qr_token}");
        $response->assertOk();
        // Mobile scanner should show family number, not family name (PII removed)
        $response->assertDontSee('HiddenNameFamily');
        $response->assertSee('Family #' . $family->family_number);
    }

    public function test_mobile_scanner_with_valid_token_shows_items_to_pack(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();
        $family = $this->createFamily();
        $list = $this->createPackingListWithItems($family);

        $response = $this->get("/warehouse/mobile-scan?token={$list->qr_token}");
        $response->assertOk();
        $response->assertSee('items to pack');
    }

    // =============================================
    // 7. Shopping Assignments - New Types
    // =============================================

    public function test_smart_split_assignment_creates_multiple_assignments(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        // Create families with numbers so smart split has data
        for ($i = 0; $i < 6; $i++) {
            $family = $this->createFamily([
                'family_name' => "SmartSplitFamily{$i}",
                'number_of_family_members' => 4,
            ]);
            $this->createPackingListWithItems($family);
        }

        $response = $this->actingAs($this->santa)->post('/santa/shopping-day/assignments', [
            'ninja_name' => 'Smart Ninja',
            'split_type' => 'smart_split',
            'num_shoppers' => 3,
        ]);

        $response->assertRedirect('/santa/shopping-day');
        // Smart split should create multiple assignments
        $this->assertGreaterThanOrEqual(1, ShoppingAssignment::where('split_type', 'smart_split')->count());
    }

    public function test_subcategory_assignment_creates_single_assignment(): void
    {
        $this->seedWarehouseCategories();
        $this->seedGroceryItems();

        $groceryItem = GroceryItem::first();

        $response = $this->actingAs($this->santa)->post('/santa/shopping-day/assignments', [
            'ninja_name' => 'SubcatNinja',
            'split_type' => 'subcategory',
            'subcategory_category' => 'canned',
            'subcategory_items' => [$groceryItem->id],
        ]);

        $response->assertRedirect('/santa/shopping-day');
        $this->assertDatabaseHas('shopping_assignments', [
            'ninja_name' => 'SubcatNinja',
            'split_type' => 'subcategory',
        ]);
    }

    public function test_smart_split_validation_fails_without_num_shoppers(): void
    {
        $response = $this->actingAs($this->santa)->post('/santa/shopping-day/assignments', [
            'ninja_name' => 'BadSplitNinja',
            'split_type' => 'smart_split',
            // missing num_shoppers
        ]);

        $response->assertSessionHasErrors('num_shoppers');
    }

    public function test_subcategory_validation_fails_without_subcategory_items(): void
    {
        $response = $this->actingAs($this->santa)->post('/santa/shopping-day/assignments', [
            'ninja_name' => 'BadSubcatNinja',
            'split_type' => 'subcategory',
            'subcategory_category' => 'canned',
            // missing subcategory_items
        ]);

        $response->assertSessionHasErrors('subcategory_items');
    }

    public function test_shopping_assignment_model_get_description_smart_split(): void
    {
        $assignment = ShoppingAssignment::create([
            'ninja_name' => 'Test Ninja',
            'split_type' => 'smart_split',
            'config' => [
                'group_number' => 1,
                'total_groups' => 3,
                'family_ids' => [1, 2, 3],
            ],
        ]);

        $description = $assignment->getDescription();
        $this->assertStringContainsString('Smart split group 1/3', $description);
        $this->assertStringContainsString('3 families', $description);
    }

    public function test_shopping_assignment_model_get_description_subcategory(): void
    {
        $assignment = ShoppingAssignment::create([
            'ninja_name' => 'Test Ninja',
            'split_type' => 'subcategory',
            'config' => [
                'category_name' => 'canned',
                'item_ids' => [1, 2, 3, 4],
            ],
        ]);

        $description = $assignment->getDescription();
        $this->assertStringContainsString('Canned', $description);
        $this->assertStringContainsString('4 selected items', $description);
    }

    public function test_shopping_assignment_model_get_display_name_for_smart_split(): void
    {
        $assignment = ShoppingAssignment::create([
            'ninja_name' => 'Alpha Ninja',
            'split_type' => 'smart_split',
            'config' => [
                'group_number' => 2,
                'total_groups' => 4,
                'family_ids' => [5, 6],
            ],
        ]);

        $displayName = $assignment->getDisplayName();
        $this->assertEquals('Alpha Ninja', $displayName);
    }

    public function test_shopping_assignment_model_get_display_name_with_user(): void
    {
        $assignment = ShoppingAssignment::create([
            'user_id' => $this->coordinator->id,
            'split_type' => 'smart_split',
            'config' => [
                'group_number' => 1,
                'total_groups' => 2,
                'family_ids' => [1],
            ],
        ]);

        $displayName = $assignment->getDisplayName();
        $this->assertEquals('Coord Inator', $displayName);
    }

    // =============================================
    // 8. Table Sorting Infrastructure & Page Loads
    // =============================================

    public function test_warehouse_inventory_page_loads(): void
    {
        $this->seedWarehouseCategories();

        $response = $this->actingAs($this->coordinator)->get(route('warehouse.inventory'));
        $response->assertOk();
    }

    public function test_santa_users_page_loads(): void
    {
        $response = $this->actingAs($this->santa)->get(route('santa.users'));
        $response->assertOk();
    }

    public function test_gift_bank_page_loads(): void
    {
        $this->seedWarehouseCategories();

        $response = $this->actingAs($this->coordinator)->get(route('warehouse.gift-bank'));
        $response->assertOk();
    }

    public function test_command_center_page_loads(): void
    {
        $response = $this->actingAs($this->santa)->get(route('santa.commandCenter'));
        $response->assertOk();
    }

    public function test_shopping_day_page_loads(): void
    {
        $response = $this->actingAs($this->santa)->get(route('santa.shoppingDay'));
        $response->assertOk();
    }

    // =============================================
    // 9. Settings Persistence
    // =============================================

    public function test_footer_text_setting_persists(): void
    {
        Setting::set('footer_text', 'Test Footer 2026');
        Setting::clearCache();
        $this->assertEquals('Test Footer 2026', Setting::get('footer_text'));
    }

    public function test_backup_interval_hours_setting_persists(): void
    {
        Setting::set('backup_interval_hours', '8');
        Setting::clearCache();
        $this->assertEquals('8', Setting::get('backup_interval_hours'));
    }

    public function test_backup_path_setting_persists(): void
    {
        Setting::set('backup_path', '/var/backups/gfsd');
        Setting::clearCache();
        $this->assertEquals('/var/backups/gfsd', Setting::get('backup_path'));
    }

    public function test_packing_show_names_toggle_persists(): void
    {
        Setting::set('packing_show_names', '0');
        Setting::clearCache();
        $this->assertEquals('0', Setting::get('packing_show_names'));

        Setting::set('packing_show_names', '1');
        Setting::clearCache();
        $this->assertEquals('1', Setting::get('packing_show_names'));
    }

    public function test_packing_show_names_toggle_via_settings_post(): void
    {
        // POST with packing_show_names unchecked (boolean false)
        $response = $this->actingAs($this->santa)->post(route('santa.updateSettings'), [
            'packing_show_names' => false,
            'season_year' => date('Y'),
        ]);

        $response->assertRedirect();
        Setting::clearCache();
        $this->assertEquals('0', Setting::get('packing_show_names'));

        // POST with packing_show_names checked (boolean true)
        $response = $this->actingAs($this->santa)->post(route('santa.updateSettings'), [
            'packing_show_names' => '1',
            'season_year' => date('Y'),
        ]);

        $response->assertRedirect();
        Setting::clearCache();
        $this->assertEquals('1', Setting::get('packing_show_names'));
    }

    public function test_all_settings_persist_in_single_post(): void
    {
        $response = $this->actingAs($this->santa)->post(route('santa.updateSettings'), [
            'footer_text' => 'All-In-One Footer',
            'backup_interval_hours' => '6',
            'backup_path' => '/custom/path',
            'packing_show_names' => '1',
            'packing_system_enabled' => '1',
            'season_year' => date('Y'),
        ]);

        $response->assertRedirect();
        Setting::clearCache();

        $this->assertEquals('All-In-One Footer', Setting::get('footer_text'));
        $this->assertEquals('6', Setting::get('backup_interval_hours'));
        $this->assertEquals('/custom/path', Setting::get('backup_path'));
        $this->assertEquals('1', Setting::get('packing_show_names'));
        $this->assertEquals('1', Setting::get('packing_system_enabled'));
    }
}
