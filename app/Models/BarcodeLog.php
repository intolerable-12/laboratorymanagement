<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BarcodeLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_no',
        'borrow_transaction_id',
        'item_type',
        'item_id',
        'barcode',
        'quantity',
        'is_voided',
        'condition_in',
        'voided_by',
        'voided_at',
        'action',
        'scanned_at',
        'device_name',
        'ip_address',
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
            'quantity' => 'decimal:2',
            'is_voided' => 'boolean',
            'voided_at' => 'datetime',
            'scanned_at' => 'datetime',
        ];
    }

    /**
     * Get the user who performed the scan.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_no', 'userNo');
    }

    /**
     * Get the parent scanned item (Equipment or Chemical).
     */
    public function item(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'item_type', 'item_id');
    }
}
