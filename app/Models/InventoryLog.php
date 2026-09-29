<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryLog extends Model
{
    use HasFactory;

    public const ACTIONS = [
        'Borrow',
        'Return',
        'Purchase',
        'Adjustment',
        'Damage',
        'Lost',
        'Maintenance',
        'Stock In',
        'Stock Out',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'item_type',
        'item_id',
        'performed_by',
        'action',
        'quantity_before',
        'quantity_changed',
        'quantity_after',
        'remarks',
        'performed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity_before' => 'decimal:2',
            'quantity_changed' => 'decimal:2',
            'quantity_after' => 'decimal:2',
            'performed_at' => 'datetime',
        ];
    }

    /**
     * Get the parent logged item (Equipment or Chemical).
     */
    public function item(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'item_type', 'item_id');
    }

    /**
     * Get the user who performed the inventory action.
     */
    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by', 'userNo')->withTrashed();
    }

    public function movementLabel(): string
    {
        return match ($this->action) {
            'Return', 'Purchase', 'Stock In' => 'Added',
            'Borrow', 'Damage', 'Lost', 'Stock Out' => 'Deducted',
            'Adjustment' => (float) $this->quantity_changed >= 0 ? 'Added' : 'Deducted',
            default => $this->action,
        };
    }

    public function movementTone(): string
    {
        return $this->movementLabel() === 'Added' ? 'success' : ($this->movementLabel() === 'Deducted' ? 'danger' : 'secondary');
    }

    public function sourceLabel(): string
    {
        return match ($this->action) {
            'Borrow' => 'Student request',
            'Return' => 'Student return',
            'Stock In', 'Stock Out' => 'Coordinator update',
            default => 'Inventory action',
        };
    }
}
