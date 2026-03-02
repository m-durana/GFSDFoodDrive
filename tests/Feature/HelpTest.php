<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HelpTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::create([
            'username' => 'helper', 'first_name' => 'H', 'last_name' => 'U',
            'password' => 'password', 'permission' => 8,
        ]);
    }

    public function test_help_index_loads(): void
    {
        $response = $this->actingAs($this->user)->get(route('help.index'));
        $response->assertOk();
    }

    public function test_help_topic_loads(): void
    {
        $response = $this->actingAs($this->user)->get(route('help.show', 'getting-started'));
        $response->assertOk();
    }

    public function test_help_invalid_topic_404(): void
    {
        $response = $this->actingAs($this->user)->get(route('help.show', 'nonexistent-topic'));
        $response->assertNotFound();
    }

    public function test_guest_cannot_access_help(): void
    {
        $response = $this->get(route('help.index'));
        $response->assertRedirect(route('login'));
    }
}
