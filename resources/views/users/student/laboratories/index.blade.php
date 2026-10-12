@extends('users.student.layouts.app')

@section('title', 'Laboratories')
@section('page-title', 'Laboratories')
@section('page-subtitle', 'Explore the laboratories available at your campus')

@section('content')
    <section class="hero-banner card border-0 mb-4">
        <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-4">
            <div>
                <span class="badge rounded-pill text-bg-primary mb-3">{{ auth()->user()->campusName() }} campus</span>
                <h2 class="h3 fw-semibold mb-2 text-dark">Know your laboratories</h2>
                <p class="mb-0 text-secondary">View each laboratory's location, capacity, status, and available facilities before you plan a visit or request.</p>
            </div>
            <a href="{{ route('student.dashboard') }}" class="btn btn-light border px-4">Back to dashboard</a>
        </div>
    </section>

    <div class="section-card mb-4">
        <div class="card-body p-3 p-lg-4">
            <form method="GET" action="{{ route('student.laboratories.index') }}" class="row g-2">
                <div class="col-md-10">
                    <label for="laboratory-search" class="visually-hidden">Search laboratories</label>
                    <input id="laboratory-search" type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search by laboratory, building, room, or description">
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass me-1"></i> Search</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        @forelse ($laboratories as $laboratory)
            @php($imagePath = $laboratory->primaryImagePath())
            <div class="col-12 col-md-6 col-xl-4">
                <article class="card section-card border-0 h-100 overflow-hidden">
                    <div class="position-relative">
                        @if ($imagePath)
                            <a href="{{ route('student.laboratories.show', $laboratory) }}" class="student-laboratory-photo-trigger d-block">
                                <img src="{{ asset('storage/' . $imagePath) }}" alt="{{ $laboratory->laboratory_name }}" class="w-100" style="height: 220px; object-fit: cover;">
                            </a>
                        @else
                            <div class="d-flex align-items-center justify-content-center bg-light text-secondary" style="height: 220px;">
                                <div class="text-center"><i class="fa-solid fa-flask fs-1 mb-2"></i><div>No photos yet</div></div>
                            </div>
                        @endif
                        <span class="position-absolute top-0 end-0 m-3 badge text-bg-{{ $laboratory->status === 'Available' ? 'success' : ($laboratory->status === 'Under Maintenance' ? 'warning' : 'secondary') }}">{{ $laboratory->status }}</span>
                    </div>
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="small text-uppercase text-secondary mb-1">{{ $laboratory->laboratory_code }}</div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <h2 class="h5 fw-semibold text-dark mb-0">{{ $laboratory->laboratory_name }}</h2>
                            <span class="badge rounded-pill text-bg-info">{{ $laboratory->campus }} campus</span>
                        </div>
                        <p class="small text-secondary mb-3"><i class="fa-solid fa-location-dot me-1"></i>{{ $laboratory->building ?: 'Building not specified' }} · Room {{ $laboratory->room_number }}</p>
                        <p class="text-secondary small mb-4">{{ \Illuminate\Support\Str::limit($laboratory->description ?: 'Laboratory information will be updated by the coordinator.', 120) }}</p>

                        <div class="row g-2 small mb-4">
                            <div class="col-12"><div class="bg-light rounded-3 p-2"><div class="text-secondary">Capacity</div><div class="fw-semibold text-dark">{{ $laboratory->capacity }}</div></div></div>
                        </div>

                        <a href="{{ route('student.laboratories.show', $laboratory) }}" class="btn btn-outline-primary mt-auto">View laboratory details</a>
                    </div>
                </article>
            </div>
        @empty
            <div class="col-12">
                <div class="section-card p-5 text-center text-secondary">No laboratories match your search.</div>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $laboratories->links('pagination::bootstrap-5') }}
    </div>
@endsection
