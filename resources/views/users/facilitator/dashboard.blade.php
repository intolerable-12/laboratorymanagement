@extends('users.facilitator.layouts.app')

@section('title', 'Laboratory In-charge Dashboard')
@section('page-title', 'Laboratory In-charge Dashboard')
@section('user-name', 'John Doe')
@section('user-role', 'Laboratory In-charge')

@section('nav-links')
    @include('users.facilitator.partials.nav-links', ['active' => 'dashboard'])
@endsection

@section('content')
        <div class="section-card bg-white mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
                <div class="hero-copy">
                    <div class="social-eyebrow mb-3">Laboratory In-charge</div>
                    <h2>Welcome back, {{ auth()->user()?->first_name ?? 'In-charge' }}</h2>
                    <p class="mb-0 text-secondary">Monitor equipment operations and check out approved requests.</p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('facilitator.inventory-alerts.index') }}" class="btn btn-outline-secondary px-4 rounded-pill">
                        Inventory Alert Settings
                    </a>
                    <a href="{{ route('facilitator.checkout.index') }}" class="btn btn-primary px-4 rounded-pill">
                        Open Checkout
                    </a>
                </div>
            </div>
        </div>

        @include('partials.announcement-feed', [
            'announcements' => $announcements,
            'feedTitle' => 'Announcements',
            'feedSubtitle' => 'Coordinator notices that the laboratory in-charge should review first.',
        ])

        <section class="mb-4">
            <div class="d-flex align-items-center gap-2 mb-3">
                <i class="fa-solid fa-microscope text-primary"></i>
                <i class="fa-solid fa-flask text-success"></i>
                <h3 class="h4 fw-semibold mb-0 text-dark">Equipment &amp; Chemical Overview</h3>
            </div>
            <div class="row g-3 g-xl-4">
                @foreach ($equipmentStats as $metric)
                    <div class="col-12 col-sm-6 col-xl-3">
                        @php
                            $metricUrl = match ($metric['label']) {
                                'Damaged equipment' => route('facilitator.equipment.index', ['condition' => 'Damaged']),
                                'Low stock equipment' => route('facilitator.equipment.index', ['low_stock' => '1']),
                                default => null,
                            };
                        @endphp
                        @if ($metricUrl)
                            <a href="{{ $metricUrl }}" class="d-block h-100 text-decoration-none" aria-label="View damaged equipment">
                        @endif
                        <div class="card metric-card border-0 h-100">
                            <div class="card-body p-4">
                                <div class="text-uppercase small fw-semibold text-secondary mb-3">{{ $metric['label'] }}</div>
                                <div class="display-6 fw-semibold mb-2 text-dark">{{ $metric['value'] }}</div>
                                <div class="small text-secondary">{{ $metric['note'] }}</div>
                                @if ($metricUrl)
                                    <div class="small fw-semibold text-primary mt-3">See more <i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i></div>
                                @endif
                            </div>
                        </div>
                        @if ($metricUrl)
                            </a>
                        @endif
                    </div>
                @endforeach
                @foreach ($chemicalStats as $metric)
                    <div class="col-12 col-sm-6 col-xl-4">
                        @php
                            $metricUrl = match ($metric['label']) {
                                'Expired chemicals' => route('facilitator.chemicals.index', ['status' => 'Expired']),
                                'Low stock chemicals' => route('facilitator.chemicals.index', ['low_stock' => '1']),
                                default => null,
                            };
                        @endphp
                        @if ($metricUrl)
                            <a href="{{ $metricUrl }}" class="d-block h-100 text-decoration-none" aria-label="View {{ strtolower($metric['label']) }}">
                        @endif
                        <div class="card metric-card border-0 h-100">
                            <div class="card-body p-4">
                                <div class="text-uppercase small fw-semibold text-secondary mb-3">{{ $metric['label'] }}</div>
                                @if (isset($metric['breakdown']))
                                    <div class="mb-2">
                                        @forelse ($metric['breakdown'] as $quantity)
                                            <div class="d-flex justify-content-between align-items-center gap-3 py-1">
                                                <span class="badge rounded-pill text-bg-light border text-secondary">{{ $quantity['unit'] }}</span>
                                                <span class="fw-semibold text-dark text-end">{{ $quantity['value'] }} {{ $quantity['unit'] }}</span>
                                            </div>
                                        @empty
                                            <div class="display-6 fw-semibold text-dark">0</div>
                                        @endforelse
                                    </div>
                                @else
                                    <div class="display-6 fw-semibold mb-2 text-dark">{{ $metric['value'] }}</div>
                                @endif
                                <div class="small text-secondary">{{ $metric['note'] }}</div>
                                @if ($metricUrl)
                                    <div class="small fw-semibold text-primary mt-3">See more <i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i></div>
                                @endif
                            </div>
                        </div>
                        @if ($metricUrl)
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>

        <section class="row g-3 g-xl-4 mb-4">
            @foreach ($operationalStats as $metric)
                <div class="col-12 col-sm-6 col-xl-4">
                    <div class="card metric-card border-0 h-100">
                        <div class="card-body p-4">
                            <div class="text-uppercase small fw-semibold text-secondary mb-3">{{ $metric['label'] }}</div>
                            <div class="display-6 fw-semibold mb-2 text-dark">{{ $metric['value'] }}</div>
                            <div class="small text-secondary">{{ $metric['note'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </section>

        @include('partials.laboratory-inventory-summary', ['laboratoryInventory' => $laboratoryInventory])

        <section class="row g-3 mb-4" aria-label="Equipment check-in and checkout">
            <div class="col-12 col-sm-6 col-xl-3">
                <a href="{{ route('facilitator.checkin.index') }}" class="card metric-card border-0 h-100 text-decoration-none">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <i class="fa-solid fa-arrow-right-to-bracket text-primary fs-4" aria-hidden="true"></i>
                        <div class="flex-grow-1">
                            <div class="small text-uppercase fw-semibold text-secondary">Check in equipment</div>
                            <div class="h4 fw-semibold text-dark mb-0">{{ number_format($checkinCount) }}</div>
                            <div class="small fw-semibold text-primary mt-1">See more <i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i></div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <a href="{{ route('facilitator.checkout.index') }}" class="card metric-card border-0 h-100 text-decoration-none">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <i class="fa-solid fa-arrow-right-from-bracket text-success fs-4" aria-hidden="true"></i>
                        <div class="flex-grow-1">
                            <div class="small text-uppercase fw-semibold text-secondary">Check out equipment/chemical</div>
                            <div class="h4 fw-semibold text-dark mb-0">{{ number_format($checkoutCount) }}</div>
                            <div class="small fw-semibold text-primary mt-1">See more <i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i></div>
                        </div>
                    </div>
                </a>
            </div>
        </section>

        <section class="card section-card border-0 mb-4">
            <div class="card-body p-4 p-xl-5">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                    <div>
                        <h3 class="h4 fw-semibold mb-1 text-dark">Approved Requests - Ready for Checkout</h3>
                        <p class="mb-0 text-secondary">Scan the approved equipment or chemical barcode when the scheduled borrow time arrives.</p>
                    </div>
                    <a href="{{ route('facilitator.checkout.index') }}" class="btn btn-outline-primary btn-sm">View checkout queue</a>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-dark">Borrower</th>
                                <th class="text-dark">Items</th>
                                <th class="text-dark">Borrow Date</th>
                                <th class="text-dark">Release Status</th>
                                <th class="text-center text-dark">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($checkoutBorrows as $borrow)
                                @php
                                    $itemSummary = $borrow->items->map(function ($item) {
                                        $name = $item->item?->equipment_name ?? $item->item?->chemical_name ?? 'Unknown item';
                                        $unit = $item->item_type === 'Chemical' ? ' '.($item->item?->unit ?? 'unit') : ' unit(s)';

                                        return $name.' × '.number_format((float) $item->quantity_borrowed, $item->item_type === 'Chemical' ? 2 : 0).$unit;
                                    })->implode(', ');
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $borrow->requesterName() }}</div>
                                        <div class="small text-secondary">{{ $borrow->requesterStudentId() ?? 'Student' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $itemSummary }}</div>
                                        <div class="small text-secondary">{{ $borrow->items->count() }} approved item{{ $borrow->items->count() === 1 ? '' : 's' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $borrow->borrowed_at?->format('M d, Y') }}</div>
                                        <div class="small text-secondary">Return {{ $borrow->due_at?->format('M d, Y') ?? '—' }}</div>
                                    </td>
                                    <td><span class="badge text-bg-{{ $borrow->status === 'Partially Borrowed' ? 'warning' : 'success' }}">{{ $borrow->status === 'Partially Borrowed' ? 'In progress' : 'Ready' }}</span></td>
                                    <td class="text-center"><a href="{{ route('facilitator.checkout.show', $borrow) }}" class="btn btn-sm btn-primary">Checkout</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-secondary py-5">No approved borrow requests are ready for checkout.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
@endsection
