@extends('users.instructor.layouts.app')

@section('title', $chemicalCategory->category_name . ' Chemicals')
@section('user-name', 'John Doe')
@section('user-role', 'Instructor')

@section('nav-links')
    @include('users.instructor.partials.nav-links', ['active' => 'inventory'])
@endsection

@section('content')
    <div class="inventory-page">
        <section class="inventory-hero card border-0 mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div class="flex-grow-1" style="max-width: 48rem;">
                    <a href="{{ route('instructor.inventory.chemicals.index') }}" class="inventory-back-link mb-3">
                        <i class="fa-solid fa-arrow-left"></i>
                        Back to chemical categories
                    </a>
                    <span class="inventory-eyebrow mb-3">Chemical category</span>
                    <h2 class="h3 fw-semibold text-dark mb-2">{{ $chemicalCategory->category_name }}</h2>
                    <p class="mb-0 text-secondary">{{ $chemicalCategory->description ?: 'Available chemicals in this category.' }}</p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <span class="inventory-chip"><i class="fa-solid fa-flask-vial"></i> {{ $chemicalCategory->available_chemical_count }} items</span>
                    <span class="inventory-chip"><i class="fa-solid fa-circle-check"></i> Available only</span>
                </div>
            </div>
        </section>

        <section class="inventory-rail-shell inventory-rail-shell--with-nav" data-inventory-rail-shell>
            <button type="button" class="inventory-rail-nav inventory-rail-nav--prev" data-inventory-rail-prev aria-label="Scroll chemicals left">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <button type="button" class="inventory-rail-nav inventory-rail-nav--next" data-inventory-rail-next aria-label="Scroll chemicals right">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
            <div class="inventory-rail inventory-rail--chemical" data-inventory-rail>
                @forelse ($chemicals as $chemical)
                    <a href="{{ route('instructor.inventory.chemicals.show', $chemical) }}" class="inventory-rail-card inventory-rail-card--visual inventory-rail-card--chemical">
                        <div class="inventory-rail-card__shine"></div>
                        <div class="inventory-rail-card__media">
                            @if ($chemical->image)
                                <img src="{{ asset('storage/' . $chemical->image) }}" alt="{{ $chemical->chemical_name }}">
                            @else
                                <div class="inventory-rail-card__placeholder">
                                    <span class="inventory-rail-card__placeholder-mark">
                                        <i class="fa-solid fa-vial"></i>
                                    </span>
                                    <span class="inventory-rail-card__placeholder-label">No photo available</span>
                                </div>
                            @endif
                        </div>
                        <div class="inventory-rail-card__overlay">
                            <div class="inventory-rail-card__title">{{ $chemical->chemical_name }}</div>
                        </div>
                    </a>
                @empty
                    <div class="inventory-rail-empty">
                        No available chemicals were found in this category.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@endsection
