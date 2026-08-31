@extends('users.facilitator.layouts.app')

@section('title', 'Laboratory In-charge Dashboard')
@section('page-title', 'Laboratory In-charge Dashboard')
@section('user-name', 'John Doe')
@section('user-role', 'Laboratory In-charge')

@section('nav-links')
    @include('users.facilitator.partials.nav-links', ['active' => 'dashboard'])
@endsection

@section('content')
    <div class="facilitator-dashboard">
        <section class="hero-banner card border-0 mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <h2 class="h3 fw-semibold mb-2 text-dark">Laboratory In-charge Dashboard</h2>
                    <p class="mb-0 text-secondary">Welcome back, {{ trim((auth()->user()?->first_name ?? '').' '.(auth()->user()?->last_name ?? '')) ?: 'Laboratory In-charge' }}. Monitor equipment operations and check out approved requests.</p>
                </div>
                <a href="{{ route('facilitator.checkout.index') }}" class="btn btn-light border px-3 px-lg-4"><i class="fa-solid fa-barcode me-1"></i> Open Checkout</a>
            </div>
        </section>

        @include('partials.announcement-feed', [
            'announcements' => $announcements,
            'feedTitle' => 'Announcements',
            'feedSubtitle' => 'Coordinator notices that the laboratory in-charge should review first.',
        ])

        <section class="mb-4">
            <div class="d-flex align-items-center gap-2 mb-3">
                <i class="fa-solid fa-microscope text-primary"></i>
                <h3 class="h4 fw-semibold mb-0 text-dark">Equipment overview</h3>
            </div>
            <div class="row g-3 g-xl-4">
                @foreach ($equipmentStats as $metric)
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card metric-card border-0 h-100">
                            <div class="card-body p-4">
                                <div class="text-uppercase small fw-semibold text-secondary mb-3">{{ $metric['label'] }}</div>
                                <div class="display-6 fw-semibold mb-2 text-dark">{{ $metric['value'] }}</div>
                                <div class="small text-secondary">{{ $metric['note'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="mb-4">
            <div class="d-flex align-items-center gap-2 mb-3">
                <i class="fa-solid fa-flask text-success"></i>
                <h3 class="h4 fw-semibold mb-0 text-dark">Chemical overview</h3>
            </div>
            <div class="row g-3 g-xl-4">
                @foreach ($chemicalStats as $metric)
                    <div class="col-12 col-sm-6 col-xl-4">
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
                            </div>
                        </div>
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
                                <th>Borrower</th>
                                <th>Items</th>
                                <th>Borrow Date</th>
                                <th>Release Status</th>
                                <th>Action</th>
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
                                        <div class="fw-semibold text-dark">{{ trim(($borrow->borrower?->first_name ?? '').' '.($borrow->borrower?->last_name ?? '')) }}</div>
                                        <div class="small text-secondary">{{ $borrow->borrower?->userID ?? 'Student' }}</div>
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
                                    <td class="text-end"><a href="{{ route('facilitator.checkout.show', $borrow) }}" class="btn btn-sm btn-primary">Checkout</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-secondary py-5">No approved borrow requests are ready for checkout.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <a href="{{ route('facilitator.checkout.index') }}" class="mt-4 d-inline-block fw-semibold text-decoration-none">View all checkout requests <i class="fa-solid fa-arrow-right ms-1"></i></a>
            </div>
        </section>
    </div>
@endsection
