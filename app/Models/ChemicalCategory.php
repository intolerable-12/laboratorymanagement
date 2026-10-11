<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class ChemicalCategory extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category_code',
        'category_name',
        'description',
    ];

    public function scopeForCampus(Builder $query, ?string $campus): Builder
    {
        return filled($campus)
            ? $query->whereHas('chemicals', fn (Builder $chemicalQuery) => $chemicalQuery->forCampus($campus))
            : $query;
    }

    /**
     * Get all chemicals under this category.
     */
    public function chemicals(): HasMany
    {
        return $this->hasMany(Chemical::class, 'category_id');
    }
}
