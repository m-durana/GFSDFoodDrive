<?php

namespace App\Helpers;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QrCodeHelper
{
    /**
     * Generate a base64-encoded PNG QR code image for a given URL.
     * Returns a data URI suitable for embedding in <img> tags.
     */
    public static function generateBase64(string $url, int $scale = 3): string
    {
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'scale' => $scale,
            'imageBase64' => true,
            'quietzoneSize' => 1,
        ]);

        return (new QRCode($options))->render($url);
    }

    /**
     * Generate a signed scan URL for a child.
     */
    public static function scanUrl(int $childId): string
    {
        return url()->signedRoute('scan.show', ['child' => $childId]);
    }
}
