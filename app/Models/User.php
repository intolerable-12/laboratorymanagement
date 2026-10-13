<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use App\Support\Campus;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'userNo';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'userID',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'birth_date',
        'gender',
        'email',
        'contact_number',
        'password',
        'profile_photo',
        'role_id',
        'department_id',
        'campus',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birth_date' => 'date',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the role associated with the user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the department associated with the user.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Resolve the campus this account is allowed to work with.
     * Students and instructors inherit it from their department; laboratory
     * in-charges use their explicit assignment. Coordinators are cross-campus.
     */
    public function campusName(): ?string
    {
        $this->loadMissing(['role', 'department']);
        $roleName = strtolower((string) $this->role?->role_name);

        if ($roleName === 'coordinator') {
            return null;
        }

        if ($roleName === 'laboratory in-charge') {
            return Campus::normalize($this->campus) ?? $this->department?->campusName();
        }

        if (in_array($roleName, ['student', 'instructor'], true)) {
            return $this->department?->campusName();
        }

        return Campus::normalize($this->campus);
    }

    /**
     * Get the profile photo URL for display in authenticated layouts.
     */
    public function profilePhotoUrl(): ?string
    {
        $photo = trim((string) $this->profile_photo);

        if ($photo === '') {
            return null;
        }

        if (filter_var($photo, FILTER_VALIDATE_URL)) {
            return $photo;
        }

        if (str_starts_with($photo, 'storage/')) {
            return asset($photo);
        }

        if (Storage::disk('public')->exists($photo)) {
            return asset('storage/' . $photo);
        }

        return asset($photo);
    }

    /**
     * Get the reservations submitted by this requester.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'user_no', 'userNo');
    }

    /**
     * Get the borrowing requests submitted by this requester.
     */
    public function borrowTransactions(): HasMany
    {
        return $this->hasMany(BorrowTransaction::class, 'borrower_id', 'userNo');
    }
}
