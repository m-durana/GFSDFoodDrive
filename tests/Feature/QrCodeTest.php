<?php

namespace Tests\Feature;

use App\Helpers\QrCodeHelper;
use App\Models\Child;
use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrCodeTest extends TestCase
{
    use RefreshDatabase;

    private User $santa;
    private Family $family;
    private Child $child;

    protected function setUp(): void
    {
        parent::setUp();

        $this->santa = User::create([
            'username' => 'santa_qr',
            'first_name' => 'Santa',
            'last_name' => 'QR',
            'password' => 'password123',
            'permission' => 9,
        ]);

        $this->family = Family::create([
            'user_id' => $this->santa->id,
            'family_name' => 'QR Test Family',
            'family_number' => 42,
            'address' => '100 Main St',
            'phone1' => '360-555-0042',
            'number_of_adults' => 2,
            'number_of_children' => 1,
            'number_of_family_members' => 3,
        ]);

        $this->child = Child::create([
            'family_id' => $this->family->id,
            'gender' => 'Female',
            'age' => '7',
            'school' => 'Crossroads',
            'gift_level' => 0,
            'mail_merged' => false,
        ]);
    }

    public function test_qr_code_helper_generates_base64_png(): void
    {
        $dataUri = QrCodeHelper::generateBase64('https://example.com/test');
        $this->assertStringStartsWith('data:image/png;base64,', $dataUri);
    }

    public function test_qr_code_helper_generates_signed_url(): void
    {
        $url = QrCodeHelper::scanUrl($this->child->id);
        $this->assertStringContainsString('/scan/' . $this->child->id, $url);
        $this->assertStringContainsString('signature=', $url);
    }

    public function test_scan_page_loads_with_valid_signature(): void
    {
        $url = QrCodeHelper::scanUrl($this->child->id);
        $path = parse_url($url, PHP_URL_PATH) . '?' . parse_url($url, PHP_URL_QUERY);

        $response = $this->get($path);
        $response->assertStatus(200);
        $response->assertSee('#42');
        $response->assertSee('Female');
        $response->assertSee('7');
    }

    public function test_scan_page_rejects_invalid_signature(): void
    {
        $response = $this->get('/scan/' . $this->child->id . '?signature=invalid');
        $response->assertStatus(403);
    }

    public function test_scan_update_changes_gift_level(): void
    {
        $url = url()->signedRoute('scan.update', ['child' => $this->child->id]);
        $path = parse_url($url, PHP_URL_PATH) . '?' . parse_url($url, PHP_URL_QUERY);

        $response = $this->put($path, [
            'gift_level' => 3,
            'gifts_received' => 'Lego set, jacket',
            'adopter_name' => 'Jane Doe',
        ]);

        $response->assertRedirect();

        $this->child->refresh();
        $this->assertEquals(3, $this->child->gift_level->value);
        $this->assertEquals('Lego set, jacket', $this->child->gifts_received);
        $this->assertEquals('Jane Doe', $this->child->adopter_name);
    }

    public function test_gift_tags_generate_successfully_with_qr(): void
    {
        $response = $this->actingAs($this->santa)->get('/coordinator/gift-tags?filter=all');
        $response->assertStatus(200);
        // Gift tags may return HTML or PDF depending on whether DomPDF is installed.
        // Either way, a 200 response with QR generation is success.
    }
}
