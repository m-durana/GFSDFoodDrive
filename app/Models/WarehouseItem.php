<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseItem extends Model
{
    protected $fillable = ['category_id', 'name', 'barcode', 'description', 'is_generic', 'active'];

    protected function casts(): array
    {
        return [
            'is_generic' => 'boolean',
            'active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(WarehouseCategory::class, 'category_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WarehouseTransaction::class, 'item_id');
    }
}
