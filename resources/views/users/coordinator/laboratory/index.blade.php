@extends('users.coordinator.layouts.app')

@section('title', 'Laboratories')
@section('page-title', 'Laboratories')

@section('content')
    @if (session('status'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
    @endif

    {{-- Metrics Cards --}}
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

    {{-- Controls & View Toggle Header --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <h2 class="h4 fw-bold text-dark mb-0">Overview</h2>
        
        <div class="d-flex align-items-center gap-3 ms-auto ms-sm-0">
            {{-- Switcher Button Group --}}
            <div class="btn-group" role="group" aria-label="View switch toggle">
                <button type="button" class="btn btn-outline-secondary" id="btnTableView" onclick="switchLabView('table')">
                    <i class="bi bi-list-task me-1"></i> List
                </button>
                <button type="button" class="btn btn-outline-secondary" id="btnCardView" onclick="switchLabView('card')">
                    <i class="bi bi-grid-fill me-1"></i> Cards
                </button>
            </div>

            <a href="{{ route('coordinator.laboratories.create') }}" class="btn btn-primary px-4">Add laboratory</a>
        </div>
    </div>

    {{-- LIST / TABLE VIEW (DEFAULT) --}}
    <div id="tableViewSection">
        <div class="section-card" id="laboratoriesTable">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="text-dark ps-4">Laboratory</th>
                                <th scope="col" class="text-dark">Room</th>
                                <th scope="col" class="text-dark">Capacity</th>
                                <th scope="col" class="text-dark">Status</th>
                                <th scope="col" class="text-center text-dark pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($laboratories as $laboratory)
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
                                    <td class="text-center pe-4">
                                        <div class="btn-group action-buttons" role="group" aria-label="Laboratory row actions">
                                            <!-- View Icon -->
                                            <a href="{{ route('coordinator.laboratories.show', $laboratory) }}"
                                                class="btn btn-sm btn-outline-secondary"
                                                title="View" aria-label="View">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>

                                            <!-- Edit Icon -->
                                            <a href="{{ route('coordinator.laboratories.edit', $laboratory) }}"
                                                class="btn btn-sm btn-outline-primary"
                                                title="Edit" aria-label="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>

                                            <!-- Delete Icon -->
                                            <form action="{{ route('coordinator.laboratories.destroy', $laboratory) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Delete this laboratory?');"
                                                    title="Delete" aria-label="Delete">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-secondary py-4">No laboratories found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- CARD VIEW --}}
    <div id="cardViewSection" class="d-none">
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
                                    <!-- View Icon -->
                                    <a href="{{ route('coordinator.laboratories.show', $laboratory) }}"
                                        class="btn btn-sm btn-outline-secondary"
                                        title="View" aria-label="View">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>

                                    <!-- Edit Icon -->
                                    <a href="{{ route('coordinator.laboratories.edit', $laboratory) }}"
                                        class="btn btn-sm btn-outline-primary"
                                        title="Edit" aria-label="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
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
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $laboratories->withQueryString()->links('pagination::bootstrap-5') }}
    </div>

    {{-- Switcher Logic JavaScript --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Read view mode from localStorage or default to 'table'
            const activeView = localStorage.getItem('laboratory_view_preference') || 'table';
            switchLabView(activeView);
        });

        function switchLabView(mode) {
            const tableView = document.getElementById('tableViewSection');
            const cardView = document.getElementById('cardViewSection');
            const btnTable = document.getElementById('btnTableView');
            const btnCard = document.getElementById('btnCardView');

            if (mode === 'card') {
                tableView.classList.add('d-none');
                cardView.classList.remove('d-none');

                btnCard.classList.add('active', 'btn-primary');
                btnCard.classList.remove('btn-outline-secondary');

                btnTable.classList.remove('active', 'btn-primary');
                btnTable.classList.add('btn-outline-secondary');
            } else {
                cardView.classList.add('d-none');
                tableView.classList.remove('d-none');

                btnTable.classList.add('active', 'btn-primary');
                btnTable.classList.remove('btn-outline-secondary');

                btnCard.classList.remove('active', 'btn-primary');
                btnCard.classList.add('btn-outline-secondary');
            }

            // Save preference
            localStorage.setItem('laboratory_view_preference', mode);
        }
    </script>
@endsection