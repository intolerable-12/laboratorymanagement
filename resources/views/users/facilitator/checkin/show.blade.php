@extends($isCoordinator ? 'users.coordinator.layouts.app' : 'users.facilitator.layouts.app')

@section('title', 'Check In '.$borrowTransaction->borrow_no)
@section('page-title', 'Check In '.$borrowTransaction->borrow_no)

@section('nav-links')
    @if (! $isCoordinator)
        @include('users.facilitator.partials.nav-links', ['active' => 'checkin'])
    @endif
@endsection

@php
    $checkinRoutePrefix = $isCoordinator ? 'coordinator.checkin' : 'facilitator.checkin';
@endphp

@section('content')
    @php
        $borrowerName = trim(collect([$borrowTransaction->borrower?->first_name, $borrowTransaction->borrower?->middle_name, $borrowTransaction->borrower?->last_name, $borrowTransaction->borrower?->suffix])->filter()->implode(' '));
        $completed = $borrowTransaction->status === 'Returned';
        $totalCheckedOut = $borrowTransaction->items->sum(fn ($item) => (float) ($item->quantity_checked_out ?? 0));
        $totalReturned = $borrowTransaction->items->sum(fn ($item) => (float) $item->quantity_returned);
        $totalUsed = $borrowTransaction->items->where('item_type', 'Chemical')->sum(fn ($item) => (float) ($item->quantity_used ?? 0));
        $totalAccounted = $borrowTransaction->items->sum(fn ($item) => (float) $item->quantity_returned + (float) ($item->quantity_used ?? 0) + (float) $item->quantity_lost + (float) $item->quantity_damaged);
        $scanCount = $scanLogs->count();
        $scanConditions = ['Excellent', 'Good', 'Fair', 'Damaged', 'Under Repair', 'Lost'];
    @endphp

    <div class="account-page" data-barcode-checkin>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <a href="{{ route($checkinRoutePrefix.'.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i> Check-in queue
            </a>
            <div class="d-flex align-items-center gap-2">
                <span class="text-secondary small"><span id="checkin-scan-count">{{ $scanCount }}</span> scan{{ $scanCount === 1 ? '' : 's' }}</span>
                <span id="checkin-status" class="badge text-bg-{{ $completed ? 'success' : ($borrowTransaction->status === 'Partially Returned' ? 'warning' : 'primary') }} px-3 py-2">{{ $borrowTransaction->status }}</span>
            </div>
        </div>

        @if (session('checkin_status'))
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4"><i class="fa-solid fa-circle-check me-1"></i>{{ session('checkin_status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
                <div class="fw-semibold mb-1">Check-in could not be completed.</div>
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
                        <div class="text-uppercase small fw-semibold text-secondary mb-1">Due</div>
                        <div class="fw-semibold text-dark">{{ $borrowTransaction->due_at?->format('M d, Y') ?? '—' }}</div>
                        <div class="small text-secondary">{{ $borrowTransaction->due_at?->format('h:i A') ?? '—' }}</div>
                    </div>
                    <div class="col-sm-4 col-lg-3">
                        <div class="text-uppercase small fw-semibold text-secondary mb-1">Return progress</div>
                        <div id="checkin-total" class="fw-semibold text-dark" data-accounted="{{ $totalAccounted }}" data-checked-out="{{ $totalCheckedOut }}">{{ number_format($totalAccounted, 2) }} / {{ number_format($totalCheckedOut, 2) }}</div>
                        <div class="small text-secondary">Returned <span id="returned-total">{{ number_format($totalReturned, 2) }}</span> · Used <span id="used-total">{{ number_format($totalUsed, 2) }}</span></div>
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
                                <h2 class="h4 fw-semibold text-dark mb-1"><i class="fa-solid fa-cart-shopping text-primary me-2"></i>Checked-in cart</h2>
                                <p class="small text-secondary mb-0">Each return scan records its quantity and condition.</p>
                            </div>
                            <span class="badge rounded-pill text-bg-light border text-dark px-3 py-2"><span id="checkin-cart-count">{{ $scanCount }}</span> line{{ $scanCount === 1 ? '' : 's' }}</span>
                        </div>
                    </div>
                    <div class="card-body p-4" data-scan-cart-shell>
                        <div class="btn-group shadow-sm flex-wrap mb-3" data-scan-filter-tabs role="group" aria-label="Filter check-in scans by condition">
                            <button type="button" class="btn btn-primary px-4 py-2" data-scan-filter="all" aria-pressed="true">
                                All <span class="badge bg-white text-primary ms-2" data-scan-filter-count>{{ $scanCount }}</span>
                            </button>
                            @foreach ($scanConditions as $condition)
                                @php $conditionCount = $scanLogs->filter(fn ($log) => ($log->condition_in ?? 'Good') === $condition)->count(); @endphp
                                <button type="button" class="btn btn-outline-secondary px-4 py-2" data-scan-filter="{{ $condition }}" aria-pressed="false">
                                    {{ $condition }} <span class="badge bg-secondary text-white ms-2" data-scan-filter-count>{{ $conditionCount }}</span>
                                </button>
                            @endforeach
                        </div>
                        <div id="checkin-cart" class="scanned-cart-scroll" data-remove-url-template="{{ route($checkinRoutePrefix.'.remove', ['borrowTransaction' => $borrowTransaction, 'barcodeLog' => '__SCAN__']) }}">
                        @forelse ($scanLogs as $log)
                            @php
                                $logItemName = $log->item?->equipment_name ?? $log->item?->chemical_name ?? 'Item unavailable';
                                $logUnit = $log->item_type === 'Chemical' ? ($log->item?->unit ?? 'unit') : 'unit(s)';
                            @endphp
                            <div class="d-flex align-items-center gap-3 py-3 {{ !$loop->last ? 'border-bottom' : '' }}" data-checkin-row data-checkin-id="{{ $log->id }}" data-scan-condition="{{ $log->condition_in ?? 'Good' }}">
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
                                        <span class="badge rounded-pill text-bg-{{ in_array($log->condition_in, ['Damaged', 'Under Repair', 'Lost'], true) ? 'danger' : 'success' }}">{{ $log->condition_in ?? 'Good' }}</span>
                                    </div>
                                    <div class="small text-secondary mt-1"><i class="fa-solid fa-barcode me-1"></i>{{ $log->barcode }} · {{ $log->scanned_at?->format('M d, Y h:i A') ?? '—' }}</div>
                                </div>
                                <div class="d-flex align-items-center gap-3 flex-shrink-0">
                                    <div class="text-end">
                                        <div class="fw-semibold text-dark">× {{ number_format((float) $log->quantity, $log->item_type === 'Chemical' ? 2 : 0) }}</div>
                                        <div class="small text-secondary">{{ $logUnit }}</div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-link text-danger p-1" data-remove-checkin="{{ $log->id }}" title="Remove this scan" aria-label="Remove {{ $logItemName }} from cart"><i class="fa-solid fa-trash-can"></i></button>
                                </div>
                            </div>
                        @empty
                            <div id="empty-checkin-cart" class="text-center py-5">
                                <div class="rounded-circle bg-light text-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;"><i class="fa-solid fa-rotate-left fa-lg"></i></div>
                                <h3 class="h5 fw-semibold text-dark">Cart is empty</h3>
                                <p class="small text-secondary mb-0">Scanned returned equipment and chemicals will appear here.</p>
                            </div>
                        @endforelse
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 p-4 pb-0">
                        <h2 class="h5 fw-semibold text-dark mb-1">Return checklist</h2>
                        <p class="small text-secondary mb-0">Chemicals show returned and used quantities in their own unit.</p>
                    </div>
                    <div class="card-body p-4">
                        @foreach ($borrowTransaction->items as $item)
                            @php
                                $checkedOut = (float) ($item->quantity_checked_out ?? 0);
                                $returned = (float) $item->quantity_returned;
                                $used = (float) ($item->quantity_used ?? 0);
                                $lost = (float) $item->quantity_lost;
                                $damaged = (float) $item->quantity_damaged;
                                $accounted = $returned + $used + $lost + $damaged;
                                $precision = $item->item_type === 'Chemical' ? 2 : 0;
                                $itemName = $item->item?->equipment_name ?? $item->item?->chemical_name ?? 'Item unavailable';
                                $unit = $item->item_type === 'Chemical' ? ($item->item?->unit ?? 'unit') : 'unit(s)';
                            @endphp
                            <div class="d-flex align-items-center gap-3 py-3 {{ !$loop->last ? 'border-bottom' : '' }}" data-checkin-key="{{ $item->item_type }}:{{ $item->item_id }}" data-item-type="{{ $item->item_type }}" data-checkin-barcode="{{ $item->item?->barcode ?? '' }}" data-item-name="{{ $itemName }}" data-item-unit="{{ $item->item_type === 'Chemical' ? ($item->item?->unit ?? 'unit') : 'unit(s)' }}">
                                <div class="flex-grow-1">
                                    <div class="fw-semibold text-dark">
                                        {{ $itemName }}
                                        @if ($item->item_type === 'Chemical' && $item->item?->is_expired)
                                            <span class="badge text-bg-danger ms-1">Expired</span>
                                        @endif
                                    </div>
                                    <div class="small text-secondary">{{ $item->item_type }} · {{ $item->item?->barcode ?? 'Barcode unavailable' }} · {{ $unit }}</div>
                                    <div class="small text-secondary mt-1">Returned <span data-progress-returned>{{ number_format($returned, $precision) }}</span> · Used <span data-progress-used>{{ number_format($used, $precision) }}</span> · Damaged <span data-progress-damaged>{{ number_format($damaged, $precision) }}</span> · Lost <span data-progress-lost>{{ number_format($lost, $precision) }}</span></div>
                                </div>
                                <div class="text-end">
                                    <div data-progress-accounted class="fw-semibold {{ $accounted + 0.001 >= $checkedOut ? 'text-success' : 'text-dark' }}">{{ number_format($accounted, $precision) }} / {{ number_format($checkedOut, $precision) }}</div>
                                    <div data-progress-outstanding class="small text-{{ $accounted + 0.001 >= $checkedOut ? 'success' : 'secondary' }}">{{ $accounted + 0.001 >= $checkedOut ? 'Complete' : number_format(max(0, $checkedOut - $accounted), $precision).' remaining' }}</div>
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
                            <div><h2 class="h4 fw-semibold text-dark mb-0">Scan return</h2><div class="small text-secondary">POS check-in station</div></div>
                        </div>
                        <p class="text-secondary small mb-4">Scan the barcode, enter the returned chemical quantity in its attached unit, then choose the received condition.</p>
                        @if ($completed)
                            <div class="alert alert-success small border-0"><i class="fa-solid fa-circle-check me-1"></i>All borrowed items are accounted for.</div>
                        @endif
                        <div id="checkin-ajax-feedback" class="alert border-0 small d-none" role="alert"></div>
                        <form method="POST" action="{{ route($checkinRoutePrefix.'.scan', $borrowTransaction) }}" id="checkin-scan-form">
                            @csrf
                            <div class="mb-3">
                                <label for="checkin-barcode" class="form-label fw-semibold text-dark">Barcode</label>
                                <div class="input-group input-group-lg"><span class="input-group-text bg-white"><i class="fa-solid fa-barcode text-primary"></i></span><input type="text" name="barcode" id="checkin-barcode" class="form-control" autocomplete="off" required {{ $completed ? 'disabled' : '' }} placeholder="Scan returned item"></div>
                            </div>
                            <button type="button" class="btn btn-primary btn-lg w-100" id="start-checkin-scanner" {{ $completed ? 'disabled' : '' }}><i class="fa-solid fa-barcode me-1"></i> Start scanner</button>
                            <button type="button" class="btn btn-outline-danger btn-lg w-100 d-none" id="stop-checkin-scanner" {{ $completed ? 'disabled' : '' }}><i class="fa-solid fa-stop me-1"></i> Stop scanning</button>
                        </form>
                        <div id="checkin-scanner-help" class="small text-success mt-3 d-none"><i class="fa-solid fa-circle-dot me-1"></i>Scanner active - scan the returned item now.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="checkin-quantity-modal" tabindex="-1" aria-labelledby="checkin-quantity-modal-title" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <div class="small text-uppercase fw-semibold text-secondary mb-1">Barcode captured</div>
                        <h2 class="modal-title h4 fw-semibold text-dark" id="checkin-quantity-modal-title">Enter returned quantity</h2>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cancel quantity entry"></button>
                </div>
                <div class="modal-body">
                    <div class="rounded-3 bg-light p-3 mb-4">
                        <div class="small text-secondary">Item</div>
                        <div class="fw-semibold text-dark" data-checkin-item-name>Scanned item</div>
                        <div class="small text-secondary mt-2" data-checkin-item-state-label>Equipment condition</div>
                        <div class="fw-semibold text-dark" data-checkin-item-state>Unknown</div>
                    </div>
                    <label for="checkin-quantity" class="form-label fw-semibold text-dark">Returned quantity</label>
                    <div class="input-group input-group-lg">
                        <input type="number" name="quantity" id="checkin-quantity" class="form-control" min="0.01" step="0.01" required disabled form="checkin-scan-form" placeholder="Enter returned quantity">
                        <span class="input-group-text bg-white" data-checkin-unit>unit(s)</span>
                    </div>
                    <div class="form-text">Enter the returned quantity using the item’s listed unit.</div>
                    <div class="mt-3">
                        <label for="condition_in" class="form-label fw-semibold text-dark" data-checkin-condition-label>Equipment condition</label>
                        <select name="condition_in" id="condition_in" class="form-select" form="checkin-scan-form" required disabled>
                            @foreach (['Excellent', 'Good', 'Fair', 'Damaged', 'Under Repair', 'Lost'] as $condition)
                                <option value="{{ $condition }}" @selected($condition === 'Good')>{{ $condition }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="submit-checkin-scan" disabled form="checkin-scan-form"><i class="fa-solid fa-check me-1"></i> Confirm check-in</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="checkin-complete-modal" tabindex="-1" aria-labelledby="checkin-complete-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-body text-center p-5">
                    <div class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                        <i class="fa-solid fa-circle-check fa-2x"></i>
                    </div>
                    <h2 class="h4 fw-semibold text-dark mb-2" id="checkin-complete-modal-title">Check-in complete</h2>
                    <p class="text-secondary mb-4">All items have been successfully scanned and checked in.</p>
                    <button type="button" class="btn btn-success px-4" data-bs-dismiss="modal">Continue</button>
                </div>
            </div>
        </div>
    </div>
@endsection
