<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubstitutePackingItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'notes' => 'required|string|max:500',
            'new_item_id' => 'nullable|integer|exists:warehouse_items,id',
        ];
    }
}
