@extends('users.coordinator.layouts.app')

@section('title', 'User Management')
@section('page-title', 'User Management')

@php
    $tabQuery = request()->except('page');
    $tableRoute = $archived ? 'coordinator.users.archived' : 'coordinator.users.index';
    $listQuery = request()->query();
    $currentSort = $sort ?? request()->query('sort', 'name');
    $currentDirection = $direction ?? request()->query('direction', 'asc');
    $sortQuery = request()->except('page', 'sort', 'direction');

    $sortUrl = function (string $column) use ($tableRoute, $sortQuery, $currentSort, $currentDirection) {
        $nextDirection = $currentSort === $column && $currentDirection === 'asc' ? 'desc' : 'asc';

        return route($tableRoute, array_merge($sortQuery, [
            'sort' => $column,
            'direction' => $nextDirection,
        ]));
    };

    $sortIcon = function (string $column) use ($currentSort, $currentDirection) {
        if ($currentSort !== $column) {
            return 'fa-sort text-secondary opacity-50';
        }

        return $currentDirection === 'asc' ? 'fa-sort-up text-primary' : 'fa-sort-down text-primary';
    };
@endphp

@section('content')
    @if (session('status'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <div class="small text-uppercase text-secondary">User management</div>
            <div class="text-secondary">Manage users and departments.</div>
        </div>
        <div class="btn-group shadow-sm" role="group" aria-label="User management navigation">
            <a href="{{ route('coordinator.users.index', $tabQuery) }}" class="btn btn-primary">
                <i class="fa-solid fa-users me-2"></i>Users
            </a>
            <a href="{{ route('coordinator.departments.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-building-columns me-2"></i>Department
            </a>
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
            <form method="GET" action="{{ route($tableRoute) }}" class="row g-3 align-items-end" data-live-search-form="users">
                <div class="col-12 col-lg-4">
                    <label for="search" class="form-label fw-medium mb-1">Search</label>
                    <input type="search" id="search" name="search" value="{{ $search }}"
                        placeholder="User ID, name, email, or contact" class="form-control admin-form-control">
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
                            <option value="{{ $role->id }}" @selected((string) $roleId === (string) $role->id)>
                                {{ $role->role_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-lg-3">
                    <label for="department_id" class="form-label fw-medium mb-1">Department</label>
                    <select id="department_id" name="department_id" class="form-select admin-form-control">
                        <option value="">All</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected((string) $departmentId === (string) $department->id)>
                                {{ $department->department_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-lg-auto d-flex gap-2">
                    <input type="hidden" name="sort" value="{{ $currentSort }}">
                    <input type="hidden" name="direction" value="{{ $currentDirection }}">
                    <button type="submit" class="btn btn-primary px-4">Search</button>
                    <a href="{{ route($archived ? 'coordinator.users.archived' : 'coordinator.users.index') }}"
                        class="btn btn-outline-secondary px-4">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div data-live-search-results="users">
        <div class="section-card mb-4">
            <div class="card-body p-3 p-xl-4">
                <div
                    class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                    <div class="nav nav-pills gap-2">
                        <a href="{{ route('coordinator.users.index', $filters) }}"
                            class="nav-link rounded-3 {{ $archived ? '' : 'active' }}">Active users</a>
                        <a href="{{ route('coordinator.users.archived', $filters) }}"
                            class="nav-link rounded-3 {{ $archived ? 'active' : '' }}">
                            Archived users
                            <span class="badge text-bg-light border text-dark ms-2">{{ $stats['archived'] }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-card" id="usersTable">
        <div class="card-header bg-white border-0 pt-4 px-4 px-xl-5">
            <div
                class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <h3 class="h5 fw-semibold mb-1">{{ $archived ? 'Archived Users' : 'Users' }}</h3>
                </div>

                @unless ($archived)
                    <a href="{{ route('coordinator.users.create') }}" class="btn btn-primary px-4">Add user</a>
                @endunless
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-4">
                                <a href="{{ $sortUrl('userID') }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1">
                                    <span>User ID</span>
                                    <i class="fa-solid {{ $sortIcon('userID') }} small"></i>
                                </a>
                            </th>
                            <th scope="col">
                                <a href="{{ $sortUrl('name') }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1">
                                    <span>Name</span>
                                    <i class="fa-solid {{ $sortIcon('name') }} small"></i>
                                </a>
                            </th>
                            <th scope="col">
                                <a href="{{ $sortUrl('role') }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1">
                                    <span>Role</span>
                                    <i class="fa-solid {{ $sortIcon('role') }} small"></i>
                                </a>
                            </th>
                            <th scope="col">
                                <a href="{{ $sortUrl('department') }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1">
                                    <span>Department</span>
                                    <i class="fa-solid {{ $sortIcon('department') }} small"></i>
                                </a>
                            </th>
                            <th scope="col">
                                <a href="{{ $sortUrl('email') }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1">
                                    <span>Email</span>
                                    <i class="fa-solid {{ $sortIcon('email') }} small"></i>
                                </a>
                            </th>
                            <th scope="col">
                                <a href="{{ $sortUrl('status') }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1">
                                    <span>Status</span>
                                    <i class="fa-solid {{ $sortIcon('status') }} small"></i>
                                </a>
                            </th>
                            <th scope="col" class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            @php
                                $isCoordinator = strtolower((string) ($user->role->role_name ?? '')) === 'coordinator';
                            @endphp
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
                                    <span
                                        class="badge text-bg-{{ $archived ? 'secondary' : ($user->status === 'Active' ? 'success' : ($user->status === 'Suspended' ? 'warning' : 'secondary')) }}">
                                        {{ $archived ? 'Archived' : $user->status }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group action-buttons" role="group" aria-label="User actions">
                                        <!-- View Icon -->
                                        <a href="{{ route('coordinator.users.show', array_merge(['user' => $user], $listQuery)) }}"
                                            class="btn btn-sm btn-outline-secondary" title="View" aria-label="View">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>

                                        @if (!$archived)
                                            <!-- Edit Icon -->
                                            <a href="{{ route('coordinator.users.edit', array_merge(['user' => $user], $listQuery)) }}"
                                                class="btn btn-sm btn-outline-primary" title="Edit" aria-label="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>

                                            <!-- Archive Icon -->
                                            @if ($isCoordinator)
                                                {{-- Coordinator accounts cannot be archived --}}
                                            @else
                                                <form action="{{ route('coordinator.users.destroy', $user) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Archive this user?');" title="Archive"
                                                        aria-label="Archive">
                                                        <i class="fa-solid fa-box-archive"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @else
                                            <!-- Restore Icon -->
                                            <form action="{{ route('coordinator.users.restore', $user) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success"
                                                    onclick="return confirm('Restore this user?');" title="Restore"
                                                    aria-label="Restore">
                                                    <i class="fa-solid fa-rotate-left"></i>
                                                </button>
                                            </form>
                                        @endif
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

        </div>

        <div class="mt-4" data-live-search-pagination>
            {{ $users->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
