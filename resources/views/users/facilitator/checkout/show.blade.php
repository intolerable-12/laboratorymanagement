@extends($isCoordinator ? 'users.coordinator.layouts.app' : 'users.facilitator.layouts.app')

@section('title', 'Checkout '.$borrowTransaction->borrow_no)
@section('page-title', 'Checkout '.$borrowTransaction->borrow_no)

@section('nav-links')
    @if (! $isCoordinator)
        @include('users.facilitator.partials.nav-links', ['active' => 'checkout'])
    @endif
@endsection

@php
    $checkoutRoutePrefix = $isCoordinator ? 'coordinator.checkout' : 'facilitator.checkout';
@endphp

@section('content')
    @php
        $borrowerName = trim(collect([$borrowTransaction->borrower?->first_name, $borrowTransaction->borrower?->middle_name, $borrowTransaction->borrower?->last_name, $borrowTransaction->borrower?->suffix])->filter()->implode(' '));
        $completed = $borrowTransaction->status === 'Borrowed';
        $totalRequested = $borrowTransaction->items->sum(fn ($item) => (float) $item->quantity_borrowed);
        $totalCheckedOut = $borrowTransaction->items->sum(fn ($item) => (float) ($item->quantity_checked_out ?? 0));
        $scanCount = $scanLogs->count();
        $scanConditions = ['Excellent', 'Good', 'Fair', 'Damaged', 'Under Repair', 'Lost'];
    @endphp

    <div class="account-page" data-barcode-checkout>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <a href="{{ route($checkoutRoutePrefix.'.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i> Checkout queue
            </a>
            <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small"><span id="scan-count">{{ $scanCount }}</span> scan{{ $scanCount === 1 ? '' : 's' }}</span>
                @if ($isCheckoutOverdue)
                    <span class="badge text-bg-danger px-3 py-2"><i class="fa-solid fa-triangle-exclamation me-1"></i>Overdue checkout</span>
                @endif
                <span id="checkout-status" class="badge text-bg-{{ $completed ? 'success' : ($borrowTransaction->status === 'Partially Borrowed' ? 'warning' : 'primary') }} px-3 py-2">{{ $borrowTransaction->status }}</span>
            </div>
        </div>

        @if (session('scan_status'))
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
                <i class="fa-solid fa-circle-check me-1"></i>{{ session('scan_status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
                <div class="fw-semibold mb-1">Checkout could not be completed.</div>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <section class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4 p-xl-5">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-5">
                        <div class="text-uppercase small fw-semibold text-secondary mb-1">Borrower</div>
                        <h1 class="h3 fw-semibold text-dark mb-1">{{ $borrowerName ?: 'Student' }}</h1>
                        <div class="text-secondary">{{ $borrowTransaction->borrower?->userID ?? '—' }} · {{ $borrowTransaction->laboratory?->laboratory_name ?? 'Laboratory not specified' }}</div>
                    </div>
                    <div class="col-sm-4 col-lg-2">
                        <div class="text-uppercase small fw-semibold text-secondary mb-1">Borrow no.</div>
                        <div class="fw-semibold text-dark">{{ $borrowTransaction->borrow_no }}</div>
                    </div>
                    <div class="col-sm-4 col-lg-2">
                        <div class="text-uppercase small fw-semibold text-secondary mb-1">Scheduled</div>
                        <div class="fw-semibold text-dark">{{ $borrowTransaction->borrowed_at?->format('M d, Y') ?? '—' }}</div>
                        <div class="small text-secondary">{{ $borrowTransaction->borrowed_at?->format('h:i A') ?? '—' }}</div>
                    </div>
                    <div class="col-sm-4 col-lg-3">
                        <div class="text-uppercase small fw-semibold text-secondary mb-1">Checkout total</div>
                        <div id="checkout-total" class="fw-semibold text-dark" data-current="{{ $totalCheckedOut }}" data-requested="{{ $totalRequested }}">{{ number_format($totalCheckedOut, 2) }} / {{ number_format($totalRequested, 2) }}</div>
                        <div class="small text-secondary">Due {{ $borrowTransaction->due_at?->format('M d, Y h:i A') ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </section>

        <div class="row g-4 align-items-start">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 p-4 pb-0">
                        <div class="d-flex justify-content-between align-items-center gap-3">
                            <div>
                                <h2 class="h4 fw-semibold text-dark mb-1"><i class="fa-solid fa-cart-shopping text-primary me-2"></i>Scanned cart</h2>
                                <p class="small text-secondary mb-0">Every scan is added as a checkout line item.</p>
                            </div>
                            <span class="badge rounded-pill text-bg-light border text-dark px-3 py-2"><span id="cart-count">{{ $scanCount }}</span> line{{ $scanCount === 1 ? '' : 's' }}</span>
                        </div>
                    </div>
                    <div class="card-body p-4" data-scan-cart-shell>
                        <div class="btn-group shadow-sm flex-wrap mb-3" data-scan-filter-tabs role="group" aria-label="Filter checkout scans by condition">
                            <button type="button" class="btn btn-primary px-4 py-2" data-scan-filter="all" aria-pressed="true">
                                All <span class="badge bg-white text-primary ms-2" data-scan-filter-count>{{ $scanCount }}</span>
                            </button>
                            @foreach ($scanConditions as $condition)
                                @php
                                    $conditionCount = $scanLogs->filter(function ($log) use ($borrowTransaction, $condition) {
                                        $item = $borrowTransaction->items->first(fn ($borrowItem) => $borrowItem->item_type === $log->item_type && (int) $borrowItem->item_id === (int) $log->item_id);

                                        return ($item?->condition_out ?? 'Good') === $condition;
                                    })->count();
                                @endphp
                                <button type="button" class="btn btn-outline-secondary px-4 py-2" data-scan-filter="{{ $condition }}" aria-pressed="false">
                                    {{ $condition }} <span class="badge bg-secondary text-white ms-2" data-scan-filter-count>{{ $conditionCount }}</span>
                                </button>
                            @endforeach
                        </div>
                        <div id="scanned-cart" class="scanned-cart-scroll" data-remove-url-template="{{ route($checkoutRoutePrefix.'.remove', ['borrowTransaction' => $borrowTransaction, 'barcodeLog' => '__SCAN__']) }}">
                        @forelse ($scanLogs as $log)
                            @php
                                $logItemName = $log->item?->equipment_name ?? $log->item?->chemical_name ?? 'Item unavailable';
                                $logUnit = $log->item_type === 'Chemical' ? ' '.($log->item?->unit ?? 'unit') : ' unit(s)';
                                $logBorrowItem = $borrowTransaction->items->first(fn ($borrowItem) => $borrowItem->item_type === $log->item_type && (int) $borrowItem->item_id === (int) $log->item_id);
                                $logCondition = $logBorrowItem?->condition_out ?? 'Good';
                            @endphp
                            <div class="d-flex align-items-center gap-3 py-3 {{ !$loop->last ? 'border-bottom' : '' }}" data-scan-row data-scan-id="{{ $log->id }}" data-scan-condition="{{ $logCondition }}">
                                <div class="rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px;">
                                    <i class="fa-solid fa-{{ $log->item_type === 'Chemical' ? 'flask' : 'microscope' }}"></i>
                                </div>
                                <div class="flex-grow-1 min-width-0">
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <span class="fw-semibold text-dark">
                                            {{ $logItemName }}
                                            @if ($log->item_type === 'Chemical' && $log->item?->is_expired)
                                                <span class="badge text-bg-danger ms-1">Expired</span>
                                            @endif
                                        </span>
                                        <span class="badge rounded-pill text-bg-light border text-secondary">{{ $log->item_type }}</span>
                                        <span class="badge rounded-pill text-bg-{{ in_array($logCondition, ['Damaged', 'Under Repair', 'Lost'], true) ? 'danger' : 'success' }}">{{ $logCondition }}</span>
                                    </div>
                                    <div class="small text-secondary mt-1">
                                        <i class="fa-solid fa-barcode me-1"></i>{{ $log->barcode }}
                                        <span class="mx-1">·</span>{{ $log->scanned_at?->format('M d, Y h:i A') ?? '—' }}
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-3 flex-shrink-0">
                                    <div class="text-end">
                                        <div class="fw-semibold text-dark">× {{ number_format((float) $log->quantity, $log->item_type === 'Chemical' ? 2 : 0) }}</div>
                                        <div class="small text-secondary">{{ $logUnit }}</div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-link text-danger p-1 remove-scan" data-remove-scan="{{ $log->id }}" title="Remove this scan" aria-label="Remove {{ $logItemName }} from cart">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div id="empty-cart" class="text-center py-5">
                                <div class="rounded-circle bg-light text-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                                    <i class="fa-solid fa-cart-shopping fa-lg"></i>
                                </div>
                                <h3 class="h5 fw-semibold text-dark">Cart is empty</h3>
                                <p class="small text-secondary mb-0">Scanned equipment and chemicals will appear here.</p>
                            </div>
                        @endforelse
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 p-4 pb-0">
                        <h2 class="h5 fw-semibold text-dark mb-1">Approved items checklist</h2>
                        <p class="small text-secondary mb-0">Use this to confirm the cart matches the approved request.</p>
                    </div>
                    <div class="card-body p-4">
                        @foreach ($borrowTransaction->items as $item)
                            @php
                                $requested = (float) $item->quantity_borrowed;
                                $checkedOut = (float) ($item->quantity_checked_out ?? 0);
                                $remaining = max(0, round($requested - $checkedOut, 2));
                                $itemName = $item->item?->equipment_name ?? $item->item?->chemical_name ?? 'Item unavailable';
                                $precision = $item->item_type === 'Chemical' ? 2 : 0;
                            @endphp
                            <div class="d-flex align-items-center gap-3 py-3 {{ !$loop->last ? 'border-bottom' : '' }}" data-checklist-key="{{ $item->item_type }}:{{ $item->item_id }}" data-item-type="{{ $item->item_type }}" data-checklist-barcode="{{ $item->item?->barcode ?? '' }}" data-item-name="{{ $itemName }}" data-item-unit="{{ $item->item_type === 'Chemical' ? ($item->item?->unit ?? 'unit') : 'unit(s)' }}">
                                <div class="flex-grow-1">
                                    <div class="fw-semibold text-dark">
                                        {{ $itemName }}
                                        @if ($item->item_type === 'Chemical' && $item->item?->is_expired)
                                            <span class="badge text-bg-danger ms-1">Expired</span>
                                        @endif
                                    </div>
                                    <div class="small text-secondary">{{ $item->item_type }} · {{ $item->item?->barcode ?? 'Barcode unavailable' }}</div>
                                </div>
                                <div class="text-end">
                                    <div data-progress-current class="fw-semibold {{ $remaining <= 0 ? 'text-success' : 'text-dark' }}">{{ number_format($checkedOut, $precision) }} / {{ number_format($requested, $precision) }}</div>
                                    <div data-progress-remaining class="small text-{{ $remaining <= 0 ? 'success' : 'secondary' }}">{{ $remaining <= 0 ? 'Complete' : number_format($remaining, $precision).' remaining' }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm position-sticky" style="top: 1rem;">
                    <div class="card-body p-4 p-xl-5">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="rounded-3 bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 42px; height: 42px;"><i class="fa-solid fa-barcode"></i></span>
                            <div>
                                <h2 class="h4 fw-semibold text-dark mb-0">Scan item</h2>
                                <div class="small text-secondary">POS checkout station</div>
                            </div>
                        </div>
                        <p class="text-secondary small mb-4">Start the scanner, then scan the barcode. Your USB HID scanner types into the focused field like a keyboard.</p>

                        @if ($isCheckoutOverdue && !$completed)
                            <div class="alert alert-danger small border-0">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i>Checkout is overdue. It was scheduled for {{ $borrowTransaction->borrowed_at?->format('M d, Y') ?? 'the scheduled borrow date' }}, but checkout is still allowed.
                            </div>
                        @elseif (!$canCheckout && !$completed)
                            <div class="alert alert-warning small border-0">
                                Checkout opens on {{ $borrowTransaction->borrowed_at?->format('M d, Y') ?? 'the scheduled borrow date' }}.
                            </div>
                        @elseif ($completed)
                            <div class="alert alert-success small border-0">
                                <i class="fa-solid fa-circle-check me-1"></i>All approved items are checked out.
                            </div>
                        @endif

                        <div id="ajax-feedback" class="alert border-0 small d-none" role="alert"></div>

                            <form method="POST" action="{{ route($checkoutRoutePrefix.'.scan', $borrowTransaction) }}" id="checkout-scan-form">
                                @csrf
                                <div class="mb-3">
                                    <label for="barcode" class="form-label fw-semibold text-dark">Barcode</label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-white"><i class="fa-solid fa-barcode text-primary"></i></span>
                                        <input type="text" name="barcode" id="barcode" class="form-control" autocomplete="off" required {{ !$canCheckout || $completed ? 'disabled' : '' }} placeholder="Scan barcode">
                                    </div>
                                </div>
                                <button type="button" class="btn btn-primary btn-lg w-100" id="start-scanner" {{ !$canCheckout || $completed ? 'disabled' : '' }}>
                                    <i class="fa-solid fa-barcode me-1"></i> Start scanner
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-lg w-100 d-none" id="stop-scanner" {{ !$canCheckout || $completed ? 'disabled' : '' }}>
                                    <i class="fa-solid fa-stop me-1"></i> Stop scanning
                                </button>
                            </form>
                            <div id="scanner-help" class="small text-success mt-3 d-none"><i class="fa-solid fa-circle-dot me-1"></i>Scanner active — scan the item now.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="checkout-quantity-modal" tabindex="-1" aria-labelledby="checkout-quantity-modal-title" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <div class="small text-uppercase fw-semibold text-secondary mb-1">Barcode captured</div>
                        <h2 class="modal-title h4 fw-semibold text-dark" id="checkout-quantity-modal-title">Enter checkout quantity</h2>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cancel quantity entry"></button>
                </div>
                <div class="modal-body">
                    <div class="rounded-3 bg-light p-3 mb-4">
                        <div class="small text-secondary">Item</div>
                        <div class="fw-semibold text-dark" data-checkout-item-name>Scanned item</div>
                        <div class="small text-secondary mt-2" data-checkout-item-state-label>Equipment condition</div>
                        <div class="fw-semibold text-dark" data-checkout-item-state>Unknown</div>
                    </div>
                    <label for="quantity" class="form-label fw-semibold text-dark">Quantity</label>
                    <div class="input-group input-group-lg">
                        <input type="number" name="quantity" id="quantity" class="form-control" min="0.01" step="0.01" required disabled form="checkout-scan-form" placeholder="Enter quantity">
                        <span class="input-group-text bg-white" data-checkout-unit>unit(s)</span>
                    </div>
                    <div class="form-text">Enter the quantity using the item’s listed unit.</div>
                    <div class="mt-3">
                        <label for="condition_out" class="form-label fw-semibold text-dark" data-checkout-condition-label>Equipment condition</label>
                        <select name="condition_out" id="condition_out" class="form-select" form="checkout-scan-form" required disabled>
                            @foreach (['Excellent', 'Good', 'Fair', 'Damaged', 'Under Repair', 'Lost'] as $condition)
                                <option value="{{ $condition }}" @selected($condition === 'Good')>{{ $condition }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="submit-checkout-scan" disabled form="checkout-scan-form"><i class="fa-solid fa-check me-1"></i> Confirm checkout</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="checkout-complete-modal" tabindex="-1" aria-labelledby="checkout-complete-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-body text-center p-5">
                    <div class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                        <i class="fa-solid fa-circle-check fa-2x"></i>
                    </div>
                    <h2 class="h4 fw-semibold text-dark mb-2" id="checkout-complete-modal-title">Checkout complete</h2>
                    <p class="text-secondary mb-4">All items have been successfully scanned and checked out.</p>
                    <button type="button" class="btn btn-success px-4" data-bs-dismiss="modal">Continue</button>
                </div>
            </div>
        </div>
    </div>

@endsection
