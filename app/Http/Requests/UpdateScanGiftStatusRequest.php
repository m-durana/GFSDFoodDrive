<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateScanGiftStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Signed-route-authenticated endpoint; signing is enforced via
        // route middleware (signed).
        return true;
    }

    public function rules(): array
    {
        return [
            'gift_level' => ['required', 'integer', 'in:0,1,2,3'],
            'gifts_received' => ['nullable', 'string', 'max:1000'],
            'adopter_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
