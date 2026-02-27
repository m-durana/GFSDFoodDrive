<?php

namespace App\Services;

use App\Models\Family;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class GeocodingService
{
    /**
     * Geocode all families that have an address but no coordinates.
     *
     * @return array{geocoded: int, errors: int}
     */
    public function geocodeAll(): array
    {
        $families = Family::geocodeable()->get();
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

        return ['geocoded' => $geocoded, 'errors' => $errors];
    }

    /**
     * Try to geocode a single address using Nominatim, falling back to ORS.
     */
    public function geocodeAddress(string $address, ?string $orsKey = null): ?array
    {
        // Try Nominatim first (free, no key needed)
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'GFSDFoodDrive/1.0',
            ])->timeout(10)->get('https://nominatim.openstreetmap.org/search', [
                'q' => $address,
                'format' => 'json',
                'limit' => 1,
                'countrycodes' => 'us',
            ]);

            if ($response->successful() && count($response->json()) > 0) {
                $result = $response->json()[0];
                return ['lat' => (float) $result['lat'], 'lng' => (float) $result['lon']];
            }
        } catch (\Exception $e) {
            // Fall through to ORS
        }

        // Try OpenRouteService geocoding if key available
        if ($orsKey) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => $orsKey,
                ])->timeout(10)->get('https://api.openrouteservice.org/geocode/search', [
                    'text' => $address,
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
                // Fall through
            }
        }

        return null;
    }
}
