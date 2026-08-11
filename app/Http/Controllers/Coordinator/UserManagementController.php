<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        return $this->renderIndex($request, false);
    }

    public function archived(Request $request)
    {
        return $this->renderIndex($request, true);
    }

    public function create()
    {
        $roles = $this->manageableRoles();
        $departments = Department::orderBy('department_name')->get(['id', 'department_name']);

        return view('users.coordinator.usermanagement.create', compact('roles', 'departments'));
    }

    public function store(Request $request)
    {
        $data = $this->validateUserData($request);

        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()->route('coordinator.users.index')->with('status', 'User created successfully.');
    }

    public function show(User $user)
    {
        $this->preventCoordinatorAccess($user);

        $user->load(['role', 'department']);

        return view('users.coordinator.usermanagement.show', compact('user'));
    }

    public function edit(User $user)
    {
        $this->preventCoordinatorAccess($user);

        $roles = $this->manageableRoles();
        $departments = Department::orderBy('department_name')->get(['id', 'department_name']);

        return view('users.coordinator.usermanagement.edit', compact('user', 'roles', 'departments'));
    }

    public function update(Request $request, User $user)
    {
        $this->preventCoordinatorAccess($user);

        $data = $this->validateUserData($request, $user);

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return redirect()->route('coordinator.users.index')->with('status', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $this->preventCoordinatorAccess($user);

        $user->delete();

        return redirect()->route('coordinator.users.index')->with('status', 'User archived successfully.');
    }

    public function restore(User $user)
    {
        $this->preventCoordinatorAccess($user);

        abort_unless($user->trashed(), 404);

        $user->restore();

        return redirect()->route('coordinator.users.archived')->with('status', 'User restored successfully.');
    }

    private function renderIndex(Request $request, bool $archived)
    {
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status', '');
        $roleId = $request->query('role_id', '');
        $departmentId = $request->query('department_id', '');

        $users = $this->manageableUsersQuery($archived)
            ->with(['role', 'department'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('userID', 'like', '%' . $search . '%')
                        ->orWhere('first_name', 'like', '%' . $search . '%')
                        ->orWhere('middle_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('contact_number', 'like', '%' . $search . '%');
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->when($roleId !== '', fn (Builder $query) => $query->where('role_id', $roleId))
            ->when($departmentId !== '', fn (Builder $query) => $query->where('department_id', $departmentId))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(12);

        $roles = $this->manageableRoles();
        $departments = Department::orderBy('department_name')->get(['id', 'department_name']);
        $statuses = ['Active', 'Inactive', 'Suspended'];

        $stats = [
            'total' => $this->manageableUsersQuery(false)->count(),
            'active' => $this->manageableUsersQuery(false)->where('status', 'Active')->count(),
            'inactive' => $this->manageableUsersQuery(false)->where('status', 'Inactive')->count(),
            'suspended' => $this->manageableUsersQuery(false)->where('status', 'Suspended')->count(),
            'archived' => $this->manageableUsersQuery(true)->count(),
        ];

        $filters = array_filter([
            'search' => $search,
            'status' => $status,
            'role_id' => $roleId,
            'department_id' => $departmentId,
        ], static fn ($value) => $value !== '' && $value !== null);

        return view('users.coordinator.usermanagement.index', compact(
            'users',
            'stats',
            'roles',
            'departments',
            'statuses',
            'search',
            'status',
            'roleId',
            'departmentId',
            'archived',
            'filters'
        ));
    }

    private function manageableUsersQuery(bool $archived = false): Builder
    {
        return User::query()
            ->whereHas('role', function (Builder $query) {
                $query->where('role_name', '!=', 'Coordinator');
            })
            ->when($archived, fn (Builder $query) => $query->onlyTrashed(), fn (Builder $query) => $query->withoutTrashed());
    }

    private function manageableRoles()
    {
        return Role::query()
            ->where('role_name', '!=', 'Coordinator')
            ->orderBy('role_name')
            ->get(['id', 'role_name']);
    }

    private function validateUserData(Request $request, ?User $user = null): array
    {
        $userIdRule = Rule::unique('users', 'userID');
        $emailRule = Rule::unique('users', 'email');

        if ($user) {
            $userIdRule->ignore($user->userNo, 'userNo');
            $emailRule->ignore($user->userNo, 'userNo');
        }

        return $request->validate([
            'userID' => ['required', 'string', 'max:30', $userIdRule],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'suffix' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', Rule::in(['Male', 'Female'])],
            'email' => ['required', 'email', 'max:255', $emailRule],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'role_id' => [
                'required',
                Rule::exists('roles', 'id')->where(fn (Builder $query) => $query->where('role_name', '!=', 'Coordinator')),
            ],
            'department_id' => ['nullable', 'exists:departments,id'],
            'status' => ['required', Rule::in(['Active', 'Inactive', 'Suspended'])],
            'password' => $user ? ['nullable', 'string', 'min:8'] : ['required', 'string', 'min:8'],
        ]);
    }

    private function preventCoordinatorAccess(User $user): void
    {
        $user->loadMissing('role');

        abort_if(strtolower((string) $user->role?->role_name) === 'coordinator', 403);
    }
}
