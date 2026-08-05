<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BorrowTransaction extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'borrow_no',
        'reservation_id',
        'borrower_id',
        'released_by',
        'received_by',
        'borrowed_at',
        'due_at',
        'returned_at',
        'status',
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
            'borrowed_at' => 'datetime',
            'due_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    /**
     * Get the reservation associated with the transaction.
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Get the borrower (student).
     */
    public function borrower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'borrower_id', 'userNo');
    }

    /**
     * Get the facilitator who released the items.
     */
    public function releasedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by', 'userNo');
    }

    /**
     * Get the facilitator who received the returned items.
     */
    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by', 'userNo');
    }

    /**
     * Get the items included in this transaction.
     */
    public function items(): HasMany
    {
        return $this->hasMany(BorrowItem::class);
    }
}