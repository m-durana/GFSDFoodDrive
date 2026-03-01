<?php

namespace Tests\Feature;

use App\Enums\GiftLevel;
use App\Models\Child;
use App\Models\Family;
use App\Models\GiftBankItem;
use App\Models\User;
use App\Models\WarehouseTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GiftBankTest extends TestCase
{
    use RefreshDatabase;

    private User $santa;
    private User $familyUser;
    private Family $family;
    private Child $child;

    protected function setUp(): void
    {
        parent::setUp();

        $this->santa = User::create([
            'username' => 'giftbank_santa',
            'first_name' => 'Test',
            'last_name' => 'Santa',
            'password' => 'password123',
            'permission' => 9,
        ]);

        $this->familyUser = User::create([
            'username' => 'giftbank_family',
            'first_name' => 'Test',
            'last_name' => 'Family',
            'password' => 'password123',
            'permission' => 7,
        ]);

        $this->family = Family::create([
            'user_id' => $this->santa->id,
            'family_name' => 'Gift Bank Test Family',
            'family_number' => 200,
            'address' => '200 Bank St',
            'phone1' => '360-555-2000',
            'number_of_adults' => 2,
            'number_of_children' => 1,
            'number_of_family_members' => 3,
            'female_adults' => 1,
            'male_adults' => 1,
            'infants' => 0,
            'young_children' => 0,
            'children_count' => 1,
            'tweens' => 0,
            'teenagers' => 0,
        ]);

        $this->child = Child::create([
            'family_id' => $this->family->id,
            'gender' => 'Female',
            'age' => '10',
            'gift_level' => GiftLevel::None,
        ]);

        $this->seed(\Database\Seeders\WarehouseCategorySeeder::class);
    }

    public function test_gift_bank_index_loads(): void
    {
        $response = $this->actingAs($this->santa)->get('/warehouse/gift-bank');
        $response->assertStatus(200);
        $response->assertSee('Gift Bank');
    }

    public function test_gift_bank_item_can_be_created(): void
    {
        $response = $this->actingAs($this->santa)->post('/warehouse/gift-bank', [
            'description' => 'Barbie doll set',
            'age_range' => '6-12',
            'gender_suitability' => 'female',
            'gift_type' => 'Toy',
            'donor_name' => 'Community Donor',
            'quantity' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('gift_bank_items', [
            'description' => 'Barbie doll set',
            'age_range' => '6-12',
            'gender_suitability' => 'female',
        ]);
    }

    public function test_gift_bank_item_requires_description(): void
    {
        $response = $this->actingAs($this->santa)->post('/warehouse/gift-bank', [
            'age_range' => '6-12',
        ]);

        $response->assertSessionHasErrors('description');
    }

    public function test_gift_bank_item_can_be_assigned_to_child(): void
    {
        $item = GiftBankItem::create([
            'description' => 'Art supply set',
            'age_range' => '6-12',
            'gender_suitability' => 'neutral',
        ]);

        $response = $this->actingAs($this->santa)->post("/warehouse/gift-bank/{$item->id}/assign/{$this->child->id}");
        $response->assertRedirect();

        $item->refresh();
        $this->assertEquals($this->child->id, $item->assigned_child_id);
        $this->assertNotNull($item->assigned_at);

        // Should also create a warehouse transaction
        $this->assertDatabaseHas('warehouse_transactions', [
            'child_id' => $this->child->id,
            'source' => 'Gift Bank',
        ]);
    }

    public function test_gift_bank_item_can_be_unassigned(): void
    {
        $item = GiftBankItem::create([
            'description' => 'Board game',
            'assigned_child_id' => $this->child->id,
            'assigned_at' => now(),
        ]);

        $response = $this->actingAs($this->santa)->post("/warehouse/gift-bank/{$item->id}/unassign");
        $response->assertRedirect();

        $item->refresh();
        $this->assertNull($item->assigned_child_id);
        $this->assertNull($item->assigned_at);
    }

    public function test_gift_bank_item_can_be_deleted(): void
    {
        $item = GiftBankItem::create([
            'description' => 'To be deleted',
        ]);

        $response = $this->actingAs($this->santa)->delete("/warehouse/gift-bank/{$item->id}");
        $response->assertRedirect();

        $this->assertDatabaseMissing('gift_bank_items', ['id' => $item->id]);
    }

    public function test_suggestions_endpoint_returns_matching_items(): void
    {
        GiftBankItem::create([
            'description' => 'Girl toy 6-12',
            'age_range' => '6-12',
            'gender_suitability' => 'female',
        ]);

        GiftBankItem::create([
            'description' => 'Boy toy 13-17',
            'age_range' => '13-17',
            'gender_suitability' => 'male',
        ]);

        GiftBankItem::create([
            'description' => 'Neutral any-age',
            'age_range' => 'any',
            'gender_suitability' => 'neutral',
        ]);

        // Child is Female, age 10 → should match '6-12' + 'any', female + neutral
        $response = $this->actingAs($this->santa)->getJson("/warehouse/gift-bank/suggestions/{$this->child->id}");
        $response->assertStatus(200);

        $descriptions = collect($response->json())->pluck('description')->toArray();
        $this->assertContains('Girl toy 6-12', $descriptions);
        $this->assertContains('Neutral any-age', $descriptions);
        $this->assertNotContains('Boy toy 13-17', $descriptions);
    }

    public function test_family_user_cannot_access_gift_bank(): void
    {
        $response = $this->actingAs($this->familyUser)->get('/warehouse/gift-bank');
        $response->assertStatus(403);
    }

    public function test_gift_bank_assign_updates_child_gift_level(): void
    {
        $item = GiftBankItem::create([
            'description' => 'Gift for child',
            'age_range' => '6-12',
            'gender_suitability' => 'female',
        ]);

        $this->actingAs($this->santa)->post("/warehouse/gift-bank/{$item->id}/assign/{$this->child->id}");

        $this->child->refresh();
        $this->assertGreaterThanOrEqual(GiftLevel::Moderate->value, $this->child->gift_level->value);
    }

    public function test_gift_bank_filters_work(): void
    {
        GiftBankItem::create(['description' => 'Unassigned item']);
        GiftBankItem::create([
            'description' => 'Assigned item',
            'assigned_child_id' => $this->child->id,
            'assigned_at' => now(),
        ]);

        $response = $this->actingAs($this->santa)->get('/warehouse/gift-bank?status=unassigned');
        $response->assertStatus(200);
        $response->assertSee('Unassigned item');

        $response = $this->actingAs($this->santa)->get('/warehouse/gift-bank?status=assigned');
        $response->assertStatus(200);
        $response->assertSee('Assigned item');
    }
}
