<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateShoppingAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && method_exists($this->user(), 'isSanta') && $this->user()->isSanta();
    }

    public function rules(): array
    {
        $rules = [
            'user_id' => ['nullable', 'exists:users,id'],
            'ninja_name' => ['required_without:user_id', 'nullable', 'string', 'max:255'],
            'split_type' => ['required', 'string', 'in:family_range,category,deficit,smart_split,subcategory'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        // Conditional validation based on split_type
        if ($this->input('split_type') === 'family_range') {
            $rules['family_start'] = ['required', 'integer', 'min:1'];
            $rules['family_end'] = ['required', 'integer', 'min:1'];
        } elseif ($this->input('split_type') === 'category') {
            $rules['categories'] = ['required', 'array', 'min:1'];
            $rules['categories.*'] = ['string'];
        } elseif ($this->input('split_type') === 'smart_split') {
            $rules['num_shoppers'] = ['required', 'integer', 'min:2', 'max:10'];
        } elseif ($this->input('split_type') === 'subcategory') {
            $rules['subcategory_category'] = ['required', 'string'];
            $rules['subcategory_items'] = ['required', 'array', 'min:1'];
            $rules['subcategory_items.*'] = ['integer', 'exists:grocery_items,id'];
        }

        return $rules;
    }
}
