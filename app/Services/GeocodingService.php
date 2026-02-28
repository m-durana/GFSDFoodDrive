<?php

namespace App\Services;

use App\Models\Family;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    /**
     * Geocode all families that have an address but no coordinates.
     *
     * @return array{geocoded: int, errors: int}
     */
    /**
     * @param int $batchSize Max families to geocode in one call (0 = unlimited)
     * @return array{geocoded: int, errors: int, remaining: int, total: int}
     */
    public function geocodeAll(int $batchSize = 0): array
    {
        set_time_limit(0);

        $total = Family::geocodeable()->count();
        $query = Family::geocodeable();
        if ($batchSize > 0) {
            $query->limit($batchSize);
        }
        $families = $query->get();

        $geocoded = 0;
        $errors = 0;
        $orsKey = Setting::get('openrouteservice_key');

        foreach ($families as $family) {
            $coords = $this->geocodeAddress($family->address, $orsKey);

            if ($coords) {
                $family->update([
                    'latitude' => $coords['lat'],
                    'longitude' => $coords['lng'],
                ]);
                $geocoded++;
            } else {
                $errors++;
            }

            // Rate limit: 1 req/sec for Nominatim
            usleep(1100000);
        }

        $remaining = Family::geocodeable()->count();

        return ['geocoded' => $geocoded, 'errors' => $errors, 'remaining' => $remaining, 'total' => $total];
    }

    /**
     * Normalize an address for geocoding.
     */
    protected function normalizeAddress(string $address): string
    {
        // Remove apartment/unit/suite designations
        $address = preg_replace('/\b(apt|apartment|unit|suite|ste|#)\s*\.?\s*\w*/i', '', $address);

        // Remove extra info in parentheses
        $address = preg_replace('/\([^)]*\)/', '', $address);

        // Normalize common abbreviations
        $address = preg_replace('/\bSt\b\.?/i', 'Street', $address);
        $address = preg_replace('/\bAve\b\.?/i', 'Avenue', $address);
        $address = preg_replace('/\bDr\b\.?/i', 'Drive', $address);
        $address = preg_replace('/\bLn\b\.?/i', 'Lane', $address);
        $address = preg_replace('/\bCt\b\.?/i', 'Court', $address);
        $address = preg_replace('/\bBlvd\b\.?/i', 'Boulevard', $address);
        $address = preg_replace('/\bPl\b\.?/i', 'Place', $address);
        $address = preg_replace('/\bRd\b\.?/i', 'Road', $address);

        // Collapse whitespace
        $address = preg_replace('/\s+/', ' ', trim($address));

        // Append state if not present (Granite Falls is in WA)
        if (!preg_match('/\b(WA|Washington)\b/i', $address) && !preg_match('/\b\d{5}\b/', $address)) {
            $address .= ', WA';
        }

        return $address;
    }

    /**
     * Try to geocode a single address using Nominatim, falling back to ORS.
     */
    public function geocodeAddress(string $address, ?string $orsKey = null): ?array
    {
        $normalizedAddress = $this->normalizeAddress($address);

        // Try Nominatim first (free, no key needed)
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'GFSDFoodDrive/1.0',
            ])->timeout(10)->get('https://nominatim.openstreetmap.org/search', [
                'q' => $normalizedAddress,
                'format' => 'json',
                'limit' => 1,
                'countrycodes' => 'us',
            ]);

            if ($response->successful() && count($response->json()) > 0) {
                $result = $response->json()[0];
                return ['lat' => (float) $result['lat'], 'lng' => (float) $result['lon']];
            }
        } catch (\Exception $e) {
            Log::warning("Geocoding: Nominatim error for '{$normalizedAddress}': {$e->getMessage()}");
        }

        // Try OpenRouteService geocoding if key available
        if ($orsKey) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => $orsKey,
                ])->timeout(10)->get('https://api.openrouteservice.org/geocode/search', [
                    'text' => $normalizedAddress,
                    'size' => 1,
                    'boundary.country' => 'US',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $features = $data['features'] ?? [];
                    if (count($features) > 0) {
                        $coords = $features[0]['geometry']['coordinates'] ?? null;
                        if ($coords) {
                            return ['lat' => (float) $coords[1], 'lng' => (float) $coords[0]];
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Geocoding: ORS error for '{$normalizedAddress}': {$e->getMessage()}");
            }
        }

        Log::info("Geocoding: Failed to geocode '{$address}' (normalized: '{$normalizedAddress}')");
        return null;
    }
}
