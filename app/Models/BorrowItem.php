<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BorrowItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'borrow_transaction_id',
        'item_type',
        'item_id',
        'quantity_borrowed',
        'quantity_returned',
        'quantity_lost',
        'quantity_damaged',
        'condition_out',
        'condition_in',
        'remarks',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity_borrowed' => 'decimal:2',
            'quantity_returned' => 'decimal:2',
            'quantity_lost' => 'decimal:2',
            'quantity_damaged' => 'decimal:2',
        ];
    }

    /**
     * Get the transaction that owns this item.
     */
    public function borrowTransaction(): BelongsTo
    {
        return $this->belongsTo(BorrowTransaction::class);
    }

    /**
     * Get the parent item model (Equipment or Chemical).
     */
    public function item(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'item_type', 'item_id');
    }
}