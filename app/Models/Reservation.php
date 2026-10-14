<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use App\Support\Campus;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reservation_no',
        'user_no',
        'laboratory_id',
        'campus',
        'experiment_title',
        'purpose',
        'reservation_date',
        'start_time',
        'end_time',
        'expected_participants',
        'status',
        'remarks',
        'school_year_id',
        'semester_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reservation_date' => 'date',
            'expected_participants' => 'integer',
        ];
    }

    public function scopeForCampus(Builder $query, ?string $campus): Builder
    {
        return filled($campus)
            ? $query->whereHas('laboratory', fn (Builder $laboratoryQuery) => $laboratoryQuery->forCampus($campus))
            : $query;
    }

    /**
     * Limit the instructor approval queue to students belonging to the
     * instructor's campus. IBED instructors review JHS students only.
     */
    public function scopeForInstructorApproval(Builder $query, ?string $campus): Builder
    {
        if (! filled($campus)) {
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->forCampus($campus)
            ->whereHas('user', function (Builder $userQuery): void {
                $userQuery->whereHas('role', fn (Builder $roleQuery) => $roleQuery->where('role_name', 'Student'));
            })
            ->where(function (Builder $requestQuery) use ($campus): void {
                $requestQuery
                    ->where('reservations.campus', $campus)
                    ->orWhereHas('user', function (Builder $userQuery) use ($campus): void {
                        $userQuery->whereHas('department', function (Builder $departmentQuery) use ($campus): void {
                            $departmentQuery->where('campus', $campus)
                                ->when(
                                    $campus === Campus::IBED,
                                    fn (Builder $query) => $query->whereRaw("UPPER(TRIM(department_code)) = 'JHS'")
                                );
                        });
                    });
            });
    }

    public function scopeForDepartment(Builder $query, ?int $departmentId): Builder
    {
        return $departmentId
            ? $query->whereHas('user', fn (Builder $userQuery) => $userQuery->where('department_id', $departmentId))
            : $query->whereRaw('1 = 0');
    }

    /**
     * Get the user (student) who made the reservation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_no', 'userNo');
    }

    /**
     * Get the laboratory reserved.
     */
    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class);
    }

    /**
     * Get the school year associated with the reservation.
     */
    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    /**
     * Get the semester associated with the reservation.
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Get the requested items attached to the reservation.
     */
    public function items(): HasMany
    {
        return $this->hasMany(ReservationItem::class);
    }

    /**
     * Get the approval logs associated with the reservation.
     */
    public function approvalLogs(): HasMany
    {
        return $this->hasMany(ApprovalLog::class);
    }

    /**
     * Get the borrow transactions created from this reservation.
     */
    public function borrowTransactions(): HasMany
    {
        return $this->hasMany(BorrowTransaction::class);
    }
}
