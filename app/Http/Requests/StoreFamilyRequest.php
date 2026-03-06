<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;

class StoreFamilyRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Allow unauthenticated self-service registration when enabled
        if (!$this->user() && Setting::get('self_registration_enabled', '0') === '1') {
            return true;
        }

        if (!$this->user()) {
            return false;
        }

        return $this->user()->isFamily() || $this->user()->isCoordinator() || $this->user()->isSanta();
    }

    public function rules(): array
    {
        return [
            'family_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'phone1' => ['required', 'string', 'max:50'],
            'phone2' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'preferred_language' => ['nullable', 'string', 'in:English,Spanish,Other'],
            'female_adults' => ['required', 'integer', 'min:0', 'max:50'],
            'male_adults' => ['required', 'integer', 'min:0', 'max:50'],
            'other_adults' => ['required', 'integer', 'min:0', 'max:50'],
            'infants' => ['required', 'integer', 'min:0', 'max:50'],
            'young_children' => ['required', 'integer', 'min:0', 'max:50'],
            'children_count' => ['required', 'integer', 'min:0', 'max:50'],
            'tweens' => ['required', 'integer', 'min:0', 'max:50'],
            'teenagers' => ['required', 'integer', 'min:0', 'max:50'],
            'has_crhs_children' => ['boolean'],
            'has_gfhs_children' => ['boolean'],
            'needs_baby_supplies' => ['boolean'],
            'pet_information' => ['nullable', 'string', 'max:1000'],
            'delivery_preference' => ['nullable', 'string', 'in:Delivery,Pickup'],
            'delivery_date' => ['nullable', 'string', 'max:100'],
            'delivery_time' => ['nullable', 'string', 'max:100'],
            'delivery_reason' => ['nullable', 'string', 'max:5000'],
            'need_for_help' => ['nullable', 'string', 'max:5000'],
            'severe_need' => ['nullable', 'string', 'max:5000'],
            'is_severe_need' => ['boolean'],
            'severe_need_notes' => ['nullable', 'string', 'max:5000'],
            'preferred_language_other' => ['nullable', 'string', 'max:100'],
            'dietary_restrictions' => ['nullable', 'array'],
            'dietary_restrictions.*' => ['string', 'in:nut_free,halal,kosher,vegetarian,gluten_free,dairy_free'],
            'dietary_notes' => ['nullable', 'string', 'max:1000'],
            'other_questions' => ['nullable', 'string', 'max:5000'],
            // Children (wizard fields)
            'children' => ['nullable', 'array'],
            'children.*.gender' => ['nullable', 'string', 'max:50'],
            'children.*.age' => ['nullable', 'string', 'max:20'],
            'children.*.school' => ['nullable', 'string', 'max:255'],
            'children.*.clothing_options' => ['nullable', 'string', 'max:500'],
            'children.*.clothing_styles' => ['nullable', 'string', 'max:500'],
            'children.*.all_sizes' => ['nullable', 'string', 'max:500'],
            'children.*.toy_ideas' => ['nullable', 'string', 'max:1000'],
            'children.*.gift_preferences' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'family_name.required' => 'Please enter the family name.',
            'address.required' => 'Please enter the family address.',
            'phone1.required' => 'Please enter a primary phone number.',
        ];
    }
}
