@extends('users.facilitator.layouts.app')

@section('title', 'Reservation Review')
@section('user-name', 'Laboratory In-charge')
@section('user-role', 'Laboratory In-charge')

@section('nav-links')
    @include('users.facilitator.partials.nav-links', ['active' => 'reservation'])
@endsection

@section('content')
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
    <div class="account-page">

        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
        @endif

        <div class="row g-4">
            <div class="col-12">
                <div class="card section-card border-0 h-100">
                    <div class="card-body p-4 p-xl-5">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                            <div>
                                <h3 class="h4 fw-semibold mb-1 text-dark">Reservation Summary</h3>
                                <p class="mb-0 text-secondary">Verify the laboratory and item availability before approving.</p>
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
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge text-bg-{{ $statusTone }}">{{ $reservation->status }}</span>
                                <a href="{{ route('facilitator.reservations.index') }}" class="btn btn-outline-secondary px-3">Back to Queue</a>
                            </div>
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

                        @unless ($reservation->status === 'Instructor Approved')
                        <div class="mb-4">
                            <h4 class="h5 fw-semibold mb-3 text-dark">Requested Items</h4>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr class="text-secondary small text-uppercase">
                                            <th>Type</th>
                                            <th>Item</th>
                                            <th>Quantity</th>
                                            <th>Unit</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($reservation->items as $item)
                                            <tr>
                                                <td>{{ $item->item_type }}</td>
                                                <td>
                                                    <div class="fw-semibold text-dark">{{ $item->item?->equipment_name ?? $item->item?->chemical_name ?? '—' }}</div>
                                                    <div class="small text-secondary">{{ $item->item?->equipment_code ?? $item->item?->chemical_code ?? '' }}</div>
                                                </td>
                                                <td>{{ $item->quantity }}</td>
                                                <td>{{ $item->unit ?? '—' }}</td>
                                                <td>{{ $item->remarks ?? '—' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-secondary py-4">No items were attached to this reservation.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endunless

                        @if ($reservation->status === 'Instructor Approved')
                            <div data-shared-remarks>
                            <div class="row g-3">
                                <div class="col-12">
                                    <form method="POST" action="{{ route('facilitator.reservations.approve', $reservation) }}" class="card border-0 bg-light h-100">
                                        @csrf
                                        <input type="hidden" name="remarks" value="{{ old('remarks') }}" data-shared-remarks-field>
                                        <div class="card-body p-3 p-xl-4">
                                            <h4 class="h5 fw-semibold text-dark mb-2">Approve Request</h4>
                                            <p class="small text-secondary mb-3">Use this after verifying that the laboratory and items are available. Quantities can be adjusted before approval.</p>

                                            @include('users.facilitator.partials.review-item-editor', [
                                                'requestKind' => 'reservation',
                                                'requestItems' => $reservation->items,
                                                'equipmentItems' => $equipmentItems,
                                                'chemicalItems' => $chemicalItems,
                                                'resultsUrl' => route('facilitator.reservations.show', $reservation),
                                            ])

                                            <div class="card border-0 bg-light mt-3 mb-0">
                                                <div class="card-body p-3 p-xl-4">
                                                    <label for="reservation-action-remarks" class="form-label fw-semibold text-dark mb-1">Remarks</label>
                                                    <p class="small text-secondary mb-3">Use the same note for either action. Remarks are optional when approving and required when rejecting.</p>
                                                    <textarea id="reservation-action-remarks" rows="3" class="form-control @error('remarks') is-invalid @enderror" data-shared-remarks-input placeholder="Add an approval note or explain why the request is rejected">{{ old('remarks') }}</textarea>
                                                    @error('remarks')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                                </div>
                                            </div>

                                            <button type="submit" class="btn btn-success w-100 mt-3" onclick="return confirm('Approve this reservation request and forward it?');">Approve and Forward</button>
                                        </div>
                                    </form>
                                </div>

                                <div class="col-12">
                                    <form method="POST" action="{{ route('facilitator.reservations.reject', $reservation) }}" class="card border-0 bg-light h-100">
                                        @csrf
                                        <input type="hidden" name="remarks" value="{{ old('remarks') }}" data-shared-remarks-field>
                                        <div class="card-body p-3 p-xl-4">
                                            <h4 class="h5 fw-semibold text-dark mb-2">Reject Request</h4>
                                            <p class="small text-secondary mb-3">Write a reason so the requester knows what to correct.</p>
                                            <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Reject this reservation request?');">Reject</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
