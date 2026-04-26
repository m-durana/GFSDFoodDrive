<?php

namespace Tests\Feature;

use App\Models\ShoppingAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_form_submits_are_throttled(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post(route('self-service.store'), [])->assertStatus(403);
        }

        $this->post(route('self-service.store'), [])->assertStatus(429);
    }

    public function test_public_shopping_token_writes_are_throttled(): void
    {
        $assignment = ShoppingAssignment::create([
            'ninja_name' => 'Rate Test',
            'split_type' => 'family_range',
            'family_start' => 1,
            'family_end' => 10,
        ]);

        for ($i = 0; $i < 20; $i++) {
            $this->postJson("/api/shopping/{$assignment->token}/check", [
                'item_key' => 'Not On The List',
                'ninja_name' => 'Nina',
            ])->assertUnprocessable();
        }

        $this->postJson("/api/shopping/{$assignment->token}/check", [
            'item_key' => 'Not On The List',
            'ninja_name' => 'Nina',
        ])->assertStatus(429);
    }

    public function test_family_status_public_reads_are_throttled(): void
    {
        $family = \App\Models\Family::create([
            'family_name' => 'Rate Limit',
            'family_number' => 300,
            'number_of_family_members' => 1,
            'number_of_adults' => 1,
            'number_of_children' => 0,
            'address' => '1 Test St',
            'phone1' => '555-0100',
            'status_token' => 'status-token',
        ]);

        \App\Models\Setting::set('family_status_enabled', '1');

        for ($i = 0; $i < 60; $i++) {
            $this->get(route('family.status', $family->status_token))->assertOk();
        }

        $this->get(route('family.status', $family->status_token))->assertStatus(429);
    }
}
