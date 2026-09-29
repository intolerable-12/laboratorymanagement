<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chemical extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUSES = ['Active', 'Inactive', 'Expired', 'For Disposal'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'chemical_code',
        'barcode',
        'chemical_name',
        'category_id',
        'laboratory_id',
        'supplier_id',
        'quantity',
        'unit',
        'minimum_stock',
        'manufactured_date',
        'expiration_date',
        'expiration_alert_days',
        'supplier_alert_sent_at',
        'low_stock_supplier_alert_sent_at',
        'low_stock_alert_sent_at',
        'received_date',
        'hazard_classification',
        'storage_location',
        'status',
        'image',
        'description',
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
            'minimum_stock' => 'decimal:2',
            'manufactured_date' => 'date',
            'expiration_date' => 'date',
            'expiration_alert_days' => 'integer',
            'supplier_alert_sent_at' => 'datetime',
            'low_stock_supplier_alert_sent_at' => 'datetime',
            'low_stock_alert_sent_at' => 'datetime',
            'received_date' => 'date',
        ];
    }

    /**
     * Scope chemicals that may be included in a user request.
     */
    public function scopeAvailableForRequest(Builder $query): Builder
    {
        $statusColumn = $query->qualifyColumn('status');
        $quantityColumn = $query->qualifyColumn('quantity');
        $expirationColumn = $query->qualifyColumn('expiration_date');

        return $query
            ->where($statusColumn, 'Active')
            ->where($quantityColumn, '>', 0)
            ->where(function (Builder $query) use ($expirationColumn): void {
                $query
                    ->whereNull($expirationColumn)
                    ->orWhereDate($expirationColumn, '>', today());
            });
    }

    /**
     * Determine whether the chemical has reached its expiration date.
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expiration_date?->isPast() ?? false;
    }

    /**
     * Get the category that owns the chemical.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ChemicalCategory::class, 'category_id');
    }

    /**
     * Get the laboratory where the chemical is stored.
     */
    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class);
    }

    /**
     * Get the supplier of the chemical.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function inventoryPeriods(): HasMany
    {
        return $this->hasMany(ChemicalInventoryPeriod::class);
    }

    public function inventoryLogs(): MorphMany
    {
        return $this->morphMany(InventoryLog::class, 'item', 'item_type', 'item_id');
    }
}
