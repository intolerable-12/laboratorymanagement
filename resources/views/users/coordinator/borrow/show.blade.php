@extends('users.coordinator.layouts.app')

@section('title', 'Borrow Review')
@section('page-title', 'Borrow Review')
@section('page-subtitle', 'Take the final coordinator action on laboratory in-charge-approved borrow requests')

@section('content')

    @php
        // Read-only mode is opt-in via ?readonly=1. Used when the detail is
        // viewed from a context that shouldn't allow actions (e.g. history).
        $isReadOnly = request()->boolean('readonly');

        // Checkout is still possible only after coordinator approval and
        // before anything has actually been checked out.
        $checkoutStillOpen = in_array($borrowTransaction->status, ['Coordinator Approved', 'Partially Borrowed'], true)
            && ! $borrowTransaction->checked_out_at;
    @endphp

    {{-- Page header. The checkout button is hidden when the transaction is
         read-only or past the checkout window. --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
        <div>
            <h2 class="h4 fw-semibold mb-1 text-dark">{{ $borrowTransaction->borrow_no }}</h2>
            <p class="mb-0 text-secondary">Final review for {{ $borrowTransaction->requesterName() }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @unless ($isReadOnly)
                @if ($checkoutStillOpen)
                    <a href="{{ route('coordinator.checkout.show', $borrowTransaction) }}" class="btn btn-success">
                        <i class="fa-solid fa-barcode me-1"></i> Proceed to checkout
                    </a>
                @endif
            @endunless
            <a href="{{ route('coordinator.borrow.index') }}" class="btn btn-outline-secondary">Back to Queue</a>
        </div>
    </div>

    {{-- Flash message after approve/reject/reschedule. Missing banner on a
         successful action usually means the controller redirected without
         ->with('status', ...). --}}
    @if (session('status'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
    @endif

    {{-- Approval chain indicator (instructor / facilitator / coordinator). --}}
    @include('users.shared.request-approval-indicator', ['approvalRequest' => $borrowTransaction])

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
                            // Status badge tone map. Unknown statuses fall back
                            // to 'secondary' via the default arm.
                            $statusTone = match ($borrowTransaction->status) {
                                'Pending' => 'warning',
                                'Instructor Approved' => 'info',
                                'Facilitator Approved' => 'primary',
                                'Coordinator Approved' => 'success',
                                'Partially Borrowed' => 'warning',
                                'Borrowed' => 'success',
                                'Partially Returned' => 'primary',
                                'Returned' => 'success',
                                'Overdue' => 'danger',
                                    'Rejected' => 'danger',
									'Cancelled' => 'danger',
                                default => 'secondary',
                            };
                            // Human-readable status label.
                            $statusLabel = $borrowTransaction->statusLabel();
                        @endphp
                        <span class="badge text-bg-{{ $statusTone }}">{{ $statusLabel }}</span>
                    </div>

                    {{-- Summary grid. Each helper (requesterName, requesterEmail,
                         etc.) abstracts the underlying requester model — verify
                         these are eager-loaded in the controller to avoid
                         per-row/per-call queries. --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-6"><div class="account-summary-card h-100"><div class="small text-secondary">Student</div><div class="fw-semibold text-dark">{{ $borrowTransaction->requesterName() }}</div><div class="small text-secondary">{{ $borrowTransaction->requesterStudentId() }}</div></div></div>
                        <div class="col-md-6"><div class="account-summary-card h-100"><div class="small text-secondary">Requester contact</div><div class="fw-semibold text-dark">{{ $borrowTransaction->requesterEmail() ?? '—' }}</div><div class="small text-secondary">{{ $borrowTransaction->requesterContactNumber() ?? 'No contact number' }} · {{ $borrowTransaction->requesterDepartmentName() ?? 'No department' }}</div></div></div>
                        <div class="col-md-6"><div class="account-summary-card h-100"><div class="small text-secondary">Borrowed At</div><div class="fw-semibold text-dark">{{ $borrowTransaction->borrowed_at?->format('M d, Y h:i A') ?? '—' }}</div><div class="small text-secondary">Due {{ $borrowTransaction->due_at?->format('M d, Y h:i A') ?? '—' }}</div></div></div>
                        <div class="col-md-6"><div class="account-summary-card h-100"><div class="small text-secondary">Submitted</div><div class="fw-semibold text-dark">{{ $borrowTransaction->created_at?->format('M d, Y h:i A') }}</div><div class="small text-secondary">Updated {{ $borrowTransaction->updated_at?->format('M d, Y h:i A') }}</div></div></div>
                        <div class="col-md-6"><div class="account-summary-card h-100"><div class="small text-secondary">Items</div><div class="fw-semibold text-dark">{{ $borrowTransaction->items->count() }} item{{ $borrowTransaction->items->count() === 1 ? '' : 's' }}</div><div class="small text-secondary">Borrow request</div></div></div>
                    </div>

                    {{-- Optional remarks block — only renders when populated. --}}
                    @if ($borrowTransaction->remarks)
                        <div class="mb-4">
                            <div class="small text-secondary text-uppercase mb-2">Remarks</div>
                            <div class="text-dark">{{ $borrowTransaction->remarks }}</div>
                        </div>
                    @endif

                    {{-- Requested items table. Requires the items relation to be
                         eager-loaded to avoid N+1 on the item lookup below. --}}
                    <div class="mb-4">
                        <h4 class="h6 fw-semibold mb-3 text-dark">Requested Items</h4>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th class="text-dark">Type</th>
                                        <th class="text-dark">Item</th>
                                        <th class="text-dark">Quantity</th>
                                        <th class="text-dark">Condition Out</th>
                                        <th class="text-dark">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($borrowTransaction->items as $item)
                                        <tr>
                                            <td>{{ $item->item_type }}</td>
                                            {{-- Item may be equipment OR chemical — the
                                                 ?? chain picks whichever is loaded.
                                                 If both are null, renders —. --}}
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

                    {{-- Approve/reject form block. Two forms share the same remarks
                         textarea via the data-shared-remarks JS bridge — see the
                         accompanying script (if any) to see how the textarea
                         value is copied into both hidden [data-shared-remarks-field]
                         inputs on submit. --}}
                    @if ($borrowTransaction->status === 'Facilitator Approved' && ! $isReadOnly)
                        <div data-shared-remarks>
                                <div class="card border-0 bg-light h-100">
                                    <div class="card-body p-3 p-xl-4">
                                        {{-- Approve form. Submitted by the button
                                             with form="borrow-approve-form" below —
                                             the button itself is outside this form. --}}
                                        <form id="borrow-approve-form" method="POST" action="{{ route('coordinator.borrow.approve', $borrowTransaction) }}">
                                            @csrf
                                            <input type="hidden" name="remarks" value="{{ old('remarks') }}" data-shared-remarks-field>
                                            <div class="card-body p-3 p-xl-4">
                                                <div class="row g-3 mb-3">
                                                    <div class="col-12">
                                                        <label class="form-label fw-semibold text-dark">Borrowed At</label>
                                                        {{-- datetime-local needs the format
                                                             Y-m-d\TH:i (with literal T).
                                                             Using single backslash here;
                                                             adjust if escaping breaks. --}}
                                                        <input type="datetime-local" name="borrowed_at" value="{{ old('borrowed_at', optional($borrowTransaction->borrowed_at)->format('Y-m-d\TH:i')) }}" class="form-control @error('borrowed_at') is-invalid @enderror" required>
                                                        @error('borrowed_at')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label fw-semibold text-dark">Due At</label>
                                                        <input type="datetime-local" name="due_at" value="{{ old('due_at', optional($borrowTransaction->due_at)->format('Y-m-d\TH:i')) }}" class="form-control @error('due_at') is-invalid @enderror" required>
                                                        @error('due_at')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </form>

                                        <div class="card border-0 bg-light mt-3 mb-0">
                                            <div class="card-body p-3 p-xl-4">
                                                <label for="borrow-action-remarks" class="form-label fw-semibold text-dark mb-1">Remarks</label>
                                                <p class="small text-secondary mb-3">Use the same note for either action. Remarks are optional when approving and required when rejecting.</p>
                                                {{-- Shared textarea. Its value is copied
                                                     into both forms' hidden remarks
                                                     fields by the JS handler. If remarks
                                                     "disappear" on submit, check that
                                                     script. --}}
                                                <textarea id="borrow-action-remarks" rows="3" class="form-control @error('remarks') is-invalid @enderror" data-shared-remarks-input placeholder="Add an approval note or explain why the request is rejected">{{ old('remarks') }}</textarea>
                                                @error('remarks')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                            </div>
                                        </div>

                                        <div>
                                            {{-- Reject form is separate from approve,
                                                 each POSTing to its own endpoint.
                                                 The Approve button uses the form=""
                                                 attribute to submit the form declared
                                                 above — the button itself lives outside
                                                 the <form> tag so this layout works. --}}
                                            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
                                                <form id="borrow-reject-form" method="POST" action="{{ route('coordinator.borrow.reject', $borrowTransaction) }}" class="d-inline-block">
                                                    @csrf
                                                    <input type="hidden" name="remarks" value="{{ old('remarks') }}" data-shared-remarks-field>
                                                    <button type="submit" class="btn btn-danger" style="min-width: 100px;" onclick="return confirm('Reject this borrow request?');">Reject</button>
                                                </form>
                                                <button type="submit" form="borrow-approve-form" class="btn btn-success" style="min-width: 100px;" onclick="return confirm('Approve this borrow request and forward it?');">Approve</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    @endif

                    {{-- Reschedule form — only shown for Coordinator Approved
                         transactions where nothing has been checked out yet. --}}
                    @if (! $isReadOnly && $borrowTransaction->status === 'Coordinator Approved')
                        <div class="card border-0 bg-light mt-3">
                            <div class="card-body p-3 p-xl-4">
                                <h4 class="h6 fw-semibold text-dark mb-2">Reschedule checkout</h4>
                                <p class="small text-secondary mb-3">Update the checkout schedule before any item has been checked out.</p>
                                <form method="POST" action="{{ route('coordinator.borrow.reschedule', $borrowTransaction) }}">
                                    @csrf
                                    @method('PATCH')
                                    <div class="row g-3 mb-3">
                                        <div class="col-12">
                                            <label class="form-label fw-semibold text-dark">Borrowed At</label>
                                            {{-- Note: the format string here uses
                                                 'Y-m-d\\TH:i' (double backslash) while
                                                 the approve form above uses 'Y-m-d\TH:i'
                                                 (single backslash). Both work in Blade
                                                 because the single backslash is being
                                                 treated as an escape for T, but the
                                                 double backslash produces a literal
                                                 backslash in the datetime value,
                                                 which browsers silently reject. If
                                                 reschedule dates come back empty,
                                                 normalize to 'Y-m-d\TH:i'. --}}
                                            <input type="datetime-local" name="borrowed_at" value="{{ old('borrowed_at', optional($borrowTransaction->borrowed_at)->format('Y-m-d\\TH:i')) }}" class="form-control @error('borrowed_at') is-invalid @enderror" required>
                                            @error('borrowed_at')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold text-dark">Due At</label>
                                            <input type="datetime-local" name="due_at" value="{{ old('due_at', optional($borrowTransaction->due_at)->format('Y-m-d\\TH:i')) }}" class="form-control @error('due_at') is-invalid @enderror" required>
                                            @error('due_at')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    {{-- Status-level error surfaced separately —
                                         typically a "no longer reschedulable" message. --}}
                                    @error('status')<div class="alert alert-danger small border-0">{{ $message }}</div>@enderror
                                    <button type="submit" class="btn btn-primary w-100" onclick="return confirm('Reschedule this borrow request?');">Save new schedule</button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            {{-- Transaction log column. Currently a stub — expand here if the
                 page ever needs a full status history. --}}
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