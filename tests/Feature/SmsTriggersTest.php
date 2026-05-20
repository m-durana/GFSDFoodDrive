<?php

namespace Tests\Feature;

use App\Enums\DeliveryStatus;
use App\Jobs\SendSmsJob;
use App\Models\DeliveryRoute;
use App\Models\Family;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\DeliveryWindowSet;
use App\Notifications\DriverRouteReady;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

/**
 * REL-05: trigger wiring for the SMS jobs that already exist.
 * Asserts dispatch happens at the right hook points.
 */
class SmsTriggersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Enable SMS for these tests.
        Setting::set('sms_enabled', '1');
        Setting::set('twilio_sid', 'AC_test');
        Setting::set('twilio_token', 'test');
        Setting::set('twilio_from', '+15555550000');
        Setting::clearCache();
    }

    public function test_delivery_window_set_dispatches_sms_when_date_first_set(): void
    {
        Bus::fake();

        $family = Family::create([
            'family_name' => 'Window Test', 'family_number' => 1,
            'number_of_family_members' => 2, 'number_of_adults' => 1,
            'number_of_children' => 1, 'phone1' => '5550001111', 'address' => '1 A St',
        ]);

        // First set of delivery_date should fire.
        $family->update(['delivery_date' => 'December 18']);
        Bus::assertDispatched(SendSmsJob::class);
    }

    public function test_delivery_window_does_not_fire_when_already_set(): void
    {
        $family = Family::create([
            'family_name' => 'Window Test 2', 'family_number' => 2,
            'number_of_family_members' => 2, 'number_of_adults' => 1,
            'number_of_children' => 1, 'phone1' => '5550002222', 'address' => '2 A St',
            'delivery_date' => 'December 18',
        ]);

        Bus::fake();
        $family->update(['delivery_date' => 'December 19']); // Changing — not first-time set.
        Bus::assertNotDispatched(SendSmsJob::class);
    }

    public function test_driver_route_ready_dispatches_when_phone_set(): void
    {
        Bus::fake();
        $route = DeliveryRoute::create([
            'name' => 'Test Route',
            'driver_name' => 'Pat',
            'driver_phone' => '5550009999',
            'stop_count' => 3,
        ]);
        DriverRouteReady::send($route);
        Bus::assertDispatched(SendSmsJob::class);
        $this->assertNotNull($route->fresh()->driver_notified_at);
    }

    public function test_driver_route_ready_skips_when_no_phone(): void
    {
        Bus::fake();
        $route = DeliveryRoute::create([
            'name' => 'No Phone Route',
            'driver_name' => 'Pat',
        ]);
        $ok = DriverRouteReady::send($route);
        $this->assertFalse($ok);
        Bus::assertNotDispatched(SendSmsJob::class);
    }
}
