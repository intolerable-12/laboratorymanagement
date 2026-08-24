@extends('users.student.layouts.app')

@section('title', 'Borrow Details')
@section('user-name', 'Student')
@section('user-role', 'Student')



@section('content')
    <div class="account-page">
        <section class="hero-banner card border-0 mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <h2 class="h3 fw-semibold mb-2 text-dark">{{ $borrowTransaction->borrow_no }}</h2>
                    <p class="mb-0 text-secondary">Review the details of your borrow request and its current status.</p>
                </div>
                <a href="{{ route('student.borrow.index') }}" class="btn btn-outline-secondary px-4">Back to Requests</a>
            </div>
        </section>

        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
        @endif

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card section-card border-0 h-100">
                    <div class="card-body p-4 p-xl-5">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                            <div>
                                <h3 class="h4 fw-semibold mb-1 text-dark">Borrow Summary</h3>
                                <p class="mb-0 text-secondary">Review the request before it moves through the approval queue.</p>
                            </div>
                            @php
                                $statusTone = match ($borrowTransaction->status) {
                                    'Pending' => 'warning',
                                    'Instructor Approved' => 'info',
                                    'Facilitator Approved' => 'primary',
                                    'Coordinator Approved' => 'success',
                                    'Borrowed' => 'success',
                                    'Partially Returned' => 'primary',
                                    'Returned' => 'success',
                                    'Overdue' => 'danger',
                                    'Rejected' => 'danger',
                                    default => 'secondary',
                                };
                            @endphp
                            <span class="badge text-bg-{{ $statusTone }}">{{ $borrowTransaction->status }}</span>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="account-summary-card h-100">
                                    <div class="small text-secondary">Borrowed At</div>
                                    <div class="fw-semibold text-dark">{{ $borrowTransaction->borrowed_at?->format('M d, Y h:i A') ?? '—' }}</div>
                                    <div class="small text-secondary">Due {{ $borrowTransaction->due_at?->format('M d, Y h:i A') ?? '—' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="account-summary-card h-100">
                                    <div class="small text-secondary">Requested By</div>
                                    <div class="fw-semibold text-dark">{{ $borrowTransaction->borrower?->first_name }} {{ $borrowTransaction->borrower?->last_name }}</div>
                                    <div class="small text-secondary">{{ $borrowTransaction->borrower?->userID }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="account-summary-card h-100">
                                    <div class="small text-secondary">Updated</div>
                                    <div class="fw-semibold text-dark">{{ $borrowTransaction->updated_at?->format('M d, Y h:i A') }}</div>
                                    <div class="small text-secondary">Submitted {{ $borrowTransaction->created_at?->format('M d, Y h:i A') }}</div>
                                </div>
                            </div>
                        </div>

                        @if ($borrowTransaction->remarks)
                            <div class="mb-4">
                                <div class="small text-secondary text-uppercase mb-2">Remarks</div>
                                <div class="text-dark">{{ $borrowTransaction->remarks }}</div>
                            </div>
                        @endif

                        <div class="mb-4">
                            <h4 class="h5 fw-semibold mb-3 text-dark">Requested Items</h4>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr class="text-secondary small text-uppercase">
                                            <th>Type</th>
                                            <th>Item</th>
                                            <th>Quantity</th>
                                            <th>Condition Out</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($borrowTransaction->items as $item)
                                            <tr>
                                                <td>{{ $item->item_type }}</td>
                                                <td>
                                                    <div class="fw-semibold text-dark">{{ $item->item?->equipment_name ?? $item->item?->chemical_name ?? '—' }}</div>
                                                    <div class="small text-secondary">{{ $item->item?->equipment_code ?? $item->item?->chemical_code ?? '' }}</div>
                                                </td>
                                                <td>{{ $item->quantity_borrowed }}</td>
                                                <td>{{ $item->condition_out }}</td>
                                                <td>{{ $item->remarks ?? '—' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-secondary py-4">No items were attached to this borrow request.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card section-card border-0 h-100">
                    <div class="card-body p-4 p-xl-5">
                        <h3 class="h4 fw-semibold mb-4 text-dark">Transaction Log</h3>

                        <div class="vstack gap-3">
                            <div class="activity-item">
                                <div class="fw-semibold text-dark mb-1">Request Submitted</div>
                                <div class="small text-secondary">{{ $borrowTransaction->created_at?->format('M d, Y h:i A') }}</div>
                            </div>

                            <div class="activity-item">
                                <div class="fw-semibold text-dark mb-1">Last Updated</div>
                                <div class="small text-secondary">{{ $borrowTransaction->updated_at?->format('M d, Y h:i A') }}</div>
                            </div>

                            <div class="activity-item">
                                <div class="fw-semibold text-dark mb-1">Release Details</div>
                                <div class="small text-secondary">Released by {{ $borrowTransaction->releasedBy?->first_name ?? '—' }} {{ $borrowTransaction->releasedBy?->last_name ?? '' }}</div>
                                <div class="small text-secondary">Received by {{ $borrowTransaction->receivedBy?->first_name ?? '—' }} {{ $borrowTransaction->receivedBy?->last_name ?? '' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
