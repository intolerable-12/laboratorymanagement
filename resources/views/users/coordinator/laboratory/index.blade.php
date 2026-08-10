@extends('users.coordinator.layouts.app')

@section('title', 'Laboratories')
@section('page-title', 'Laboratories')

@section('content')
    @if (session('status'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
    @endif

    <div class="hero-banner laboratory-hero rounded-4 p-4 p-lg-5 mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-white border mb-3">
                    <span class="badge rounded-pill text-bg-primary">Laboratory management</span>
                    <span class="small text-secondary">Track rooms, capacity, and visuals</span>
                </div>

                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="equipment-hero__accent">
                        <span class="h4 mb-0 fw-semibold">L</span>
                    </div>
                    <div>
                        <h2 class="display-6 fw-semibold text-dark mb-2">Laboratories</h2>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="section-card p-4 h-100">
                    <div class="small text-uppercase text-secondary mb-2">Quick actions</div>
                    <div class="d-grid gap-2">
                        <a href="{{ route('coordinator.laboratories.create') }}" class="btn btn-primary">Add laboratory</a>
                        <a href="#laboratoriesTable" class="btn btn-outline-secondary">Jump to list</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 g-xl-4 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2">Total laboratories</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['total'] }}</div>
                    <div class="small text-secondary">All registered spaces</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2">Available</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['available'] }}</div>
                    <div class="small text-secondary">Ready for use</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2">Unavailable</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['unavailable'] }}</div>
                    <div class="small text-secondary">Closed for use</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2">Maintenance</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['maintenance'] }}</div>
                    <div class="small text-secondary">Under repair</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        @forelse ($laboratories as $laboratory)
            <div class="col-12 col-md-6 col-xxl-4">
                <div class="laboratory-grid-card">
                    <div class="laboratory-frame laboratory-frame--card">
                        @if ($laboratory->image)
                            <img src="{{ asset('storage/' . $laboratory->image) }}" alt="{{ $laboratory->laboratory_name }}">
                        @else
                            <div class="laboratory-frame__placeholder">
                                <div class="laboratory-grid-card__placeholder-mark">L</div>
                                <div class="small text-secondary">No image available</div>
                            </div>
                        @endif
                    </div>
                    <div class="laboratory-grid-card__body">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                            <div>
                                <h3 class="h5 fw-semibold mb-1 text-dark">{{ $laboratory->laboratory_name }}</h3>
                                <div class="small text-secondary">{{ $laboratory->laboratory_code }}</div>
                            </div>
                            <span class="badge text-bg-{{ $laboratory->status === 'Available' ? 'success' : ($laboratory->status === 'Under Maintenance' ? 'warning' : 'secondary') }}">{{ $laboratory->status }}</span>
                        </div>

                        <div class="small text-secondary mb-3">{{ $laboratory->building ?? 'No building specified' }} · Room {{ $laboratory->room_number }}</div>

                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small text-uppercase text-secondary">Capacity</div>
                                <div class="fw-semibold text-dark">{{ $laboratory->capacity }}</div>
                            </div>

                            <div class="btn-group" role="group" aria-label="Laboratory actions">
                                <a href="{{ route('coordinator.laboratories.show', $laboratory) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                <a href="{{ route('coordinator.laboratories.edit', $laboratory) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="section-card p-5 text-center text-secondary">No laboratories found.</div>
            </div>
        @endforelse
    </div>

    <div class="section-card" id="laboratoriesTable">
        <div class="card-header bg-white border-0 pt-4 px-4 px-xl-5">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <h3 class="h5 fw-semibold mb-1">Laboratory table</h3>
                </div>

                <a href="{{ route('coordinator.laboratories.create') }}" class="btn btn-primary px-4">Add laboratory</a>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-4">Laboratory</th>
                            <th scope="col">Room</th>
                            <th scope="col">Capacity</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($laboratories as $laboratory)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="laboratory-table-thumb">
                                            @if ($laboratory->image)
                                                <img src="{{ asset('storage/' . $laboratory->image) }}" alt="{{ $laboratory->laboratory_name }}">
                                            @else
                                                <div class="laboratory-table-thumb__placeholder">L</div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $laboratory->laboratory_name }}</div>
                                            <div class="small text-secondary">{{ $laboratory->laboratory_code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $laboratory->building ?? '—' }} / {{ $laboratory->room_number }}</td>
                                <td>{{ $laboratory->capacity }}</td>
                                <td>
                                    <span class="badge text-bg-{{ $laboratory->status === 'Available' ? 'success' : ($laboratory->status === 'Under Maintenance' ? 'warning' : 'secondary') }}">{{ $laboratory->status }}</span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group" role="group" aria-label="Laboratory row actions">
                                        <a href="{{ route('coordinator.laboratories.show', $laboratory) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                        <a href="{{ route('coordinator.laboratories.edit', $laboratory) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                        <form action="{{ route('coordinator.laboratories.destroy', $laboratory) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this laboratory?');">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $laboratories->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
@endsection