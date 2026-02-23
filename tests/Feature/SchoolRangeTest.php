<?php

namespace Tests\Feature;

use App\Models\SchoolRange;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolRangeTest extends TestCase
{
    use RefreshDatabase;

    private User $santa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->santa = User::create([
            'username' => 'santa_test',
            'first_name' => 'Test',
            'last_name' => 'Santa',
            'password' => 'password123',
            'permission' => 9,
        ]);
    }

    public function test_school_ranges_page_loads(): void
    {
        $response = $this->actingAs($this->santa)->get('/santa/school-ranges');
        $response->assertStatus(200);
    }

    public function test_school_range_can_be_created(): void
    {
        $response = $this->actingAs($this->santa)->post('/santa/school-ranges', [
            'school_name' => 'New School',
            'range_start' => 600,
            'range_end' => 699,
            'sort_order' => 7,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('school_ranges', [
            'school_name' => 'New School',
            'range_start' => 600,
            'range_end' => 699,
        ]);
    }

    public function test_school_range_requires_name(): void
    {
        $response = $this->actingAs($this->santa)->post('/santa/school-ranges', [
            'range_start' => 600,
            'range_end' => 699,
        ]);

        $response->assertSessionHasErrors('school_name');
    }

    public function test_range_end_must_be_greater_than_start(): void
    {
        $response = $this->actingAs($this->santa)->post('/santa/school-ranges', [
            'school_name' => 'Bad Range',
            'range_start' => 100,
            'range_end' => 50,
        ]);

        $response->assertSessionHasErrors('range_end');
    }

    public function test_school_range_can_be_updated(): void
    {
        $range = SchoolRange::create([
            'school_name' => 'Old Name',
            'range_start' => 100,
            'range_end' => 199,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->santa)->put("/santa/school-ranges/{$range->id}", [
            'school_name' => 'Updated Name',
            'range_start' => 100,
            'range_end' => 249,
            'sort_order' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('school_ranges', [
            'id' => $range->id,
            'school_name' => 'Updated Name',
            'range_end' => 249,
        ]);
    }

    public function test_school_range_can_be_deleted(): void
    {
        $range = SchoolRange::create([
            'school_name' => 'Delete Me',
            'range_start' => 700,
            'range_end' => 799,
            'sort_order' => 8,
        ]);

        $response = $this->actingAs($this->santa)->delete("/santa/school-ranges/{$range->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('school_ranges', ['id' => $range->id]);
    }
}
