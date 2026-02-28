<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWarehouseReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isCoordinator() || $this->user()->isSanta();
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:warehouse_categories,id',
            'quantity' => 'required|integer|min:1|max:9999',
            'source' => 'nullable|string|max:100',
            'donor_name' => 'nullable|string|max:200',
            'barcode_scanned' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
            'item_id' => 'nullable|exists:warehouse_items,id',
            'volunteer_name' => 'nullable|string|max:200',
        ];
    }
}
