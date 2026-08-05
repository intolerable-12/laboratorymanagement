<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EquipmentMaintenance extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'equipment_maintenance';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'equipment_id',
        'reported_by',
        'assigned_to',
        'issue_title',
        'problem_description',
        'reported_date',
        'maintenance_date',
        'completed_date',
        'repair_cost',
        'priority',
        'status',
        'maintenance_notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reported_date' => 'date',
            'maintenance_date' => 'date',
            'completed_date' => 'date',
            'repair_cost' => 'decimal:2',
        ];
    }

    /**
     * Get the equipment under maintenance.
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    /**
     * Get the user who reported the issue.
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by', 'userNo');
    }

    /**
     * Get the user (technician/facilitator) assigned to the maintenance task.
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to', 'userNo');
    }
}