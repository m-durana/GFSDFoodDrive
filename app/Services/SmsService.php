<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Check if SMS is configured and enabled.
     */
    public static function isAvailable(): bool
    {
        return Setting::get('sms_enabled', '0') === '1'
            && ! empty(Setting::get('twilio_sid'))
            && ! empty(Setting::get('twilio_token'))
            && ! empty(Setting::get('twilio_from'));
    }

    /**
     * Send an SMS message via Twilio.
     */
    public static function send(string $to, string $message): bool
    {
        if (! static::isAvailable()) {
            return false;
        }

        // Normalize phone number
        $to = static::normalizePhone($to);
        if (! $to) {
            return false;
        }

        try {
            $client = new \Twilio\Rest\Client(
                Setting::get('twilio_sid'),
                Setting::get('twilio_token')
            );

            $client->messages->create($to, [
                'from' => Setting::get('twilio_from'),
                'body' => $message,
            ]);

            Log::info("SMS sent to {$to}");
            return true;
        } catch (\Exception $e) {
            Log::error("SMS failed to {$to}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Normalize a phone number to E.164 format for US numbers.
     */
    public static function normalizePhone(?string $phone): ?string
    {
        if (empty($phone)) return null;

        // Strip everything except digits
        $digits = preg_replace('/\D/', '', $phone);

        // Handle US numbers
        if (strlen($digits) === 10) {
            return '+1' . $digits;
        }
        if (strlen($digits) === 11 && str_starts_with($digits, '1')) {
            return '+' . $digits;
        }

        // Already has country code
        if (strlen($digits) >= 11) {
            return '+' . $digits;
        }

        return null; // Invalid
    }
}
