<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use App\Support\Campus;

class BorrowTransaction extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'borrow_no',
        'laboratory_id',
        'campus',
        'instructor_id',
        'reservation_id',
        'borrower_id',
        'guest_key',
        'guest_first_name',
        'guest_middle_name',
        'guest_last_name',
        'guest_suffix',
        'guest_student_id',
        'guest_email',
        'guest_contact_number',
        'guest_department_id',
        'released_by',
        'received_by',
        'borrowed_at',
        'checked_out_at',
        'checkout_notified_at',
        'due_at',
        'checkin_notified_at',
        'overdue_notified_at',
        'returned_at',
        'status',
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
            'borrowed_at' => 'datetime',
            'checked_out_at' => 'datetime',
            'checkout_notified_at' => 'datetime',
            'due_at' => 'datetime',
            'checkin_notified_at' => 'datetime',
            'overdue_notified_at' => 'datetime',
            'returned_at' => 'datetime',
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
     * Limit calendar entries to borrow requests that still have an active
     * schedule. A Returned transaction has completed check-in for all of its
     * equipment and should no longer be shown on any borrow calendar.
     */
    public function scopeForCalendar(Builder $query): Builder
    {
        return $query->whereIn('status', [
            'Coordinator Approved',
            'Partially Borrowed',
            'Borrowed',
            'Partially Returned',
            'Overdue',
        ]);
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
                    ->whereNotNull('borrow_transactions.guest_key')
                    ->orWhereHas('borrower', function (Builder $userQuery): void {
                        $userQuery->whereHas('role', fn (Builder $roleQuery) => $roleQuery->where('role_name', 'Student'));
                    });
            })
            ->where(function (Builder $requestQuery) use ($campus): void {
                $requestQuery
                    ->where('borrow_transactions.campus', $campus)
                    ->orWhereHas('borrower', function (Builder $userQuery) use ($campus): void {
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
                    ->where('borrow_transactions.guest_department_id', $departmentId)
                    ->orWhereHas('borrower', fn (Builder $userQuery) => $userQuery->where('department_id', $departmentId));
            })
            : $query->whereRaw('1 = 0');
    }

    /**
     * Get the reservation associated with the transaction.
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Get the laboratory associated with the transaction.
     */
    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class);
    }

    /**
     * Get the borrower (student).
     */
    public function borrower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'borrower_id', 'userNo');
    }

    public function guestDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'guest_department_id');
    }

    public function isGuestRequest(): bool
    {
        return $this->borrower_id === null;
    }

    public function requesterName(): string
    {
        if ($this->borrower) {
            return trim(collect([
                $this->borrower->first_name,
                $this->borrower->middle_name,
                $this->borrower->last_name,
                $this->borrower->suffix,
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
        return $this->borrower?->email ?: $this->guest_email;
    }

    public function requesterStudentId(): ?string
    {
        return $this->borrower?->userID ?: $this->guest_student_id;
    }

    public function requesterContactNumber(): ?string
    {
        return $this->borrower?->contact_number ?: $this->guest_contact_number;
    }

    public function requesterDepartmentName(): ?string
    {
        return $this->borrower?->department?->department_name
            ?: $this->guestDepartment?->department_name;
    }

    public function assignedInstructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id', 'userNo');
    }

    /**
     * Get the facilitator who released the items.
     */
    public function releasedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by', 'userNo');
    }

    /**
     * Get the facilitator who received the returned items.
     */
    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by', 'userNo');
    }

    /**
     * Get the items included in this transaction.
     */
    public function items(): HasMany
    {
        return $this->hasMany(BorrowItem::class);
    }

    /**
     * Get the barcode scans associated with this checkout.
     */
    public function barcodeLogs(): HasMany
    {
        return $this->hasMany(BarcodeLog::class);
    }
}
