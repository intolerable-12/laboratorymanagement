@extends('users.instructor.layouts.app')

@section('title', 'Equipment Inventory')
@section('user-name', 'John Doe')
@section('user-role', 'Instructor')

@section('nav-links')
    @include('users.instructor.partials.nav-links', ['active' => 'inventory'])
@endsection

@section('content')
    <div class="inventory-page">
        <section class="inventory-hero card border-0 mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <a href="{{ route('instructor.inventory.index') }}" class="inventory-back-link mb-3">
                        <i class="fa-solid fa-arrow-left"></i>
                        Back to inventory
                    </a>
                    <span class="inventory-eyebrow mb-3">Equipment categories</span>
                    <h2 class="h3 fw-semibold text-dark mb-2">Choose a category to see available equipment.</h2>
                    <p class="mb-0 text-secondary">The cards scroll horizontally and expand on hover, with image-backed fallbacks when a photo is missing.</p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <span class="inventory-chip"><i class="fa-solid fa-list"></i> {{ $stats['categories'] }} categories</span>
                    <span class="inventory-chip"><i class="fa-solid fa-box-open"></i> {{ $stats['available_items'] }} available items</span>
                </div>
            </div>
        </section>

        <section class="inventory-rail-shell inventory-rail-shell--with-nav" data-inventory-rail-shell>
            <button type="button" class="inventory-rail-nav inventory-rail-nav--prev" data-inventory-rail-prev aria-label="Scroll equipment categories left">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <button type="button" class="inventory-rail-nav inventory-rail-nav--next" data-inventory-rail-next aria-label="Scroll equipment categories right">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
            <div class="inventory-rail inventory-rail--category inventory-rail--equipment" data-inventory-rail>
                @forelse ($categories as $category)
                    <a href="{{ route('instructor.inventory.equipment.categories.show', $category) }}" class="inventory-rail-card inventory-rail-card--category inventory-rail-card--equipment">
                        <div class="inventory-rail-card__shine"></div>
                        <div class="inventory-rail-card__media">
                            @if (! empty($featuredImages[$category->id]))
                                <img src="{{ asset('storage/' . $featuredImages[$category->id]) }}" alt="{{ $category->category_name }}">
                            @else
                                <div class="inventory-rail-card__placeholder inventory-rail-card__placeholder--category">
                                    <span class="inventory-rail-card__placeholder-mark">
                                        <i class="fa-solid fa-microscope"></i>
                                    </span>
                                    <span class="inventory-rail-card__placeholder-label">No photo available</span>
                                </div>
                            @endif
                        </div>
                        <div class="inventory-rail-card__overlay inventory-rail-card__overlay--center">
                            <div class="inventory-rail-card__title">{{ $category->category_name }}</div>
                        </div>
                    </a>
                @empty
                    <div class="inventory-rail-empty">
                        No equipment categories are available right now.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@endsection
