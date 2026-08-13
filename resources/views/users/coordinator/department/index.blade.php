@extends('users.coordinator.layouts.app')

@section('title', 'Departments')
@section('page-title', 'Departments')

@section('content')
    @if (session('status'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">{{ session('error') }}</div>
    @endif

    <div class="row g-3 g-xl-4 mb-4">
        <div class="col-12 col-sm-4">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2">Total departments</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['total'] }}</div>
                    <div class="small text-secondary">All department records</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2">In use</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['with_users'] }}</div>
                    <div class="small text-secondary">Assigned to users</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2">Empty</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['empty'] }}</div>
                    <div class="small text-secondary">No users assigned</div>
                </div>
            </div>
        </div>
    </div>

    <div class="section-card mb-4">
        <div class="card-body p-4 p-xl-5">
            <form method="GET" action="{{ route('coordinator.departments.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-lg-8">
                    <label for="search" class="form-label fw-medium mb-1">Search</label>
                    <input
                        type="search"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Department code, name, or description"
                        class="form-control admin-form-control"
                    >
                </div>

                <div class="col-12 col-lg-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">Search</button>
                    <a href="{{ route('coordinator.departments.index') }}" class="btn btn-outline-secondary px-4">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="section-card" id="departmentsTable">
        <div class="card-header bg-white border-0 pt-4 px-4 px-xl-5">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <h3 class="h5 fw-semibold mb-1">Department list</h3>
                </div>

                <a href="{{ route('coordinator.departments.create') }}" class="btn btn-primary px-4">Add department</a>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-4">Department</th>
                            <th scope="col">Code</th>
                            <th scope="col">Users</th>
                            <th scope="col">Description</th>
                            <th scope="col" class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($departments as $department)
                            @php
                                $hasUsers = $department->users_count > 0;
                            @endphp
                            <tr>
                                <td class="ps-4 fw-semibold text-dark">{{ $department->department_name }}</td>
                                <td><span class="badge text-bg-light border text-dark">{{ $department->department_code }}</span></td>
                                <td>{{ $department->users_count }}</td>
                                <td class="text-secondary">{{ $department->description ?? '-' }}</td>
                                <td class="text-end pe-4">
                                    <div class="btn-group" role="group" aria-label="Department actions">
                                        <a href="{{ route('coordinator.departments.show', $department) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                        <a href="{{ route('coordinator.departments.edit', $department) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                        @if ($hasUsers)
                                            <button type="button" class="btn btn-sm btn-outline-danger" disabled title="Users are assigned to this department">Delete</button>
                                        @else
                                            <form action="{{ route('coordinator.departments.destroy', $department) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this department?');">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-5">No departments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $departments->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
@endsection
