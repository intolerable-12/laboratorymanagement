@extends('users.student.layouts.app')

@section('title', 'Inventory')
@section('user-name', 'Student')
@section('user-role', 'Student')



@section('content')
    <div class="inventory-page">
        <div class="d-flex justify-content-end mb-3">
            @include('users.student.inventory.partials.view-switcher', ['active' => null])
        </div>

        <section class="inventory-hero card border-0 mb-4">
            <div class="card-body p-4 p-xl-5">
                <div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-4">
                    <div class="flex-grow-1" style="max-width: 52rem;">
                        <span class="inventory-eyebrow mb-3">Browse inventory</span>
                        <h2 class="display-6 fw-semibold text-dark mb-3">Find available equipment and chemicals</h2>
                        <p class="lead text-secondary mb-4">Explore inventory by category, review what is available, and open the item details you need without leaving the student dashboard.</p>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="inventory-chip"><i class="fa-solid fa-microscope"></i> Equipment</span>
                            <span class="inventory-chip"><i class="fa-solid fa-vial"></i> Chemicals</span>
                            <span class="inventory-chip"><i class="fa-solid fa-layer-group"></i> Category-first</span>
                        </div>
                    </div>

                    <div class="inventory-panel p-3 p-md-4" style="min-width: min(100%, 31rem); max-width: 31rem;">
                        <div class="inventory-launch-grid">
                            <a href="{{ route('student.inventory.equipment.index') }}" class="inventory-launch-card inventory-launch-card--equipment text-decoration-none text-dark d-flex flex-column gap-3">
                                <span class="inventory-launch-card__icon"><i class="fa-solid fa-microscope"></i></span>
                                <div>
                                    <h3 class="h4 fw-semibold mb-2">Equipment</h3>
                                    <p class="mb-0 text-secondary">Browse available laboratory equipment by category, then open a clean detail page for each item.</p>
                                </div>
                                <div class="mt-auto d-inline-flex align-items-center gap-2 fw-semibold text-dark">
                                    Open equipment
                                    <i class="fa-solid fa-arrow-right"></i>
                                </div>
                            </a>

                            <a href="{{ route('student.inventory.chemicals.index') }}" class="inventory-launch-card inventory-launch-card--chemical text-decoration-none text-dark d-flex flex-column gap-3">
                                <span class="inventory-launch-card__icon"><i class="fa-solid fa-vial"></i></span>
                                <div>
                                    <h3 class="h4 fw-semibold mb-2">Chemical</h3>
                                    <p class="mb-0 text-secondary">Scan available chemicals by category, then review only the details students actually need.</p>
                                </div>
                                <div class="mt-auto d-inline-flex align-items-center gap-2 fw-semibold text-dark">
                                    Open chemicals
                                    <i class="fa-solid fa-arrow-right"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stat Cards Container -->
        <section class="d-flex flex-wrap gap-4">
            <!-- Equipment Card -->
            <div class="card metric-card border-0 shadow-sm" style="width: 100%; max-width: 19rem; aspect-ratio: 1 / 1;">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="inventory-stat__label d-block text-uppercase fw-semibold text-secondary mb-1" style="letter-spacing: 0.06em; font-size: 0.78rem;">Equipment Available</span>
                            <span class="text-muted small">Ready for request</span>
                        </div>
                        <span class="inventory-launch-card__icon d-flex align-items-center justify-content-center shadow-sm" style="width: 3rem; height: 3rem; font-size: 1.25rem;">
                            <i class="fa-solid fa-microscope"></i>
                        </span>
                    </div>

                    <div class="my-auto py-2">
                        <div class="display-3 fw-bold text-dark lh-1 mb-2">{{ $stats['equipment_available'] }}</div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 font-monospace" style="font-size: 0.75rem;">
                            <i class="fa-solid fa-circle-check me-1"></i> Active Inventory
                        </span>
                    </div>

                    <a href="{{ route('student.inventory.equipment.index') }}" class="text-decoration-none text-dark fw-semibold d-flex align-items-center justify-content-between pt-3 border-top border-light-subtle">
                        <span>Browse equipment catalog</span>
                        <i class="fa-solid fa-arrow-right text-secondary"></i>
                    </a>
                </div>
            </div>

            <!-- Chemicals Card -->
            <div class="card metric-card border-0 shadow-sm" style="width: 100%; max-width: 19rem; aspect-ratio: 1 / 1;">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="inventory-stat__label d-block text-uppercase fw-semibold text-secondary mb-1" style="letter-spacing: 0.06em; font-size: 0.78rem;">Chemicals Available</span>
                            <span class="text-muted small">Ready for request</span>
                        </div>
                        <span class="inventory-launch-card__icon d-flex align-items-center justify-content-center shadow-sm" style="width: 3rem; height: 3rem; font-size: 1.25rem;">
                            <i class="fa-solid fa-vial"></i>
                        </span>
                    </div>

                    <div class="my-auto py-2">
                        <div class="display-3 fw-bold text-dark lh-1 mb-2">{{ $stats['chemicals_available'] }}</div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 font-monospace" style="font-size: 0.75rem;">
                            <i class="fa-solid fa-flask me-1"></i> Active Inventory
                        </span>
                    </div>

                    <a href="{{ route('student.inventory.chemicals.index') }}" class="text-decoration-none text-dark fw-semibold d-flex align-items-center justify-content-between pt-3 border-top border-light-subtle">
                        <span>Browse chemical stock</span>
                        <i class="fa-solid fa-arrow-right text-secondary"></i>
                    </a>
                </div>
            </div>
        </section>
    </div>
@endsection
