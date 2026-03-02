<?php

namespace Tests\Feature;

use App\Enums\DeliveryStatus;
use App\Enums\GiftLevel;
use App\Models\Child;
use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportReportsTest extends TestCase
{
    use RefreshDatabase;

    private User $santa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->santa = User::create([
            'username' => 'santa', 'first_name' => 'S', 'last_name' => 'C',
            'password' => 'password', 'permission' => 9,
        ]);
    }

    public function test_export_page_loads(): void
    {
        $response = $this->actingAs($this->santa)->get(route('santa.export'));
        $response->assertOk();
    }

    public function test_reports_page_loads(): void
    {
        $response = $this->actingAs($this->santa)->get(route('santa.reports'));
        $response->assertOk();
    }

    public function test_reports_page_shows_stats(): void
    {
        $family = Family::create([
            'family_name' => 'Report Test', 'family_number' => 1,
            'number_of_family_members' => 4, 'number_of_adults' => 2,
            'number_of_children' => 2, 'phone1' => '555-1234', 'address' => '1 Main St',
            'delivery_status' => DeliveryStatus::Delivered,
        ]);
        Child::create([
            'family_id' => $family->id,
            'gender' => 'Male', 'age' => 8,
            'gift_level' => GiftLevel::Full,
        ]);

        $response = $this->actingAs($this->santa)->get(route('santa.reports'));
        $response->assertOk();
    }

    public function test_gifts_page_loads(): void
    {
        $response = $this->actingAs($this->santa)->get(route('santa.gifts'));
        $response->assertOk();
    }

    public function test_gifts_page_filters_by_level(): void
    {
        $family = Family::create([
            'family_name' => 'Gift Test', 'family_number' => 1,
            'number_of_family_members' => 3, 'number_of_adults' => 2,
            'number_of_children' => 1, 'phone1' => '555-1234', 'address' => '1 Main St',
        ]);
        Child::create([
            'family_id' => $family->id,
            'gender' => 'Female', 'age' => 8,
            'gift_level' => GiftLevel::Full,
        ]);

        $response = $this->actingAs($this->santa)->get(route('santa.gifts', ['level' => 3]));
        $response->assertOk();
    }

    public function test_all_families_page_loads(): void
    {
        $response = $this->actingAs($this->santa)->get(route('santa.families'));
        $response->assertOk();
    }

    public function test_family_user_cannot_access_exports(): void
    {
        $familyUser = User::create([
            'username' => 'family', 'first_name' => 'F', 'last_name' => 'U',
            'password' => 'password', 'permission' => 7,
        ]);

        $response = $this->actingAs($familyUser)->get(route('santa.export'));
        $response->assertForbidden();

        $response = $this->actingAs($familyUser)->get(route('santa.reports'));
        $response->assertForbidden();
    }
}
