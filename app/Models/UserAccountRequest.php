<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class UserAccountRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'email',
        'user_id',
        'contact_number',
        'password',
        'profile_photo',
        'role_id',
        'department_id',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by', 'userNo');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'Pending');
    }
}
