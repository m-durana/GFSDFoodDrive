<?php

namespace Tests\Feature;

use App\Enums\DeliveryLogStatus;
use App\Models\DeliveryLog;
use App\Models\Family;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryLogStatusEnumTest extends TestCase
{
    use RefreshDatabase;

    public function test_enum_round_trips_through_eloquent_cast(): void
    {
        $family = Family::create([
            'family_number' => 8881,
            'family_name' => 'Enum Family',
            'address' => '1 Test St',
            'phone1' => '5555550001',
            'season_year' => date('Y'),
        ]);

        $log = DeliveryLog::create([
            'family_id' => $family->id,
            'status' => DeliveryLogStatus::Delivered,
            'notes' => 'enum write',
        ]);

        $fresh = DeliveryLog::find($log->id);
        $this->assertInstanceOf(DeliveryLogStatus::class, $fresh->status);
        $this->assertSame(DeliveryLogStatus::Delivered, $fresh->status);
    }

    public function test_string_writes_coerce_to_enum_case_on_read(): void
    {
        $family = Family::create([
            'family_number' => 8882,
            'family_name' => 'Enum Family 2',
            'address' => '2 Test St',
            'phone1' => '5555550002',
            'season_year' => date('Y'),
        ]);

        $log = DeliveryLog::create([
            'family_id' => $family->id,
            'status' => 'in_transit',
        ]);

        $this->assertSame(DeliveryLogStatus::InTransit, $log->fresh()->status);
    }

    public function test_invalid_status_string_throws_on_read(): void
    {
        $family = Family::create([
            'family_number' => 8883,
            'family_name' => 'Enum Family 3',
            'address' => '3 Test St',
            'phone1' => '5555550003',
            'season_year' => date('Y'),
        ]);

        // Bypass cast on write
        \Illuminate\Support\Facades\DB::table('delivery_logs')->insert([
            'family_id' => $family->id,
            'status' => 'not_a_real_status',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(\ValueError::class);
        DeliveryLog::orderBy('id', 'desc')->first()->status;
    }

    public function test_addlog_validator_rejects_unknown_status(): void
    {
        // Set up a santa user to make addLog request
        $user = \App\Models\User::create([
            'username' => 'enum_santa',
            'first_name' => 'E',
            'last_name' => 'S',
            'password' => 'password123',
            'permission' => 9,
        ]);
        if (class_exists(\Spatie\Permission\Models\Role::class)
            && method_exists($user, 'syncRoles')) {
            $user->syncRoles(['santa']);
        }

        $family = Family::create([
            'family_number' => 8884,
            'family_name' => 'Enum Family 4',
            'address' => '4 Test St',
            'phone1' => '5555550004',
            'season_year' => date('Y'),
        ]);

        $response = $this->actingAs($user)
            ->from('/delivery-day')
            ->post("/delivery-day/{$family->id}/log", [
                'status' => 'invalid_status_xyz',
                'notes' => 'should reject',
            ]);

        $response->assertSessionHasErrors('status');
    }
}
