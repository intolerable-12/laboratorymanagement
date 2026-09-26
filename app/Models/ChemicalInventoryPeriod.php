<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChemicalInventoryPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'chemical_id',
        'school_year_id',
        'semester_id',
        'beginning_quantity',
        'ending_quantity',
        'used_quantity',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'beginning_quantity' => 'decimal:2',
            'ending_quantity' => 'decimal:2',
            'used_quantity' => 'decimal:2',
        ];
    }

    public function chemical(): BelongsTo
    {
        return $this->belongsTo(Chemical::class);
    }

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }
}
