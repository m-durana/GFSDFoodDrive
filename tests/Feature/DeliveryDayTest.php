<?php

namespace Tests\Feature;

use App\Enums\DeliveryStatus;
use App\Models\DeliveryLog;
use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryDayTest extends TestCase
{
    use RefreshDatabase;

    private User $santa;
    private Family $family;

    protected function setUp(): void
    {
        parent::setUp();
        $this->santa = User::create([
            'username' => 'santa', 'first_name' => 'S', 'last_name' => 'C',
            'password' => 'password', 'permission' => 9,
        ]);
        $this->family = Family::create([
            'family_name' => 'Delivery Test', 'family_number' => 1,
            'number_of_family_members' => 3, 'number_of_adults' => 2,
            'number_of_children' => 1, 'address' => '123 Main St', 'phone1' => '555-1234',
            'delivery_preference' => 'Delivery', 'delivery_status' => DeliveryStatus::Pending,
        ]);
    }

    public function test_delivery_day_page_loads(): void
    {
        $response = $this->actingAs($this->santa)->get(route('delivery.index'));
        $response->assertOk();
        $response->assertSee('Delivery Day');
        $response->assertSee('Delivery Test');
    }

    public function test_delivery_status_can_be_updated(): void
    {
        $response = $this->actingAs($this->santa)->put(route('delivery.updateStatus', $this->family), [
            'delivery_status' => 'in_transit',
        ]);
        $response->assertRedirect();
        $this->assertEquals(DeliveryStatus::InTransit, $this->family->fresh()->delivery_status);
        $this->assertDatabaseHas('delivery_logs', [
            'family_id' => $this->family->id,
            'status' => 'in_transit',
        ]);
    }

    public function test_delivery_team_can_be_assigned(): void
    {
        $response = $this->actingAs($this->santa)->put(route('delivery.updateTeam', $this->family), [
            'delivery_team' => 'Team Alpha',
        ]);
        $response->assertRedirect();
        $this->assertEquals('Team Alpha', $this->family->fresh()->delivery_team);
    }

    public function test_delivery_log_can_be_added(): void
    {
        $response = $this->actingAs($this->santa)->post(route('delivery.addLog', $this->family), [
            'status' => 'no_answer',
            'notes' => 'Nobody home at 2pm',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('delivery_logs', [
            'family_id' => $this->family->id,
            'status' => 'no_answer',
            'notes' => 'Nobody home at 2pm',
            'user_id' => $this->santa->id,
        ]);
    }

    public function test_delivered_log_updates_family_status(): void
    {
        $this->actingAs($this->santa)->post(route('delivery.addLog', $this->family), [
            'status' => 'delivered',
            'notes' => 'Left with neighbor',
        ]);
        $this->assertEquals(DeliveryStatus::Delivered, $this->family->fresh()->delivery_status);
    }

    public function test_attempted_log_sets_in_transit(): void
    {
        $this->actingAs($this->santa)->post(route('delivery.addLog', $this->family), [
            'status' => 'attempted',
        ]);
        $this->assertEquals(DeliveryStatus::InTransit, $this->family->fresh()->delivery_status);
    }

    public function test_logs_page_loads(): void
    {
        DeliveryLog::create([
            'family_id' => $this->family->id,
            'user_id' => $this->santa->id,
            'status' => 'delivered',
            'notes' => 'Test log entry',
        ]);

        $response = $this->actingAs($this->santa)->get(route('delivery.logs'));
        $response->assertOk();
        $response->assertSee('Delivery Logs');
        $response->assertSee('Test log entry');
    }

    public function test_filter_by_team(): void
    {
        $this->family->update(['delivery_team' => 'Team A']);
        Family::create([
            'family_name' => 'Other Family', 'family_number' => 2,
            'number_of_family_members' => 2, 'number_of_adults' => 1,
            'number_of_children' => 1, 'address' => '456 Oak St', 'phone1' => '555-5678',
            'delivery_team' => 'Team B',
        ]);

        $response = $this->actingAs($this->santa)->get(route('delivery.index', ['team' => 'Team A']));
        $response->assertOk();
        $response->assertSee('Delivery Test');
        $response->assertDontSee('Other Family');
    }

    public function test_stats_shown_on_delivery_page(): void
    {
        $response = $this->actingAs($this->santa)->get(route('delivery.index'));
        $response->assertOk();
        $response->assertSee('Total Families');
        $response->assertSee('Pending');
    }
}
