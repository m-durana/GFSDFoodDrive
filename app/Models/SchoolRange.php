<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolRange extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_name',
        'range_start',
        'range_end',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'range_start' => 'integer',
            'range_end' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the next available family number in this range.
     */
    public function nextAvailableNumber(): ?int
    {
        $usedNumbers = Family::whereBetween('family_number', [$this->range_start, $this->range_end])
            ->pluck('family_number')
            ->toArray();

        for ($i = $this->range_start; $i <= $this->range_end; $i++) {
            if (!in_array($i, $usedNumbers)) {
                return $i;
            }
        }

        return null; // Range is full
    }
}
