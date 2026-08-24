@extends('users.instructor.layouts.app')

@section('title', $equipmentCategory->category_name . ' Equipment')
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
                    <a href="{{ route('instructor.inventory.equipment.index') }}" class="inventory-back-link mb-3">
                        <i class="fa-solid fa-arrow-left"></i>
                        Back to equipment categories
                    </a>
                    <span class="inventory-eyebrow mb-3">Equipment category</span>
                    <h2 class="h3 fw-semibold text-dark mb-2">{{ $equipmentCategory->category_name }}</h2>
                    <p class="mb-0 text-secondary">{{ $equipmentCategory->description ?: 'Available equipment in this category.' }}</p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <span class="inventory-chip"><i class="fa-solid fa-box-open"></i> {{ $equipmentCategory->available_equipment_count }} items</span>
                    <span class="inventory-chip"><i class="fa-solid fa-circle-check"></i> Available only</span>
                </div>
            </div>
        </section>

        <section class="inventory-rail-shell inventory-rail-shell--with-nav" data-inventory-rail-shell>
            <button type="button" class="inventory-rail-nav inventory-rail-nav--prev" data-inventory-rail-prev aria-label="Scroll equipment left">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <button type="button" class="inventory-rail-nav inventory-rail-nav--next" data-inventory-rail-next aria-label="Scroll equipment right">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
            <div class="inventory-rail inventory-rail--equipment" data-inventory-rail>
                @forelse ($equipmentItems as $equipment)
                    <a href="{{ route('instructor.inventory.equipment.show', $equipment) }}" class="inventory-rail-card inventory-rail-card--visual inventory-rail-card--equipment">
                        <div class="inventory-rail-card__shine"></div>
                        <div class="inventory-rail-card__media">
                            @if ($equipment->image)
                                <img src="{{ asset('storage/' . $equipment->image) }}" alt="{{ $equipment->equipment_name }}">
                            @else
                                <div class="inventory-rail-card__placeholder">
                                    <span class="inventory-rail-card__placeholder-mark">
                                        <i class="fa-solid fa-microscope"></i>
                                    </span>
                                    <span class="inventory-rail-card__placeholder-label">No photo available</span>
                                </div>
                            @endif
                        </div>
                        <div class="inventory-rail-card__overlay">
                            <div class="inventory-rail-card__title">{{ $equipment->equipment_name }}</div>
                        </div>
                    </a>
                @empty
                    <div class="inventory-rail-empty">
                        No available equipment was found in this category.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@endsection
