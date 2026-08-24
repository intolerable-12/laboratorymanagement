@extends('users.instructor.layouts.app')

@section('title', 'Inventory')
@section('user-name', 'John Doe')
@section('user-role', 'Instructor')

@section('nav-links')
    @include('users.instructor.partials.nav-links', ['active' => 'inventory'])
@endsection

@section('content')
    <div class="inventory-page">
        <section class="inventory-hero card border-0 mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-4">
                <div class="flex-grow-1" style="max-width: 52rem;">
                    <span class="inventory-eyebrow mb-3">Instructor inventory</span>
                    <h2 class="display-6 fw-semibold text-dark mb-3">Browse available laboratory equipment and chemicals.</h2>
                    <p class="lead text-secondary mb-4">Use the same category-first experience as students, but from the instructor workspace.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="inventory-chip"><i class="fa-solid fa-microscope"></i> Equipment</span>
                        <span class="inventory-chip"><i class="fa-solid fa-vial"></i> Chemicals</span>
                        <span class="inventory-chip"><i class="fa-solid fa-layer-group"></i> Category-first</span>
                    </div>
                </div>

                <div class="inventory-panel p-3 p-md-4" style="min-width: min(100%, 31rem); max-width: 31rem;">
                    <div class="inventory-launch-grid">
                        <a href="{{ route('instructor.inventory.equipment.index') }}" class="inventory-launch-card inventory-launch-card--equipment text-decoration-none text-dark d-flex flex-column gap-3">
                            <span class="inventory-launch-card__icon"><i class="fa-solid fa-microscope"></i></span>
                            <div>
                                <h3 class="h4 fw-semibold mb-2">Equipment</h3>
                                <p class="mb-0 text-secondary">Open available laboratory equipment by category and review item details.</p>
                            </div>
                            <div class="mt-auto d-inline-flex align-items-center gap-2 fw-semibold text-dark">
                                Open equipment
                                <i class="fa-solid fa-arrow-right"></i>
                            </div>
                        </a>

                        <a href="{{ route('instructor.inventory.chemicals.index') }}" class="inventory-launch-card inventory-launch-card--chemical text-decoration-none text-dark d-flex flex-column gap-3">
                            <span class="inventory-launch-card__icon"><i class="fa-solid fa-vial"></i></span>
                            <div>
                                <h3 class="h4 fw-semibold mb-2">Chemical</h3>
                                <p class="mb-0 text-secondary">Open available chemicals by category and review the student-facing details.</p>
                            </div>
                            <div class="mt-auto d-inline-flex align-items-center gap-2 fw-semibold text-dark">
                                Open chemicals
                                <i class="fa-solid fa-arrow-right"></i>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
