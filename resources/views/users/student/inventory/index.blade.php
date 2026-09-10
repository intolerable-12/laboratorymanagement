@extends('users.student.layouts.app')

@section('title', 'Inventory')
@section('page-title', 'Inventory')
@section('user-name', 'Student')
@section('user-role', 'Student')



@section('content')
    <div class="account-page inventory-page">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div>
                <div class="text-secondary">Browse available laboratory inventory.</div>
            </div>
        </div>

        <div class="row g-3 g-xl-4">
            <div class="col-12 col-sm-6">
                <a href="{{ route('student.inventory.equipment.index') }}" class="card metric-card h-100 text-decoration-none">
                    <div class="card-body d-flex align-items-center justify-content-between gap-3">
                        <div>
                            <div class="small text-uppercase text-secondary mb-2">Equipment available</div>
                            <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['equipment_available'] }}</div>
                            <div class="small text-secondary">Ready for student requests</div>
                        </div>
                        <span class="inventory-launch-card__icon d-flex align-items-center justify-content-center flex-shrink-0" style="width: 3.5rem; height: 3.5rem;">
                            <i class="fa-solid fa-microscope" aria-hidden="true"></i>
                        </span>
                    </div>
                    <div class="card-footer bg-transparent border-0 px-4 pb-4 pt-0 text-dark fw-semibold">Browse equipment catalog <i class="fa-solid fa-arrow-right ms-2 text-secondary" aria-hidden="true"></i></div>
                </a>
            </div>
            <div class="col-12 col-sm-6">
                <a href="{{ route('student.inventory.chemicals.index') }}" class="card metric-card h-100 text-decoration-none">
                    <div class="card-body d-flex align-items-center justify-content-between gap-3">
                        <div>
                            <div class="small text-uppercase text-secondary mb-2">Chemicals available</div>
                            <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['chemicals_available'] }}</div>
                            <div class="small text-secondary">Ready for student requests</div>
                        </div>
                        <span class="inventory-launch-card__icon d-flex align-items-center justify-content-center flex-shrink-0" style="width: 3.5rem; height: 3.5rem;">
                            <i class="fa-solid fa-vial" aria-hidden="true"></i>
                        </span>
                    </div>
                    <div class="card-footer bg-transparent border-0 px-4 pb-4 pt-0 text-dark fw-semibold">Browse chemical stock <i class="fa-solid fa-arrow-right ms-2 text-secondary" aria-hidden="true"></i></div>
                </a>
            </div>
        </div>
    </div>
@endsection
