@extends('users.student.layouts.app')

@section('title', $equipment->equipment_name)
@section('user-name', 'Student')
@section('user-role', 'Student')


@section('content')
    <div class="inventory-page">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
            <a href="{{ route('student.inventory.equipment.categories.show', $equipment->category) }}" class="inventory-back-link">
                <i class="fa-solid fa-arrow-left"></i>
                Back to category
            </a>
            <span class="inventory-chip"><i class="fa-solid fa-circle-check"></i> Available now</span>
        </div>

        <section class="row g-0 inventory-detail-hero">
            <div class="col-lg-5 inventory-detail-media">
                @if ($equipment->image)
                    <img src="{{ asset('storage/' . $equipment->image) }}" alt="{{ $equipment->equipment_name }}">
                @else
                    <div class="inventory-detail-media__placeholder">
                        <div>
                            <div class="inventory-detail-media__placeholder-mark">
                                <i class="fa-solid fa-microscope"></i>
                            </div>
                            <div>No equipment image available</div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-lg-7 p-4 p-xl-5 inventory-detail-panel">
                @php
                    $equipmentBrandModel = collect([$equipment->brand, $equipment->model])->filter()->implode(' / ');
                @endphp
                <span class="inventory-eyebrow mb-3">Equipment detail</span>
                <h2 class="display-6 fw-semibold text-dark mb-3">{{ $equipment->equipment_name }}</h2>
                <p class="text-secondary mb-4">
                    {{ $equipment->description ?: 'A clean student-facing summary of the equipment item and the fields you need to see at a glance.' }}
                </p>

                <div class="inventory-detail-specs">
                    <div class="inventory-detail-spec">
                        <span class="inventory-detail-spec__label">Category</span>
                        <span class="inventory-detail-spec__value">{{ $equipment->category?->category_name ?? 'Equipment' }}</span>
                    </div>
                    <div class="inventory-detail-spec">
                        <span class="inventory-detail-spec__label">Laboratory</span>
                        <span class="inventory-detail-spec__value">{{ $equipment->laboratory?->laboratory_name ?? 'Not listed' }}</span>
                    </div>
                    <div class="inventory-detail-spec">
                        <span class="inventory-detail-spec__label">Brand / Model</span>
                        <span class="inventory-detail-spec__value">{{ $equipmentBrandModel !== '' ? $equipmentBrandModel : 'N/A' }}</span>
                    </div>
                    <div class="inventory-detail-spec">
                        <span class="inventory-detail-spec__label">Availability</span>
                        <span class="inventory-detail-spec__value">{{ number_format($equipment->available_quantity) }} of {{ number_format($equipment->quantity) }}</span>
                    </div>
                    <div class="inventory-detail-spec">
                        <span class="inventory-detail-spec__label">Condition</span>
                        <span class="inventory-detail-spec__value">{{ $equipment->condition }}</span>
                    </div>
                    <div class="inventory-detail-spec">
                        <span class="inventory-detail-spec__label">Storage</span>
                        <span class="inventory-detail-spec__value">{{ $equipment->storage_location ?: 'Not listed' }}</span>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
