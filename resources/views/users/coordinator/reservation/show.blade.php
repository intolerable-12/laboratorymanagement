@extends('users.coordinator.layouts.app')

@section('title', 'Reservation Review')
@section('page-title', 'Reservation Review')
@section('page-subtitle', 'Take the final coordinator action on facilitator-approved requests')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
        <div>
            <h2 class="h4 fw-semibold mb-1 text-dark">{{ $reservation->reservation_no }}</h2>
            <p class="mb-0 text-secondary">Final review for {{ $reservation->user?->first_name }} {{ $reservation->user?->last_name }}</p>
        </div>
        <a href="{{ route('coordinator.reservations.index') }}" class="btn btn-outline-secondary">Back to Queue</a>
    </div>

    @if (session('status'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card admin-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                        <div>
                            <h3 class="h5 fw-semibold mb-1 text-dark">Reservation Summary</h3>
                            <p class="mb-0 text-secondary">This request is ready for final coordinator review.</p>
                        </div>
                        @php
                            $statusTone = match ($reservation->status) {
                                'Pending' => 'warning',
                                'Instructor Approved' => 'info',
                                'Facilitator Approved' => 'primary',
                                'Coordinator Approved' => 'success',
                                'Rejected' => 'danger',
                                default => 'secondary',
                            };
                        @endphp
                        <span class="badge text-bg-{{ $statusTone }}">{{ $reservation->status }}</span>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6"><div class="account-summary-card h-100"><div class="small text-secondary">Student</div><div class="fw-semibold text-dark">{{ $reservation->user?->first_name }} {{ $reservation->user?->last_name }}</div><div class="small text-secondary">{{ $reservation->user?->userID }}</div></div></div>
                        <div class="col-md-6"><div class="account-summary-card h-100"><div class="small text-secondary">Laboratory</div><div class="fw-semibold text-dark">{{ $reservation->laboratory?->laboratory_name ?? '—' }}</div><div class="small text-secondary">{{ $reservation->laboratory?->laboratory_code }}</div></div></div>
                        <div class="col-md-6"><div class="account-summary-card h-100"><div class="small text-secondary">Schedule</div><div class="fw-semibold text-dark">{{ $reservation->reservation_date?->format('M d, Y') }}</div><div class="small text-secondary">{{ substr((string) $reservation->start_time, 0, 5) }} - {{ substr((string) $reservation->end_time, 0, 5) }}</div></div></div>
                        <div class="col-md-6"><div class="account-summary-card h-100"><div class="small text-secondary">Participants</div><div class="fw-semibold text-dark">{{ $reservation->expected_participants }}</div><div class="small text-secondary">{{ $reservation->schoolYear?->school_year }} | {{ $reservation->semester?->semester_name }}</div></div></div>
                    </div>

                    <div class="mb-4">
                        <div class="small text-secondary text-uppercase mb-2">Purpose</div>
                        <div class="text-dark">{{ $reservation->purpose }}</div>
                    </div>

                    <div class="mb-4">
                        <h4 class="h6 fw-semibold mb-3 text-dark">Requested Items</h4>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr class="text-secondary small text-uppercase"><th>Type</th><th>Item</th><th>Quantity</th><th>Unit</th><th>Remarks</th></tr>
                                </thead>
                                <tbody>
                                    @forelse ($reservation->items as $item)
                                        <tr>
                                            <td>{{ $item->item_type }}</td>
                                            <td><div class="fw-semibold text-dark">{{ $item->item?->equipment_name ?? $item->item?->chemical_name ?? '—' }}</div><div class="small text-secondary">{{ $item->item?->equipment_code ?? $item->item?->chemical_code ?? '' }}</div></td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>{{ $item->unit ?? '—' }}</td>
                                            <td>{{ $item->remarks ?? '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-secondary py-4">No items were attached to this reservation.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if ($reservation->status === 'Facilitator Approved')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <form method="POST" action="{{ route('coordinator.reservations.approve', $reservation) }}" class="card border-0 bg-light h-100">
                                    @csrf
                                    <div class="card-body p-3 p-xl-4">
                                        <h4 class="h6 fw-semibold text-dark mb-2">Approve</h4>
                                        <p class="small text-secondary mb-3">The requester will receive a final approval email. Adjust the final schedule if needed.</p>
                                        <div class="row g-3 mb-3">
                                            <div class="col-12">
                                                <label class="form-label fw-semibold text-dark">Reservation Date</label>
                                                <input type="date" name="reservation_date" value="{{ old('reservation_date', optional($reservation->reservation_date)->format('Y-m-d')) }}" class="form-control @error('reservation_date') is-invalid @enderror">
                                                @error('reservation_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold text-dark">Start Time</label>
                                                <input type="time" name="start_time" value="{{ old('start_time', substr((string) $reservation->start_time, 0, 5)) }}" class="form-control @error('start_time') is-invalid @enderror">
                                                @error('start_time')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold text-dark">End Time</label>
                                                <input type="time" name="end_time" value="{{ old('end_time', substr((string) $reservation->end_time, 0, 5)) }}" class="form-control @error('end_time') is-invalid @enderror">
                                                @error('end_time')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-dark">Remarks</label>
                                            <textarea name="remarks" rows="3" class="form-control @error('remarks') is-invalid @enderror" placeholder="Optional approval note">{{ old('remarks') }}</textarea>
                                            @error('remarks')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                        <button type="submit" class="btn btn-success w-100">Approve</button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <form method="POST" action="{{ route('coordinator.reservations.reject', $reservation) }}" class="card border-0 bg-light h-100">
                                    @csrf
                                    <div class="card-body p-3 p-xl-4">
                                        <h4 class="h6 fw-semibold text-dark mb-2">Reject</h4>
                                        <p class="small text-secondary mb-3">A reason is required and will be emailed to the requester.</p>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-dark">Remarks</label>
                                            <textarea name="remarks" rows="3" class="form-control @error('remarks') is-invalid @enderror" placeholder="Explain why the request was rejected">{{ old('remarks') }}</textarea>
                                            @error('remarks')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                        <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Reject this reservation request?');">Reject</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card admin-card h-100">
                <div class="card-body p-4">
                    <h3 class="h5 fw-semibold mb-4 text-dark">Approval History</h3>
                    <div class="vstack gap-3">
                        @forelse ($reservation->approvalLogs as $log)
                            <div class="activity-item">
                                <div class="d-flex justify-content-between align-items-center gap-3 mb-1">
                                    <div class="fw-semibold text-dark">{{ $log->action }}</div>
                                    <span class="badge text-bg-light border text-secondary">{{ $log->role }}</span>
                                </div>
                                <div class="small text-secondary mb-1">By {{ $log->approvedBy?->first_name ?? 'System' }} {{ $log->approvedBy?->last_name ?? '' }}</div>
                                <div class="small text-secondary">{{ $log->approved_at?->format('M d, Y h:i A') }}</div>
                                @if ($log->remarks)
                                    <div class="small text-dark mt-2">{{ $log->remarks }}</div>
                                @endif
                            </div>
                        @empty
                            <div class="text-secondary">No approval action has been recorded yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection