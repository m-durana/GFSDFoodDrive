<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ToggleShoppingItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Token-authenticated public endpoint (ShoppingAssignment::token).
        return true;
    }

    public function rules(): array
    {
        return [
            'item_key' => ['required', 'string', 'max:255'],
            'ninja_name' => ['required', 'string', 'max:255'],
        ];
    }
}
