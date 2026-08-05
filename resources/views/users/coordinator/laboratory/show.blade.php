@extends('users.coordinator.layouts.app')

@section('title', 'View Laboratory')
@section('page-title', 'View Laboratory')
@section('page-subtitle', 'Review one laboratory record in detail')

@section('content')
    <div class="hero-banner laboratory-hero rounded-4 p-4 p-lg-5 mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-white border mb-3">
                    <span class="badge rounded-pill text-bg-primary">Laboratory profile</span>
                    <span class="small text-secondary">Room, capacity, and image overview</span>
                </div>

                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="equipment-hero__accent"><span class="h4 mb-0 fw-semibold">{{ strtoupper(substr($laboratory->laboratory_name, 0, 1)) }}</span></div>
                    <div>
                        <h2 class="display-6 fw-semibold text-dark mb-2">{{ $laboratory->laboratory_name }}</h2>
                        <p class="lead text-secondary mb-0" style="max-width: 58rem;">A clean laboratory profile with a fixed-size image area for consistent presentation.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 d-flex justify-content-lg-end gap-2 flex-wrap">
                <a href="{{ route('coordinator.laboratories.edit', $laboratory) }}" class="btn btn-primary px-4">Edit laboratory</a>
                <a href="{{ route('coordinator.laboratories.index') }}" class="btn btn-outline-secondary px-4">Back to list</a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-5">
            <div class="section-card h-100">
                <div class="card-body p-4 p-xl-5">
                    <div class="laboratory-frame laboratory-frame--detail mb-4">
                        @if ($laboratory->image)
                            <img src="{{ asset('storage/' . $laboratory->image) }}" alt="{{ $laboratory->laboratory_name }}">
                        @else
                            <div class="laboratory-frame__placeholder">
                                <div class="laboratory-grid-card__placeholder-mark">L</div>
                                <div class="small text-secondary">No image available</div>
                            </div>
                        @endif
                    </div>

                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <span class="badge text-bg-{{ $laboratory->status === 'Available' ? 'success' : ($laboratory->status === 'Under Maintenance' ? 'warning' : 'secondary') }}">{{ $laboratory->status }}</span>
                        <span class="badge text-bg-light border text-dark">{{ $laboratory->laboratory_code }}</span>
                    </div>

                    <div class="vstack gap-3">
                        <div>
                            <div class="small text-uppercase text-secondary mb-1">Building</div>
                            <div class="fw-semibold text-dark">{{ $laboratory->building ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="small text-uppercase text-secondary mb-1">Room number</div>
                            <div class="fw-semibold text-dark">{{ $laboratory->room_number }}</div>
                        </div>
                        <div>
                            <div class="small text-uppercase text-secondary mb-1">Capacity</div>
                            <div class="fw-semibold text-dark">{{ $laboratory->capacity }}</div>
                        </div>
                        <div>
                            <div class="small text-uppercase text-secondary mb-1">Description</div>
                            <div class="text-dark">{{ $laboratory->description ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="section-card h-100">
                <div class="card-body p-4 p-xl-5">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="small text-uppercase text-secondary mb-1">Laboratory code</div>
                            <div class="fw-semibold text-dark">{{ $laboratory->laboratory_code }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-uppercase text-secondary mb-1">Laboratory name</div>
                            <div class="fw-semibold text-dark">{{ $laboratory->laboratory_name }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-uppercase text-secondary mb-1">Building</div>
                            <div class="fw-semibold text-dark">{{ $laboratory->building ?? '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-uppercase text-secondary mb-1">Room number</div>
                            <div class="fw-semibold text-dark">{{ $laboratory->room_number }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-uppercase text-secondary mb-1">Capacity</div>
                            <div class="fw-semibold text-dark">{{ $laboratory->capacity }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-uppercase text-secondary mb-1">Status</div>
                            <div class="fw-semibold text-dark">{{ $laboratory->status }}</div>
                        </div>
                        <div class="col-12">
                            <div class="small text-uppercase text-secondary mb-1">Description</div>
                            <div class="text-dark">{{ $laboratory->description ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection