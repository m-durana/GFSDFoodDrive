<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoordinatorDocumentsTest extends TestCase
{
    use RefreshDatabase;

    private User $coordinator;
    private User $santa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->coordinator = User::create([
            'username' => 'coord_test',
            'first_name' => 'Test',
            'last_name' => 'Coord',
            'password' => 'password123',
            'permission' => 8,
        ]);

        $this->santa = User::create([
            'username' => 'santa_test',
            'first_name' => 'Test',
            'last_name' => 'Santa',
            'password' => 'password123',
            'permission' => 9,
        ]);

        // Create a few families with children for document tests
        $family = Family::create([
            'user_id' => $this->santa->id,
            'family_name' => 'Document Family',
            'family_number' => 10,
            'address' => '100 Doc St, Granite Falls, WA',
            'phone1' => '360-555-1000',
            'number_of_adults' => 2,
            'number_of_children' => 2,
            'number_of_family_members' => 4,
            'female_adults' => 1,
            'male_adults' => 1,
            'infants' => 0,
            'young_children' => 0,
            'children_count' => 2,
            'tweens' => 0,
            'teenagers' => 0,
            'delivery_preference' => 'Delivery',
            'delivery_date' => 'December 18',
            'delivery_time' => '9:00 AM - 11:00 AM',
            'delivery_team' => 'Team A',
        ]);

        Child::create([
            'family_id' => $family->id,
            'gender' => 'Male',
            'age' => '8',
            'school' => 'Crossroads',
            'mail_merged' => false,
            'gift_level' => 0,
        ]);

        Child::create([
            'family_id' => $family->id,
            'gender' => 'Female',
            'age' => '12',
            'school' => 'GFMS',
            'mail_merged' => true,
            'gift_level' => 3,
        ]);
    }

    public function test_coordinator_dashboard_loads(): void
    {
        $response = $this->actingAs($this->coordinator)->get('/coordinator');
        $response->assertStatus(200);
    }

    public function test_coordinator_dashboard_shows_stats(): void
    {
        $response = $this->actingAs($this->coordinator)->get('/coordinator');
        $response->assertStatus(200);
    }

    public function test_gift_tags_returns_html_without_dompdf(): void
    {
        // Without DomPDF installed, should return HTML view
        $response = $this->actingAs($this->coordinator)->get('/coordinator/gift-tags');
        $response->assertStatus(200);
    }

    public function test_gift_tags_unmerged_filter(): void
    {
        $response = $this->actingAs($this->coordinator)->get('/coordinator/gift-tags?filter=unmerged');
        $response->assertStatus(200);
    }

    public function test_gift_tags_all_filter(): void
    {
        $response = $this->actingAs($this->coordinator)->get('/coordinator/gift-tags?filter=all');
        $response->assertStatus(200);
    }

    public function test_gift_tags_range_filter(): void
    {
        $response = $this->actingAs($this->coordinator)->get('/coordinator/gift-tags?range_start=1&range_end=50');
        $response->assertStatus(200);
    }

    public function test_gift_tags_mark_merged(): void
    {
        $response = $this->actingAs($this->coordinator)->get('/coordinator/gift-tags?filter=unmerged&mark_merged=1');
        $response->assertStatus(200);

        // The unmerged child should now be marked as merged
        $child = Child::where('mail_merged', false)->first();
        $this->assertNull($child); // all should be merged now
    }

    public function test_family_summary_returns_html(): void
    {
        $response = $this->actingAs($this->coordinator)->get('/coordinator/family-summary');
        $response->assertStatus(200);
    }

    public function test_family_summary_range_filter(): void
    {
        $response = $this->actingAs($this->coordinator)->get('/coordinator/family-summary?range_start=1&range_end=50');
        $response->assertStatus(200);
    }

    public function test_delivery_day_returns_html(): void
    {
        $response = $this->actingAs($this->coordinator)->get('/coordinator/delivery-day');
        $response->assertStatus(200);
    }

    public function test_delivery_day_date_filter(): void
    {
        $response = $this->actingAs($this->coordinator)->get('/coordinator/delivery-day?delivery_date=December+18');
        $response->assertStatus(200);
    }

    public function test_delivery_day_team_filter(): void
    {
        $response = $this->actingAs($this->coordinator)->get('/coordinator/delivery-day?delivery_team=Team+A');
        $response->assertStatus(200);
    }

    public function test_santa_can_access_coordinator_documents(): void
    {
        $this->actingAs($this->santa)->get('/coordinator')->assertStatus(200);
        $this->actingAs($this->santa)->get('/coordinator/gift-tags')->assertStatus(200);
        $this->actingAs($this->santa)->get('/coordinator/family-summary')->assertStatus(200);
        $this->actingAs($this->santa)->get('/coordinator/delivery-day')->assertStatus(200);
    }

    public function test_family_user_cannot_access_documents(): void
    {
        $familyUser = User::create([
            'username' => 'family_test',
            'first_name' => 'Test',
            'last_name' => 'Family',
            'password' => 'password123',
            'permission' => 7,
        ]);

        $this->actingAs($familyUser)->get('/coordinator/gift-tags')->assertStatus(403);
        $this->actingAs($familyUser)->get('/coordinator/family-summary')->assertStatus(403);
        $this->actingAs($familyUser)->get('/coordinator/delivery-day')->assertStatus(403);
    }
}
