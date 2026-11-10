<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'equipment';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'equipment_code',
        'barcode',
        'equipment_name',
        'category_id',
        'laboratory_id',
        'supplier_id',
        'brand',
        'model',
        'serial_number',
        'purchase_date',
        'manufacturing_date',
        'lifespan_mode',
        'lifespan_months',
        'lifespan_years',
        'lifespan_expires_at',
        'lifespan_alert_notified_at',
        'lifespan_supplier_alert_sent_at',
        'quantity',
        'available_quantity',
        'low_stock_threshold',
        'supplier_alert_sent_at',
        'low_stock_alert_sent_at',
        'condition',
        'status',
        'is_requestable',
        'image',
        'storage_location',
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
            'purchase_date' => 'date',
            'manufacturing_date' => 'date',
            'lifespan_months' => 'integer',
            'lifespan_years' => 'integer',
            'lifespan_expires_at' => 'datetime',
            'lifespan_alert_notified_at' => 'datetime',
            'lifespan_supplier_alert_sent_at' => 'datetime',
            'quantity' => 'integer',
            'available_quantity' => 'integer',
            'low_stock_threshold' => 'integer',
            'supplier_alert_sent_at' => 'datetime',
            'low_stock_alert_sent_at' => 'datetime',
            'is_requestable' => 'boolean',
        ];
    }

    public function scopeForCampus(Builder $query, ?string $campus): Builder
    {
        return filled($campus)
            ? $query->whereHas('laboratory', fn (Builder $laboratoryQuery) => $laboratoryQuery->forCampus($campus))
            : $query;
    }

    public function scopeRequestable(Builder $query): Builder
    {
        return $query->where($query->qualifyColumn('is_requestable'), true);
    }

    /**
     * Get the category that owns the equipment.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(EquipmentCategory::class, 'category_id');
    }

    /**
     * Get the laboratory where the equipment is located.
     */
    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class);
    }

    /**
     * Get the supplier of the equipment.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function inventoryPeriods(): HasMany
    {
        return $this->hasMany(EquipmentInventoryPeriod::class);
    }

    public function inventoryLogs(): MorphMany
    {
        return $this->morphMany(InventoryLog::class, 'item', 'item_type', 'item_id');
    }
}
