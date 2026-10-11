@php
    $approvalIsReservation = $approvalRequest instanceof \App\Models\Reservation;
    $approvalRequester = $approvalIsReservation ? $approvalRequest->user : $approvalRequest->borrower;
    $approvalRequesterRole = $approvalRequester?->role?->role_name;
    $approvalCampus = $approvalRequest->laboratory?->campus;
    $approvalStatus = $approvalRequest->status;

    $approvalStage = match (true) {
        in_array($approvalStatus, ['Rejected', 'Cancelled'], true) => strtolower($approvalStatus),
        $approvalStatus === 'Pending' && $approvalRequesterRole === 'Student' => 'instructor',
        $approvalStatus === 'Pending' && $approvalRequesterRole === 'Instructor' => 'laboratory',
        $approvalStatus === 'Instructor Approved' => 'laboratory',
        $approvalStatus === 'Facilitator Approved' && $approvalCampus === 'Main' => 'coordinator',
        $approvalStatus === 'Facilitator Approved' && $approvalCampus === 'IBED' => 'approved',
        $approvalStatus === 'Coordinator Approved' => 'approved',
        default => null,
    };

    $approvalTone = match ($approvalStage) {
        'instructor', 'laboratory' => 'warning',
        'coordinator' => 'primary',
        'approved' => 'success',
        'rejected', 'cancelled' => 'danger',
        default => 'secondary',
    };

    $approvalLabel = match ($approvalStage) {
        'instructor' => 'Not approved yet by Instructor',
        'laboratory' => 'Not approved yet by Laboratory In-charge',
        'coordinator' => 'Not approved yet by Coordinator',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'cancelled' => 'Cancelled',
        default => null,
    };

    $campusTone = $approvalCampus === 'IBED' ? 'warning' : 'info';
@endphp

@if ($approvalStage)
    <div class="d-flex flex-wrap align-items-center gap-2 mb-4" role="status" aria-label="Approval status">
        <span class="badge rounded-pill text-bg-{{ $campusTone }}">{{ $approvalCampus ?? 'Unknown' }} campus</span>
        <span class="badge rounded-pill text-bg-{{ $approvalTone }}">{{ $approvalLabel }}</span>
    </div>
@endif
