<?php

namespace Tests\Unit;

use App\Services\WarehouseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WarehouseServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_off_lookup_uses_tls_verification_enabled_client(): void
    {
        $source = file_get_contents(app_path('Services/WarehouseService.php'));

        $this->assertStringNotContainsString('withoutVerifying', $source);
    }

    public function test_off_lookup_still_reads_successful_product_response(): void
    {
        Http::fake([
            'world.openfoodfacts.org/api/v0/product/*' => Http::response([
                'status' => 1,
                'product' => [
                    'product_name' => 'Test Cereal',
                    'brands' => 'Acme',
                    'categories_tags' => ['en:breakfast-cereals'],
                ],
            ]),
        ]);

        $result = app(WarehouseService::class)->lookupBarcodeExternal('12345678905');

        $this->assertSame('Test Cereal', $result['name']);
        $this->assertSame('Acme', $result['brand']);
    }
}
