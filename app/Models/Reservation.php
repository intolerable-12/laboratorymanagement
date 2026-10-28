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
        'guest_key',
        'guest_first_name',
        'guest_middle_name',
        'guest_last_name',
        'guest_suffix',
        'guest_student_id',
        'guest_email',
        'guest_contact_number',
        'guest_department_id',
        'laboratory_id',
        'campus',
        'instructor_id',
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

    public function statusLabel(): string
    {
        if ($this->status === 'Coordinator Approved' && $this->laboratory?->campus === Campus::IBED) {
            return 'IBED Laboratory In-charge approved';
        }

        return $this->status === 'Facilitator Approved'
            ? 'Laboratory In-charge Approved'
            : $this->status;
    }

    public function scopeForCampus(Builder $query, ?string $campus): Builder
    {
        return filled($campus)
            ? $query->whereHas('laboratory', fn (Builder $laboratoryQuery) => $laboratoryQuery->forCampus($campus))
            : $query;
    }

    /**
     * Limit calendar entries to reservations that still have an active
     * schedule. The linked borrow transaction becomes Returned after all
     * equipment has completed check-in.
     */
    public function scopeForCalendar(Builder $query): Builder
    {
        return $query
            ->where('status', 'Coordinator Approved')
            ->whereDoesntHave('borrowTransactions', fn (Builder $borrowQuery) => $borrowQuery->where('status', 'Returned'));
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
            ->where(function (Builder $requesterQuery): void {
                $requesterQuery
                    ->whereNotNull('reservations.guest_key')
                    ->orWhereHas('user', function (Builder $userQuery): void {
                        $userQuery->whereHas('role', fn (Builder $roleQuery) => $roleQuery->where('role_name', 'Student'));
                    });
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
            ? $query->where(function (Builder $requesterQuery) use ($departmentId): void {
                $requesterQuery
                    ->where('reservations.guest_department_id', $departmentId)
                    ->orWhereHas('user', fn (Builder $userQuery) => $userQuery->where('department_id', $departmentId));
            })
            : $query->whereRaw('1 = 0');
    }

    /**
     * Get the user (student) who made the reservation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_no', 'userNo');
    }

    public function guestDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'guest_department_id');
    }

    public function isGuestRequest(): bool
    {
        return $this->user_no === null;
    }

    public function requesterName(): string
    {
        if ($this->user) {
            return trim(collect([
                $this->user->first_name,
                $this->user->middle_name,
                $this->user->last_name,
                $this->user->suffix,
            ])->filter()->implode(' '));
        }

        return trim(collect([
            $this->guest_first_name,
            $this->guest_middle_name,
            $this->guest_last_name,
            $this->guest_suffix,
        ])->filter()->implode(' '));
    }

    public function requesterEmail(): ?string
    {
        return $this->user?->email ?: $this->guest_email;
    }

    public function requesterStudentId(): ?string
    {
        return $this->user?->userID ?: $this->guest_student_id;
    }

    public function requesterContactNumber(): ?string
    {
        return $this->user?->contact_number ?: $this->guest_contact_number;
    }

    public function requesterDepartmentName(): ?string
    {
        return $this->user?->department?->department_name
            ?: $this->guestDepartment?->department_name;
    }

    public function assignedInstructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id', 'userNo');
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
