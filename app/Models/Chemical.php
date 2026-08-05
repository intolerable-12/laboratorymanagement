<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chemical extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'chemical_code',
        'barcode',
        'chemical_name',
        'cas_number',
        'category_id',
        'laboratory_id',
        'supplier_id',
        'quantity',
        'unit',
        'minimum_stock',
        'manufactured_date',
        'expiration_date',
        'received_date',
        'unit_cost',
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
            'unit_cost' => 'decimal:2',
            'manufactured_date' => 'date',
            'expiration_date' => 'date',
            'received_date' => 'date',
        ];
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
}