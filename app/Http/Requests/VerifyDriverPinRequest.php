<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyDriverPinRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Public token-authenticated endpoint: route binding + token check
        // happens in the controller (firstOrFail on access_token).
        return true;
    }

    public function rules(): array
    {
        return [
            'pin' => ['required', 'digits:6'],
        ];
    }
}
