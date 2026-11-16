<?php

namespace App\Models;

use App\Models\User;
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
        'Expired',
        'For Disposal',
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
        // A return that didn't increase available stock was a problem return
        // (Damaged / Under Repair / Lost). Label it by the actual condition
        // instead of "Added" so the traceability log reflects what happened.
        if ($this->action === 'Return' && (float) $this->quantity_changed <= 0) {
            $remarks = (string) ($this->remarks ?? '');

            if (str_contains($remarks, 'tagged Lost')) {
                return 'Lost';
            }

            if (str_contains($remarks, 'tagged Damaged')
                || str_contains($remarks, 'tagged Under Repair')) {
                return 'Damaged';
            }

            return 'Written Off';
        }

        return match ($this->action) {
            'Return', 'Purchase', 'Stock In' => 'Added',
            'Borrow', 'Damage', 'Lost', 'Stock Out' => 'Deducted',
            'Expired', 'For Disposal' => 'Deducted',
            'Adjustment' => (float) $this->quantity_changed >= 0 ? 'Added' : 'Deducted',
            default => $this->action,
        };
    }

    public function movementTone(): string
    {
        return match ($this->movementLabel()) {
            'Added'      => 'success',
            'Deducted'   => 'danger',
            'Damaged'    => 'warning',
            'Lost'       => 'danger',
            'Written Off'=> 'secondary',
            default      => 'secondary',
        };
    }

    public function sourceLabel(): string
    {
        // For unusable returns, spell out what kind of return it was.
        if ($this->action === 'Return' && (float) $this->quantity_changed <= 0) {
            $remarks = (string) ($this->remarks ?? '');

            if (str_contains($remarks, 'tagged Lost')) {
                return 'Lost return';
            }

            if (str_contains($remarks, 'tagged Damaged')
                || str_contains($remarks, 'tagged Under Repair')) {
                return 'Damaged return';
            }

            return 'Written-off return';
        }

        return match ($this->action) {
            'Borrow' => 'Student request',
            'Return' => 'Student return',
            'Stock In', 'Stock Out' => 'Coordinator update',
            'Expired', 'For Disposal' => 'Coordinator update',
            default => 'Inventory action',
        };
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by', 'userNo')->withTrashed();
    }
}
