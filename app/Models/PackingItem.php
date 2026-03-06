<?php

namespace App\Models;

use App\Enums\PackingItemStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackingItem extends Model
{
    protected $fillable = [
        'packing_list_id',
        'category_id',
        'item_id',
        'child_id',
        'grocery_item_id',
        'description',
        'quantity_needed',
        'quantity_packed',
        'status',
        'packed_by',
        'packed_at',
        'substitute_notes',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'status' => PackingItemStatus::class,
            'packed_at' => 'datetime',
            'quantity_needed' => 'integer',
            'quantity_packed' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function packingList(): BelongsTo
    {
        return $this->belongsTo(PackingList::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(WarehouseCategory::class, 'category_id');
    }

    public function warehouseItem(): BelongsTo
    {
        return $this->belongsTo(WarehouseItem::class, 'item_id');
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function groceryItem(): BelongsTo
    {
        return $this->belongsTo(GroceryItem::class);
    }

    public function packer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'packed_by');
    }

    public function isPacked(): bool
    {
        return in_array($this->status, [
            PackingItemStatus::Packed,
            PackingItemStatus::Verified,
            PackingItemStatus::Substituted,
        ]);
    }

    public function remainingQuantity(): int
    {
        return max(0, $this->quantity_needed - $this->quantity_packed);
    }
}
