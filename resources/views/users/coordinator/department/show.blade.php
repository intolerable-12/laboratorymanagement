@extends('users.coordinator.layouts.app')

@section('title', 'View Department')
@section('page-title', 'View Department')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="h4 fw-semibold mb-1 text-dark">{{ $department->department_name }}</h2>
            <div class="text-secondary">{{ $department->department_code }}</div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('coordinator.departments.edit', $department) }}" class="btn btn-primary px-4">Edit department</a>
            <a href="{{ route('coordinator.departments.index') }}" class="btn btn-outline-secondary px-4">Back to list</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-4">
            <div class="section-card h-100">
                <div class="card-body p-4 p-xl-5">
                    <div class="vstack gap-3">
                        <div>
                            <div class="small text-uppercase text-secondary mb-1">Department code</div>
                            <div class="fw-semibold text-dark">{{ $department->department_code }}</div>
                        </div>
                        <div>
                            <div class="small text-uppercase text-secondary mb-1">Department name</div>
                            <div class="fw-semibold text-dark">{{ $department->department_name }}</div>
                        </div>
                        <div>
                            <div class="small text-uppercase text-secondary mb-1">Description</div>
                            <div class="text-dark">{{ $department->description ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="small text-uppercase text-secondary mb-1">Assigned users</div>
                            <div class="fw-semibold text-dark">{{ $department->users_count }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="section-card h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 px-xl-5">
                    <h3 class="h5 fw-semibold mb-1">Assigned users</h3>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="ps-4">Name</th>
                                    <th scope="col">User ID</th>
                                    <th scope="col">Role</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($assignedUsers as $user)
                                    <tr>
                                        <td class="ps-4 fw-semibold text-dark">{{ $user->last_name }}, {{ $user->first_name }}</td>
                                        <td>{{ $user->userID }}</td>
                                        <td>{{ $user->role->role_name ?? '-' }}</td>
                                        <td>
                                            <span class="badge text-bg-{{ $user->status === 'Active' ? 'success' : 'secondary' }}">
                                                {{ $user->status }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('coordinator.users.show', $user) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-secondary py-5">No users are assigned to this department.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $assignedUsers->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
@endsection
