@extends('users.instructor.layouts.app')

@section('title', $chemical->chemical_name)
@section('user-name', 'John Doe')
@section('user-role', 'Instructor')

@section('nav-links')
    @include('users.instructor.partials.nav-links', ['active' => 'inventory'])
@endsection

@section('content')
    <div class="inventory-page">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
            <a href="{{ route('instructor.inventory.chemicals.categories.show', $chemical->category) }}" class="inventory-back-link">
                <i class="fa-solid fa-arrow-left"></i>
                Back to category
            </a>
            <span class="inventory-chip"><i class="fa-solid fa-circle-check"></i> Available now</span>
        </div>

        <section class="row g-0 inventory-detail-hero">
            <div class="col-lg-5 inventory-detail-media">
                @if ($chemical->image)
                    <img src="{{ asset('storage/' . $chemical->image) }}" alt="{{ $chemical->chemical_name }}">
                @else
                    <div class="inventory-detail-media__placeholder">
                        <div>
                            <div class="inventory-detail-media__placeholder-mark">
                                <i class="fa-solid fa-vial"></i>
                            </div>
                            <div>No chemical image available</div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-lg-7 p-4 p-xl-5 inventory-detail-panel">
                <span class="inventory-eyebrow mb-3">Chemical detail</span>
                <h2 class="display-6 fw-semibold text-dark mb-3">{{ $chemical->chemical_name }}</h2>
                <p class="text-secondary mb-4">
                    {{ $chemical->description ?: 'A concise instructor-facing summary of the chemical item and the fields you need to see at a glance.' }}
                </p>

                <div class="inventory-detail-specs">
                    <div class="inventory-detail-spec">
                        <span class="inventory-detail-spec__label">Category</span>
                        <span class="inventory-detail-spec__value">{{ $chemical->category?->category_name ?? 'Chemical' }}</span>
                    </div>
                    <div class="inventory-detail-spec">
                        <span class="inventory-detail-spec__label">Laboratory</span>
                        <span class="inventory-detail-spec__value">{{ $chemical->laboratory?->laboratory_name ?? 'Not listed' }}</span>
                    </div>
                    <div class="inventory-detail-spec">
                        <span class="inventory-detail-spec__label">Quantity</span>
                        <span class="inventory-detail-spec__value">{{ number_format((float) $chemical->quantity, 2) }} {{ $chemical->unit }}</span>
                    </div>
                    <div class="inventory-detail-spec">
                        <span class="inventory-detail-spec__label">Hazard</span>
                        <span class="inventory-detail-spec__value">{{ $chemical->hazard_classification }}</span>
                    </div>
                    <div class="inventory-detail-spec">
                        <span class="inventory-detail-spec__label">Storage</span>
                        <span class="inventory-detail-spec__value">{{ $chemical->storage_location ?: 'Not listed' }}</span>
                    </div>
                    <div class="inventory-detail-spec">
                        <span class="inventory-detail-spec__label">Expiration</span>
                        <span class="inventory-detail-spec__value">{{ $chemical->expiration_date?->format('M d, Y') ?: 'Not listed' }}</span>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
