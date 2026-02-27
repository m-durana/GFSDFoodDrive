<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseCategory extends Model
{
    protected $fillable = ['name', 'type', 'unit', 'barcode_prefix', 'sort_order', 'active'];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(WarehouseItem::class, 'category_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WarehouseTransaction::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
