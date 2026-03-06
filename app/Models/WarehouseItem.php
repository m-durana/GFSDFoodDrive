<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WarehouseItem extends Model
{
    protected $fillable = ['category_id', 'name', 'barcode', 'description', 'is_generic', 'active', 'location_zone', 'location_shelf', 'location_bin'];

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

    public function latestTransaction(): HasOne
    {
        return $this->hasOne(WarehouseTransaction::class, 'item_id')->latestOfMany();
    }

    public function locationLabel(): string
    {
        if (!$this->location_zone && !$this->location_shelf && !$this->location_bin) {
            return 'Unassigned';
        }

        return collect([$this->location_zone, $this->location_shelf, $this->location_bin])
            ->filter()
            ->implode('-');
    }
}
