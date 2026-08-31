@extends('users.facilitator.layouts.app')

@section('title', 'Checkout '.$borrowTransaction->borrow_no)
@section('page-title', 'Checkout '.$borrowTransaction->borrow_no)

@section('nav-links')
    @include('users.facilitator.partials.nav-links', ['active' => 'checkout'])
@endsection

@section('content')
    @php
        $borrowerName = trim(collect([$borrowTransaction->borrower?->first_name, $borrowTransaction->borrower?->middle_name, $borrowTransaction->borrower?->last_name, $borrowTransaction->borrower?->suffix])->filter()->implode(' '));
        $completed = $borrowTransaction->status === 'Borrowed';
        $totalRequested = $borrowTransaction->items->sum(fn ($item) => (float) $item->quantity_borrowed);
        $totalCheckedOut = $borrowTransaction->items->sum(fn ($item) => (float) ($item->quantity_checked_out ?? 0));
        $scanCount = $scanLogs->count();
    @endphp

    <div class="account-page" data-barcode-checkout>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <a href="{{ route('facilitator.checkout.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i> Checkout queue
            </a>
            <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small"><span id="scan-count">{{ $scanCount }}</span> scan{{ $scanCount === 1 ? '' : 's' }}</span>
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
                    <div id="scanned-cart" class="card-body p-4 scanned-cart-scroll" data-remove-url-template="{{ route('facilitator.checkout.remove', ['borrowTransaction' => $borrowTransaction, 'barcodeLog' => '__SCAN__']) }}">
                        @forelse ($scanLogs as $log)
                            @php
                                $logItemName = $log->item?->equipment_name ?? $log->item?->chemical_name ?? 'Item unavailable';
                                $logUnit = $log->item_type === 'Chemical' ? ' '.($log->item?->unit ?? 'unit') : ' unit(s)';
                            @endphp
                            <div class="d-flex align-items-center gap-3 py-3 {{ !$loop->last ? 'border-bottom' : '' }}" data-scan-row data-scan-id="{{ $log->id }}">
                                <div class="rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px;">
                                    <i class="fa-solid fa-{{ $log->item_type === 'Chemical' ? 'flask' : 'microscope' }}"></i>
                                </div>
                                <div class="flex-grow-1 min-width-0">
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <span class="fw-semibold text-dark">{{ $logItemName }}</span>
                                        <span class="badge rounded-pill text-bg-light border text-secondary">{{ $log->item_type }}</span>
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
                            <div class="d-flex align-items-center gap-3 py-3 {{ !$loop->last ? 'border-bottom' : '' }}" data-checklist-key="{{ $item->item_type }}:{{ $item->item_id }}" data-item-type="{{ $item->item_type }}">
                                <div class="flex-grow-1">
                                    <div class="fw-semibold text-dark">{{ $itemName }}</div>
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

                        @if (!$canCheckout && !$completed)
                            <div class="alert alert-warning small border-0">
                                Checkout opens at {{ $borrowTransaction->borrowed_at?->format('M d, Y h:i A') ?? 'the scheduled borrow time' }}.
                            </div>
                        @elseif ($completed)
                            <div class="alert alert-success small border-0">
                                <i class="fa-solid fa-circle-check me-1"></i>All approved items are checked out.
                            </div>
                        @endif

                        <div id="ajax-feedback" class="alert border-0 small d-none" role="alert"></div>

                            <form method="POST" action="{{ route('facilitator.checkout.scan', $borrowTransaction) }}" id="checkout-scan-form">
                                @csrf
                                <div class="mb-3">
                                    <label for="barcode" class="form-label fw-semibold text-dark">Barcode</label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-white"><i class="fa-solid fa-barcode text-primary"></i></span>
                                        <input type="text" name="barcode" id="barcode" class="form-control" autocomplete="off" autofocus required {{ !$canCheckout || $completed ? 'disabled' : '' }} placeholder="Scan barcode">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="quantity" class="form-label fw-semibold text-dark">Quantity <span class="fw-normal text-secondary">(optional)</span></label>
                                    <input type="number" name="quantity" id="quantity" class="form-control" min="0.01" step="0.01" {{ !$canCheckout || $completed ? 'disabled' : '' }} placeholder="Equipment: 1 · Chemical: remaining">
                                </div>
                                <div class="mb-4">
                                    <label for="condition_out" class="form-label fw-semibold text-dark">Condition</label>
                                    <select name="condition_out" id="condition_out" class="form-select" {{ !$canCheckout || $completed ? 'disabled' : '' }}>
                                        @foreach (['Excellent', 'Good', 'Fair'] as $condition)
                                            <option value="{{ $condition }}" @selected($condition === 'Good')>{{ $condition }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="button" class="btn btn-primary btn-lg w-100" id="start-scanner" {{ !$canCheckout || $completed ? 'disabled' : '' }}>
                                    <i class="fa-solid fa-barcode me-1"></i> Start scanner
                                </button>
                                <button type="submit" class="visually-hidden" tabindex="-1">Submit scan</button>
                            </form>
                            <div id="scanner-help" class="small text-success mt-3 d-none"><i class="fa-solid fa-circle-dot me-1"></i>Scanner active — scan the item now.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
