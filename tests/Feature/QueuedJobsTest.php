<?php

namespace Tests\Feature;

use App\Jobs\RefreshRouteGeometryJob;
use App\Jobs\SendSmsJob;
use App\Models\DeliveryRoute;
use App\Models\Family;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Wave 3 — verify external/long-running work is dispatched to the queue
 * rather than executed synchronously on the request thread.
 */
class QueuedJobsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sms_dispatch_pushes_send_sms_job_when_twilio_configured(): void
    {
        Bus::fake([SendSmsJob::class]);

        Setting::set('sms_enabled', '1');
        Setting::set('twilio_sid', 'AC' . str_repeat('a', 32));
        Setting::set('twilio_token', str_repeat('b', 32));
        Setting::set('twilio_from', '+15005550006');

        \App\Services\SmsService::dispatch('5555550000', 'hello');

        Bus::assertDispatched(SendSmsJob::class, function (SendSmsJob $job) {
            return $job->to === '+15555550000' && $job->message === 'hello';
        });
    }

    public function test_sms_dispatch_no_op_when_disabled(): void
    {
        Bus::fake([SendSmsJob::class]);
        Setting::set('sms_enabled', '0');

        \App\Services\SmsService::dispatch('5555550000', 'hello');

        Bus::assertNotDispatched(SendSmsJob::class);
    }

    public function test_add_families_to_route_dispatches_refresh_geometry_job(): void
    {
        Bus::fake([RefreshRouteGeometryJob::class]);

        $santa = User::create([
            'username' => 'q_santa',
            'first_name' => 'Q',
            'last_name' => 'S',
            'password' => 'password123',
            'permission' => 9,
        ]);
        if (class_exists(\Spatie\Permission\Models\Role::class)
            && method_exists($santa, 'syncRoles')) {
            $santa->syncRoles(['santa']);
        }

        $route = DeliveryRoute::create([
            'name' => 'Q Route',
            'season_year' => date('Y'),
        ]);

        $family = Family::create([
            'family_number' => 7771,
            'family_name' => 'Q Family',
            'address' => '7 Test St',
            'phone1' => '5555557777',
            'season_year' => date('Y'),
        ]);

        $response = $this->actingAs($santa)
            ->post("/delivery-day/routes/{$route->id}/add-families", [
                'family_ids' => [$family->id],
            ]);

        $response->assertSuccessful();
        Bus::assertDispatched(RefreshRouteGeometryJob::class);
    }
}
