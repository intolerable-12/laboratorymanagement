@extends('users.coordinator.layouts.app')

@section('title', 'User Management')
@section('page-title', 'User Management')
@section('page-subtitle', 'Create, review, and maintain user accounts')

@section('content')
    @if (session('status'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
    @endif

    <div class="hero-banner rounded-4 p-4 p-lg-5 mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-white border mb-3">
                    <span class="badge rounded-pill text-bg-primary">User administration</span>
                    <span class="small text-secondary">Manage access and account records</span>
                </div>

                <h2 class="display-6 fw-semibold text-dark mb-2">Centralize every user account from one place.</h2>
                <p class="lead text-secondary mb-0" style="max-width: 58rem;">Create, review, update, and remove coordinator, facilitator, instructor, and student accounts without leaving the dashboard.</p>
            </div>

            <div class="col-lg-4">
                <div class="section-card p-4 h-100">
                    <div class="small text-uppercase text-secondary mb-2">Quick actions</div>
                    <div class="d-grid gap-2">
                        <a href="{{ route('coordinator.users.create') }}" class="btn btn-primary">Add user</a>
                        <a href="#usersTable" class="btn btn-outline-secondary">Jump to table</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 g-xl-4 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2">Total users</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['total'] }}</div>
                    <div class="small text-secondary">All accounts on record</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2">Active</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['active'] }}</div>
                    <div class="small text-secondary">Ready for sign-in</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2">Inactive</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['inactive'] }}</div>
                    <div class="small text-secondary">Temporarily disabled</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2">Suspended</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['suspended'] }}</div>
                    <div class="small text-secondary">Needs admin review</div>
                </div>
            </div>
        </div>
    </div>

    <div class="section-card mb-4">
        <div class="card-body p-4 p-xl-5">
            <form method="GET" action="{{ route('coordinator.users.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-lg-4">
                    <label for="search" class="form-label fw-medium mb-1">Search</label>
                    <input
                        type="search"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="User ID, name, email, or contact"
                        class="form-control admin-form-control"
                    >
                </div>

                <div class="col-6 col-lg-2">
                    <label for="status" class="form-label fw-medium mb-1">Status</label>
                    <select id="status" name="status" class="form-select admin-form-control">
                        <option value="">All</option>
                        @foreach ($statuses as $option)
                            <option value="{{ $option }}" @selected($status === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-lg-3">
                    <label for="role_id" class="form-label fw-medium mb-1">Role</label>
                    <select id="role_id" name="role_id" class="form-select admin-form-control">
                        <option value="">All</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected((string) $roleId === (string) $role->id)>{{ $role->role_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-lg-3">
                    <label for="department_id" class="form-label fw-medium mb-1">Department</label>
                    <select id="department_id" name="department_id" class="form-select admin-form-control">
                        <option value="">All</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected((string) $departmentId === (string) $department->id)>{{ $department->department_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-lg-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">Search</button>
                    <a href="{{ route('coordinator.users.index') }}" class="btn btn-outline-secondary px-4">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="section-card" id="usersTable">
        <div class="card-header bg-white border-0 pt-4 px-4 px-xl-5">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <h3 class="h5 fw-semibold mb-1">Users</h3>
                    <p class="mb-0 text-secondary">Browse accounts and take action with the buttons on the right.</p>
                </div>

                <a href="{{ route('coordinator.users.create') }}" class="btn btn-primary px-4">Add user</a>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-4">User ID</th>
                            <th scope="col">Name</th>
                            <th scope="col">Role</th>
                            <th scope="col">Department</th>
                            <th scope="col">Email</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td class="ps-4 fw-medium text-dark">{{ $user->userID }}</td>
                                <td>
                                    <div class="fw-medium text-dark">
                                        {{ $user->last_name }}, {{ $user->first_name }}
                                        @if ($user->middle_name)
                                            {{ ' ' . $user->middle_name }}
                                        @endif
                                        @if ($user->suffix)
                                            {{ ' ' . $user->suffix }}
                                        @endif
                                    </div>
                                    <div class="small text-secondary">{{ $user->email }}</div>
                                </td>
                                <td>{{ $user->role->role_name ?? '—' }}</td>
                                <td>{{ $user->department->department_name ?? '—' }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="badge text-bg-{{ $user->status === 'Active' ? 'success' : ($user->status === 'Suspended' ? 'warning' : 'secondary') }}">
                                        {{ $user->status }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group" role="group" aria-label="User actions">
                                        <a href="{{ route('coordinator.users.show', $user) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                        <a href="{{ route('coordinator.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                        <form action="{{ route('coordinator.users.destroy', $user) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this user?');">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-secondary py-5">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $users->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
@endsection
