<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClaimAdoptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Public endpoint; the controller checks the adopt_a_tag_enabled
        // feature flag explicitly (abort 404 if off).
        return true;
    }

    public function rules(): array
    {
        return [
            'adopter_name' => ['required', 'string', 'max:255'],
            'adopter_email' => ['required', 'email', 'max:255'],
            'adopter_phone' => ['nullable', 'string', 'max:255'],
        ];
    }
}
