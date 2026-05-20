<?php

namespace App\Http\Requests;

use App\Enums\DeliveryLogStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddDeliveryLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route is already permission:santa gated.
        return $this->user() && method_exists($this->user(), 'isSanta') && $this->user()->isSanta();
    }

    public function rules(): array
    {
        // Note: addLog only accepts terminal statuses; in_transit is set
        // automatically when the validator detects a left_at_door or attempted.
        $allowed = [
            DeliveryLogStatus::Delivered->value,
            DeliveryLogStatus::LeftAtDoor->value,
            DeliveryLogStatus::NoAnswer->value,
            DeliveryLogStatus::Attempted->value,
            DeliveryLogStatus::Note->value,
        ];

        return [
            'status' => ['required', 'string', Rule::in($allowed)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
