<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
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
        'is_guest',
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
            'is_guest' => 'boolean',
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
     * Students and instructors inherit their campus from their department;
     * laboratory in-charges use their explicit assignment. Coordinators are
     * cross-campus.
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
     * IBED students are not allowed to submit reservation or borrow requests.
     */
    public function isIbedStudent(): bool
    {
        $this->loadMissing(['role', 'department']);

        return strtolower((string) $this->role?->role_name) === 'student'
            && $this->campusName() === Campus::IBED;
    }

    public function isIbedJhsStudent(): bool
    {
        $this->loadMissing(['role', 'department']);
        $departmentCode = strtoupper(trim((string) $this->department?->department_code));
        $departmentName = strtoupper(trim((string) $this->department?->department_name));

        return $this->isIbedStudent()
            && ($departmentCode === 'JHS' || str_contains($departmentName, 'JUNIOR HIGH'));
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

    /**
     * Limit request-history requesters to the audience handled by a campus.
     *
     * IBED laboratories accept request history from IBED instructors. Main
     * campus laboratories accept Main students and instructors. Guest request
     * history is stored on the request snapshots, not on a User account.
     */
    public function scopeForRequestHistory(Builder $query, string $campus): Builder
    {
        $campus = Campus::normalize($campus);

        if (! in_array($campus, Campus::values(), true)) {
            return $query->whereRaw('1 = 0');
        }

        if ($campus === Campus::IBED) {
            return $query
                ->where('is_guest', false)
                ->whereHas('role', fn (Builder $roleQuery) => $roleQuery->where('role_name', 'Instructor'))
                ->whereHas('department', fn (Builder $departmentQuery) => $departmentQuery->where('campus', Campus::IBED));
        }

        return $query->where(function (Builder $requesterQuery) use ($campus): void {
            $requesterQuery
                ->where('is_guest', false)
                ->whereHas('role', fn (Builder $roleQuery) => $roleQuery->whereIn('role_name', ['Student', 'Instructor']))
                ->whereHas('department', fn (Builder $departmentQuery) => $departmentQuery->where('campus', $campus));
        });
    }

    /**
     * Limit users to the campus used by campus-scoped announcements.
     *
     * Students and instructors inherit campus from their department, while
     * laboratory in-charges use their explicit campus assignment with the
     * department as a fallback for older accounts.
     */
    public function scopeForCampus(Builder $query, string $campus): Builder
    {
        $campus = Campus::normalize($campus);

        if (! in_array($campus, Campus::values(), true)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $userQuery) use ($campus): void {
            $userQuery
                ->where(function (Builder $studentInstructorQuery) use ($campus): void {
                    $studentInstructorQuery
                        ->whereHas('role', fn (Builder $roleQuery) => $roleQuery->whereIn('role_name', ['Student', 'Instructor']))
                        ->whereHas('department', fn (Builder $departmentQuery) => $departmentQuery->where('campus', $campus));
                })
                ->orWhere(function (Builder $laboratoryInChargeQuery) use ($campus): void {
                    $laboratoryInChargeQuery
                        ->whereHas('role', fn (Builder $roleQuery) => $roleQuery->where('role_name', 'Laboratory In-charge'))
                        ->where(function (Builder $assignedCampusQuery) use ($campus): void {
                            $assignedCampusQuery
                                ->where('users.campus', $campus)
                                ->orWhere(function (Builder $fallbackCampusQuery) use ($campus): void {
                                    $fallbackCampusQuery
                                        ->whereNull('users.campus')
                                        ->whereHas('department', fn (Builder $departmentQuery) => $departmentQuery->where('campus', $campus));
                                });
                        });
                });
        });
    }

    /**
     * Instructors who may be selected to review student and guest requests.
     * Instructor campus is inherited from the department, so this deliberately
     * filters the department campus instead of the users.campus fallback field.
     */
    public function scopeMainCampusInstructors(Builder $query): Builder
    {
        return $query
            ->where('status', 'Active')
            ->whereHas('role', fn (Builder $roleQuery) => $roleQuery->where('role_name', 'Instructor'))
            ->whereHas('department', fn (Builder $departmentQuery) => $departmentQuery->where('campus', Campus::MAIN));
    }
}
