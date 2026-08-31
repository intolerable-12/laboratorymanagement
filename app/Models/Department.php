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

    /**
     * Get the student User ID prefix for this department.
     */
    public function studentUserIdPrefix(): ?string
    {
        $code = strtoupper(trim((string) $this->department_code));
        $name = strtoupper(trim((string) $this->department_name));

        return match (true) {
            $code === 'COL' || str_contains($code, 'COLLEGE') || str_contains($name, 'COLLEGE') => 'C',
            in_array($code, ['JHS', 'SHS'], true)
                || str_contains($code, 'JUNIOR HIGH')
                || str_contains($code, 'SENIOR HIGH')
                || str_contains($name, 'JUNIOR HIGH')
                || str_contains($name, 'SENIOR HIGH') => 'S',
            default => null,
        };
    }
}
