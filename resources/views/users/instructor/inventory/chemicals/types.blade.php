@extends('users.instructor.layouts.app')

@section('title', 'Chemical Types')
@section('page-title', 'Chemical Inventory')
@section('user-name', 'John Doe')
@section('user-role', 'Instructor')

@section('nav-links')
    @include('users.instructor.partials.nav-links', ['active' => 'inventory'])
@endsection

@php
    $typeDescriptions = [
        'Organic' => 'Carbon-based compounds and mixtures.',
        'Inorganic' => 'Mineral, elemental, and non-carbon-based compounds.',
    ];
@endphp

@section('content')
    <div class="inventory-page">
        <section class="inventory-hero card border-0 mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <a href="{{ route('instructor.inventory.chemicals.index') }}" class="inventory-back-link mb-3"><i class="fa-solid fa-arrow-left"></i> Back to chemical laboratories</a>
                    <span class="inventory-eyebrow mb-3">Chemical type</span>
                    <h2 class="h3 fw-semibold text-dark mb-2">{{ $laboratory->laboratory_name }}</h2>
                    <p class="mb-0 text-secondary">Select Organic or Inorganic to browse the categories stored in this laboratory.</p>
                </div>
                <span class="inventory-chip"><i class="fa-solid fa-location-dot"></i> {{ $campus }} campus</span>
            </div>
        </section>

        <section class="inventory-table-shell">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th class="ps-4 text-dark">Chemical type</th>
                            <th class="text-dark">Description</th>
                            <th class="text-dark">Available chemicals</th>
                            <th class="text-center text-dark pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($types as $type)
                            <tr>
                                <td class="ps-4">
                                    <div class="inventory-table__identity">
                                        <span class="inventory-table__media"><i class="fa-solid {{ $type === 'Organic' ? 'fa-leaf' : 'fa-atom' }}" aria-hidden="true"></i></span>
                                        <div>
                                            <div class="inventory-table__name"><a href="{{ route('instructor.inventory.chemicals.categories.index', ['laboratory' => $laboratory, 'type' => $type]) }}">{{ $type }}</a></div>
                                            <div class="inventory-table__meta">{{ $laboratory->laboratory_code ?: 'Chemical type' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-secondary">{{ $typeDescriptions[$type] ?? 'Chemical compounds available for browsing.' }}</td>
                                <td><span class="inventory-table__badge"><i class="fa-solid fa-flask-vial" aria-hidden="true"></i> {{ $typeCounts[$type] ?? 0 }} item{{ ($typeCounts[$type] ?? 0) === 1 ? '' : 's' }}</span></td>
                                <td class="text-center pe-4"><a href="{{ route('instructor.inventory.chemicals.categories.index', ['laboratory' => $laboratory, 'type' => $type]) }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
