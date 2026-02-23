<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShoppingAssignment extends Model
{
    protected $fillable = [
        'user_id',
        'split_type',
        'categories',
        'family_start',
        'family_end',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'categories' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the aggregate shopping list for this assignment.
     */
    public function getShoppingList(): array
    {
        $families = Family::whereNotNull('family_number')->with('children');

        if ($this->split_type === 'family_range') {
            $families->whereBetween('family_number', [$this->family_start, $this->family_end]);
        }

        $families = $families->orderBy('family_number')->get();

        $aggregated = [];
        foreach ($families as $family) {
            $list = GroceryItem::calculateForFamily($family);
            foreach ($list as $itemName => $info) {
                if ($this->split_type === 'category' && !in_array($info['category'], $this->categories ?? [])) {
                    continue;
                }
                if (!isset($aggregated[$info['category']])) {
                    $aggregated[$info['category']] = [];
                }
                $aggregated[$info['category']][$itemName] = ($aggregated[$info['category']][$itemName] ?? 0) + $info['quantity'];
            }
        }

        return $aggregated;
    }

    /**
     * Count total items in this assignment.
     */
    public function getTotalItems(): int
    {
        $list = $this->getShoppingList();
        $total = 0;
        foreach ($list as $items) {
            $total += array_sum($items);
        }
        return $total;
    }

    /**
     * Get a human-readable description of what this assignment covers.
     */
    public function getDescription(): string
    {
        if ($this->split_type === 'category') {
            return 'Categories: ' . implode(', ', array_map('ucfirst', $this->categories ?? []));
        }
        return "Families #{$this->family_start}–#{$this->family_end}";
    }
}
