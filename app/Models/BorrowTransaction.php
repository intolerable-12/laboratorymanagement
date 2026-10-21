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
            ->whereHas('borrower', function (Builder $userQuery): void {
                $userQuery->whereHas('role', fn (Builder $roleQuery) => $roleQuery->where('role_name', 'Student'));
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
            ? $query->whereHas('borrower', fn (Builder $userQuery) => $userQuery->where('department_id', $departmentId))
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
