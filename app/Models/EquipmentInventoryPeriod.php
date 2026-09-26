<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentInventoryPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipment_id',
        'school_year_id',
        'semester_id',
        'beginning_quantity',
        'ending_quantity',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'beginning_quantity' => 'integer',
            'ending_quantity' => 'integer',
        ];
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
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
