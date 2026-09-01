@extends('users.facilitator.layouts.app')

@section('title', 'Borrow Review')
@section('user-name', 'Laboratory In-charge')
@section('user-role', 'Laboratory In-charge')

@section('nav-links')
    @include('users.facilitator.partials.nav-links', ['active' => 'borrow'])
@endsection

@section('content')
    <div class="account-page">
        <section class="hero-banner card border-0 mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <h2 class="h3 fw-semibold mb-2 text-dark">{{ $borrowTransaction->borrow_no }}</h2>
                    <p class="mb-0 text-secondary">Check the requested items and decide whether to approve or reject the request.</p>
                </div>
                <a href="{{ route('facilitator.borrow.index') }}" class="btn btn-outline-secondary px-4">Back to Queue</a>
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
                                <p class="mb-0 text-secondary">Verify the requested items before approving.</p>
                            </div>
                            @php
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
                                    default => 'secondary',
                                };
                            @endphp
                            <span class="badge text-bg-{{ $statusTone }}">{{ $borrowTransaction->status }}</span>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6"><div class="account-summary-card h-100"><div class="small text-secondary">Student</div><div class="fw-semibold text-dark">{{ $borrowTransaction->borrower?->first_name }} {{ $borrowTransaction->borrower?->last_name }}</div><div class="small text-secondary">{{ $borrowTransaction->borrower?->userID }}</div></div></div>
                            <div class="col-md-6"><div class="account-summary-card h-100"><div class="small text-secondary">Borrowed At</div><div class="fw-semibold text-dark">{{ $borrowTransaction->borrowed_at?->format('M d, Y h:i A') ?? '—' }}</div><div class="small text-secondary">Due {{ $borrowTransaction->due_at?->format('M d, Y h:i A') ?? '—' }}</div></div></div>
                            <div class="col-md-6"><div class="account-summary-card h-100"><div class="small text-secondary">Updated</div><div class="fw-semibold text-dark">{{ $borrowTransaction->updated_at?->format('M d, Y h:i A') }}</div><div class="small text-secondary">Submitted {{ $borrowTransaction->created_at?->format('M d, Y h:i A') }}</div></div></div>
                            <div class="col-md-6"><div class="account-summary-card h-100"><div class="small text-secondary">Items</div><div class="fw-semibold text-dark">{{ $borrowTransaction->items->count() }} item{{ $borrowTransaction->items->count() === 1 ? '' : 's' }}</div><div class="small text-secondary">Borrow request</div></div></div>
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

                        @if ($borrowTransaction->status === 'Instructor Approved')
                            <div data-shared-remarks>
                            <div class="row g-3">
                                <div class="col-12">
                                    <form method="POST" action="{{ route('facilitator.borrow.approve', $borrowTransaction) }}" class="card border-0 bg-light h-100">
                                        @csrf
                                        <input type="hidden" name="remarks" value="{{ old('remarks') }}" data-shared-remarks-field>
                                        <div class="card-body p-3 p-xl-4">
                                            <h4 class="h5 fw-semibold text-dark mb-2">Approve Request</h4>
                                            <p class="small text-secondary mb-3">Use this after verifying that the laboratory and items are available. Quantities can be adjusted before approval.</p>

                                            <div class="table-responsive mb-3">
                                                <table class="table align-middle table-sm">
                                                    <thead>
                                                        <tr class="text-secondary small text-uppercase">
                                                            <th>Item</th>
                                                            <th class="text-end">Quantity<span class="required-indicator text-danger" aria-hidden="true">*</span><span class="visually-hidden"> (required)</span></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($borrowTransaction->items as $item)
                                                            @php
                                                                $quantityStep = $item->item_type === 'Chemical' ? '0.01' : '1';
                                                                $quantityMin = $item->item_type === 'Chemical' ? '0.01' : '1';
                                                            @endphp
                                                            <tr>
                                                                <td>
                                                                    <div class="fw-semibold text-dark">{{ $item->item?->equipment_name ?? $item->item?->chemical_name ?? '—' }}</div>
                                                                    <div class="small text-secondary">Requested: {{ $item->quantity_borrowed }}</div>
                                                                </td>
                                                                <td style="max-width: 150px;">
                                                                    <input type="number" step="{{ $quantityStep }}" min="{{ $quantityMin }}" name="items[{{ $item->id }}][quantity]" value="{{ old('items.' . $item->id . '.quantity', $item->item_type === 'Chemical' ? number_format((float) $item->quantity_borrowed, 2, '.', '') : (int) $item->quantity_borrowed) }}"
                                                                        class="form-control text-end @error('items.' . $item->id . '.quantity') is-invalid @enderror" aria-label="Quantity for {{ $item->item?->equipment_name ?? $item->item?->chemical_name ?? 'item' }}" required>
                                                                    @error('items.' . $item->id . '.quantity')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr><td colspan="2" class="text-center text-secondary py-3">No items were attached to this borrow request.</td></tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>

                                            <button type="submit" class="btn btn-success w-100" onclick="return confirm('Approve this borrow request and forward it?');">Approve and Forward</button>
                                        </div>
                                    </form>
                                </div>

                                <div class="col-12">
                                    <form method="POST" action="{{ route('facilitator.borrow.reject', $borrowTransaction) }}" class="card border-0 bg-light h-100">
                                        @csrf
                                        <input type="hidden" name="remarks" value="{{ old('remarks') }}" data-shared-remarks-field>
                                        <div class="card-body p-3 p-xl-4">
                                            <h4 class="h5 fw-semibold text-dark mb-2">Reject Request</h4>
                                            <p class="small text-secondary mb-3">Write a reason so the requester knows what to correct.</p>
                                            <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Reject this borrow request?');">Reject</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="card border-0 bg-light mt-3">
                                <div class="card-body p-3 p-xl-4">
                                    <label for="borrow-action-remarks" class="form-label fw-semibold text-dark mb-1">Remarks</label>
                                    <p class="small text-secondary mb-3">Use the same note for either action. Remarks are optional when approving and required when rejecting.</p>
                                    <textarea id="borrow-action-remarks" rows="3" class="form-control @error('remarks') is-invalid @enderror" data-shared-remarks-input placeholder="Add an approval note or explain why the request is rejected">{{ old('remarks') }}</textarea>
                                    @error('remarks')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            </div>
                        @endif
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
