@extends(request()->routeIs('facilitator.*') ? 'users.facilitator.layouts.app' : 'users.coordinator.layouts.app')

@php
    $routePrefix = request()->routeIs('facilitator.*') ? 'facilitator' : 'coordinator';
@endphp

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Equipment Lifespan</h1>
                <p class="text-muted mb-0">{{ $campus }} campus equipment lifespan and expiry dates.</p>
            </div>
        </div>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        {{-- Search + laboratory filter --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body p-3 p-md-4">
                <form method="GET" action="{{ route($routePrefix.'.equipment-lifespans.index') }}"
                      class="row g-3 align-items-end"
                      data-live-search-form="equipment-lifespans">

                    <div class="col-12 col-md-6 col-lg-7">
                        <label for="lifespan-search" class="form-label small fw-semibold text-dark mb-1">Search equipment</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="fa-solid fa-magnifying-glass text-secondary" aria-hidden="true"></i>
                            </span>
                            <input type="search" id="lifespan-search" name="search"
                                   value="{{ $search ?? '' }}" class="form-control"
                                   placeholder="Name, code, brand, or model">
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <label for="lifespan-laboratory" class="form-label small fw-semibold text-dark mb-1">Laboratory</label>
                        <select id="lifespan-laboratory" name="laboratory_id" class="form-select">
                            <option value="">All laboratories</option>
                            @foreach ($laboratories as $laboratory)
                                <option value="{{ $laboratory->id }}" @selected((string) ($laboratoryId ?? '') === (string) $laboratory->id)>
                                    {{ $laboratory->laboratory_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-lg-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">Apply</button>
                        <a href="{{ route($routePrefix.'.equipment-lifespans.index') }}" class="btn btn-outline-secondary">Clear</a>
                    </div>
                </form>
            </div>
        </div>

        <div data-live-search-results="equipment-lifespans">
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-dark">Equipment</th>
                                <th class="text-dark">Laboratory</th>
                                <th class="text-dark">Supplier</th>
                                <th class="text-dark">Lifespan</th>
                                <th class="text-dark">Expires</th>
                                <th class="text-dark text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($equipment as $item)
                                @php
                                    $mode = $item->lifespan_mode ?: 'date';
                                    $unit = $item->lifespan_months && $item->lifespan_years
                                        ? 'both'
                                        : ($item->lifespan_months ? 'months' : 'years');
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->equipment_name }}</div>
                                        <small class="text-muted">{{ $item->equipment_code }}</small>
                                    </td>
                                    <td>{{ $item->laboratory?->laboratory_name }}</td>
                                    <td>{{ $item->supplier?->supplier_name ?? '-' }}</td>
                                    <td>
                                        @if ($item->lifespan_mode === 'duration')
                                            {{ $item->lifespan_years ? $item->lifespan_years.' year(s)' : '' }}
                                            @if ($item->lifespan_years && $item->lifespan_months)
                                                {{ ' + ' }}
                                            @endif
                                            {{ $item->lifespan_months ? $item->lifespan_months.' month(s)' : '' }}
                                        @else
                                            {{ $item->lifespan_mode === 'date' ? 'Specific date' : 'Not set' }}
                                        @endif
                                    </td>
                                    <td>{{ $item->lifespan_expires_at?->format('M j, Y g:i A') ?? '-' }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary" type="button"
                                            data-bs-toggle="modal" data-bs-target="#lifespanModal{{ $item->id }}">
                                            {{ $item->lifespan_expires_at ? 'Edit lifespan' : 'Set lifespan' }}
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        No equipment matches the current filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Modals must live inside the live-search results so they
                     travel with pagination / filter changes. --}}
                @foreach ($equipment as $item)
                    @php
                        $mode = $item->lifespan_mode ?: 'date';
                        $unit = $item->lifespan_months && $item->lifespan_years
                            ? 'both'
                            : ($item->lifespan_months ? 'months' : 'years');
                        $dateValue = $item->lifespan_expires_at?->format('Y-m-d\TH:i');
                        $isSubmittedItem = (string) old('equipment_id') === (string) $item->id;
                        $mode = $isSubmittedItem ? old('lifespan_mode', $mode) : $mode;
                        $unit = $isSubmittedItem ? old('duration_unit', $unit) : $unit;
                        $dateValue = $isSubmittedItem ? old('lifespan_expires_at', $dateValue) : $dateValue;
                        $months = $isSubmittedItem ? old('lifespan_months', $item->lifespan_months) : $item->lifespan_months;
                        $years = $isSubmittedItem ? old('lifespan_years', $item->lifespan_years) : $item->lifespan_years;
                    @endphp
                    <div class="modal fade" id="lifespanModal{{ $item->id }}" tabindex="-1"
                        aria-labelledby="lifespanModalLabel{{ $item->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form method="POST" action="{{ route($routePrefix.'.equipment-lifespans.update', $item) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="equipment_id" value="{{ $item->id }}">
                                    <div class="modal-header">
                                        <h2 class="modal-title fs-5" id="lifespanModalLabel{{ $item->id }}">
                                            {{ $item->equipment_name }}
                                        </h2>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <label class="form-label" for="lifespan-mode-{{ $item->id }}">Set lifespan by</label>
                                        <select class="form-select lifespan-mode" id="lifespan-mode-{{ $item->id }}"
                                            name="lifespan_mode" data-item="{{ $item->id }}">
                                            <option value="date" @selected($mode === 'date')>Specific date</option>
                                            <option value="duration" @selected($mode === 'duration')>Months and/or years</option>
                                        </select>

                                        <div class="mt-3 lifespan-date" data-item="{{ $item->id }}">
                                            <label class="form-label" for="lifespan-date-{{ $item->id }}">Expiry date</label>
                                            <input class="form-control" type="date"
                                                id="lifespan-date-{{ $item->id }}" name="lifespan_expires_at"
                                                value="{{ $dateValue }}" min="{{ now()->toDateString() }}">
                                        </div>

                                        <div class="mt-3 lifespan-duration d-none" data-item="{{ $item->id }}">
                                            <label class="form-label" for="duration-unit-{{ $item->id }}">Duration unit</label>
                                            <select class="form-select duration-unit" id="duration-unit-{{ $item->id }}"
                                                name="duration_unit" data-item="{{ $item->id }}">
                                                <option value="months" @selected($unit === 'months')>Months</option>
                                                <option value="years" @selected($unit === 'years')>Years</option>
                                                <option value="both" @selected($unit === 'both')>Both</option>
                                            </select>
                                            <div class="row g-3 mt-1">
                                                <div class="col month-field" data-item="{{ $item->id }}">
                                                    <label class="form-label" for="lifespan-months-{{ $item->id }}">Months</label>
                                                    <input class="form-control" type="number" min="1" max="1200"
                                                        id="lifespan-months-{{ $item->id }}" name="lifespan_months"
                                                        value="{{ $months }}">
                                                </div>
                                                <div class="col year-field" data-item="{{ $item->id }}">
                                                    <label class="form-label" for="lifespan-years-{{ $item->id }}">Years</label>
                                                    <input class="form-control" type="number" min="1" max="100"
                                                        id="lifespan-years-{{ $item->id }}" name="lifespan_years"
                                                        value="{{ $years }}">
                                                </div>
                                            </div>
                                            <small class="text-muted d-block mt-2">
                                                Duration is counted from purchase date, then manufacturing date, or today if neither is recorded.
                                            </small>
                                        </div>

                                        @if ($errors->any() && $isSubmittedItem)
                                            <div class="alert alert-danger mt-3 mb-0">
                                                @foreach ($errors->all() as $error)
                                                    <div>{{ $error }}</div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Save lifespan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach

                @if ($equipment->hasPages())
                    <div class="card-footer bg-white" data-live-search-pagination>
                        {{ $equipment->withQueryString()->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.lifespan-mode').forEach((mode) => {
            const item = mode.dataset.item;
            const updateMode = () => {
                const isDuration = mode.value === 'duration';
                document.querySelector(`.lifespan-date[data-item="${item}"]`).classList.toggle('d-none', isDuration);
                document.querySelector(`.lifespan-duration[data-item="${item}"]`).classList.toggle('d-none', !isDuration);
                updateUnit();
            };
            const unit = document.querySelector(`.duration-unit[data-item="${item}"]`);
            const updateUnit = () => {
                document.querySelector(`.month-field[data-item="${item}"]`).classList.toggle('d-none', unit.value === 'years');
                document.querySelector(`.year-field[data-item="${item}"]`).classList.toggle('d-none', unit.value === 'months');
            };
            mode.addEventListener('change', updateMode);
            unit.addEventListener('change', updateUnit);
            updateMode();
        });

        @if ($errors->any())
            window.addEventListener('DOMContentLoaded', () => {
                const errorModalId = @json(old('equipment_id'));
                const errorModal = errorModalId ? document.getElementById(`lifespanModal${errorModalId}`) : null;
                if (errorModal && window.bootstrap) {
                    window.bootstrap.Modal.getOrCreateInstance(errorModal).show();
                }
            });
        @endif
    </script>
@endsection
