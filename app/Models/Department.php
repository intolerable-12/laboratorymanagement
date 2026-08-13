<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'department_code',
        'department_name',
        'description',
    ];

    /**
     * Get the users assigned to this department.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
