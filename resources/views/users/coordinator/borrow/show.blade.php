@extends('users.coordinator.layouts.app')

@section('title', 'Borrow Review')
@section('page-title', 'Borrow Review')
@section('page-subtitle', 'Take the final coordinator action on facilitator-approved borrow requests')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
        <div>
            <h2 class="h4 fw-semibold mb-1 text-dark">{{ $borrowTransaction->borrow_no }}</h2>
            <p class="mb-0 text-secondary">Final review for {{ $borrowTransaction->borrower?->first_name }} {{ $borrowTransaction->borrower?->last_name }}</p>
        </div>
        <a href="{{ route('coordinator.borrow.index') }}" class="btn btn-outline-secondary">Back to Queue</a>
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
                            <h3 class="h5 fw-semibold mb-1 text-dark">Borrow Summary</h3>
                            <p class="mb-0 text-secondary">This request is ready for final coordinator review.</p>
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
                        <div class="col-md-6"><div class="account-summary-card h-100"><div class="small text-secondary">Student</div><div class="fw-semibold text-dark">{{ $borrowTransaction->borrower?->first_name }} {{ $borrowTransaction->borrower?->last_name }}</div><div class="small text-secondary">{{ $borrowTransaction->borrower?->userID }}</div></div></div>
                        <div class="col-md-6"><div class="account-summary-card h-100"><div class="small text-secondary">Borrowed At</div><div class="fw-semibold text-dark">{{ $borrowTransaction->borrowed_at?->format('M d, Y h:i A') ?? '—' }}</div><div class="small text-secondary">Due {{ $borrowTransaction->due_at?->format('M d, Y h:i A') ?? '—' }}</div></div></div>
                        <div class="col-md-6"><div class="account-summary-card h-100"><div class="small text-secondary">Submitted</div><div class="fw-semibold text-dark">{{ $borrowTransaction->created_at?->format('M d, Y h:i A') }}</div><div class="small text-secondary">Updated {{ $borrowTransaction->updated_at?->format('M d, Y h:i A') }}</div></div></div>
                        <div class="col-md-6"><div class="account-summary-card h-100"><div class="small text-secondary">Items</div><div class="fw-semibold text-dark">{{ $borrowTransaction->items->count() }} item{{ $borrowTransaction->items->count() === 1 ? '' : 's' }}</div><div class="small text-secondary">Borrow request</div></div></div>
                    </div>

                    @if ($borrowTransaction->remarks)
                        <div class="mb-4">
                            <div class="small text-secondary text-uppercase mb-2">Remarks</div>
                            <div class="text-dark">{{ $borrowTransaction->remarks }}</div>
                        </div>
                    @endif

                    <div class="mb-4">
                        <h4 class="h6 fw-semibold mb-3 text-dark">Requested Items</h4>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr class="text-secondary small text-uppercase"><th>Type</th><th>Item</th><th>Quantity</th><th>Condition Out</th><th>Remarks</th></tr>
                                </thead>
                                <tbody>
                                    @forelse ($borrowTransaction->items as $item)
                                        <tr>
                                            <td>{{ $item->item_type }}</td>
                                            <td><div class="fw-semibold text-dark">{{ $item->item?->equipment_name ?? $item->item?->chemical_name ?? '—' }}</div><div class="small text-secondary">{{ $item->item?->equipment_code ?? $item->item?->chemical_code ?? '' }}</div></td>
                                            <td>{{ $item->quantity_borrowed }}</td>
                                            <td>{{ $item->condition_out }}</td>
                                            <td>{{ $item->remarks ?? '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-secondary py-4">No items were attached to this borrow request.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if ($borrowTransaction->status === 'Facilitator Approved')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <form method="POST" action="{{ route('coordinator.borrow.approve', $borrowTransaction) }}" class="card border-0 bg-light h-100">
                                    @csrf
                                    <div class="card-body p-3 p-xl-4">
                                        <h4 class="h6 fw-semibold text-dark mb-2">Approve</h4>
                                        <p class="small text-secondary mb-3">The requester will receive a final approval email. Adjust the final borrow period if needed.</p>
                                        <div class="row g-3 mb-3">
                                            <div class="col-12">
                                                <label class="form-label fw-semibold text-dark">Borrowed At</label>
                                                <input type="datetime-local" name="borrowed_at" value="{{ old('borrowed_at', optional($borrowTransaction->borrowed_at)->format('Y-m-d\TH:i')) }}" class="form-control @error('borrowed_at') is-invalid @enderror">
                                                @error('borrowed_at')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold text-dark">Due At</label>
                                                <input type="datetime-local" name="due_at" value="{{ old('due_at', optional($borrowTransaction->due_at)->format('Y-m-d\TH:i')) }}" class="form-control @error('due_at') is-invalid @enderror">
                                                @error('due_at')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
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
                                <form method="POST" action="{{ route('coordinator.borrow.reject', $borrowTransaction) }}" class="card border-0 bg-light h-100">
                                    @csrf
                                    <div class="card-body p-3 p-xl-4">
                                        <h4 class="h6 fw-semibold text-dark mb-2">Reject</h4>
                                        <p class="small text-secondary mb-3">A reason is required and will be emailed to the requester.</p>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-dark">Remarks</label>
                                            <textarea name="remarks" rows="3" class="form-control @error('remarks') is-invalid @enderror" placeholder="Explain why the request was rejected">{{ old('remarks') }}</textarea>
                                            @error('remarks')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                        <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Reject this borrow request?');">Reject</button>
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
                    <h3 class="h5 fw-semibold mb-4 text-dark">Transaction Log</h3>
                    <div class="vstack gap-3">
                        <div class="activity-item">
                            <div class="fw-semibold text-dark mb-1">Request Submitted</div>
                            <div class="small text-secondary">{{ $borrowTransaction->created_at?->format('M d, Y h:i A') }}</div>
                        </div>
                        <div class="activity-item">
                            <div class="fw-semibold text-dark mb-1">Last Updated</div>
                            <div class="small text-secondary">{{ $borrowTransaction->updated_at?->format('M d, Y h:i A') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection