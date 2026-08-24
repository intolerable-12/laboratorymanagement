<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
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
        $user->load(['role', 'department']);

        return view('users.coordinator.usermanagement.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = $this->manageableRoles();
        $departments = Department::orderBy('department_name')->get(['id', 'department_name']);

        return view('users.coordinator.usermanagement.edit', compact('user', 'roles', 'departments'));
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validateUserData($request, $user);

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return redirect()->route('coordinator.users.index', $request->query())->with('status', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $this->preventCoordinatorDeletion($user);

        $user->delete();

        return redirect()->route('coordinator.users.index')->with('status', 'User archived successfully.');
    }

    public function restore(User $user)
    {
        $this->preventCoordinatorDeletion($user);

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
        $sort = $request->query('sort', 'name');
        $direction = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        $sortableColumns = [
            'userID' => 'userID',
            'name' => 'last_name',
            'role' => 'role_name',
            'department' => 'department_name',
            'email' => 'email',
            'status' => 'status',
        ];

        if (! array_key_exists($sort, $sortableColumns)) {
            $sort = 'name';
        }

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
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->leftJoin('departments', 'users.department_id', '=', 'departments.id')
            ->select('users.*')
            ->when($sort === 'name', function ($query) use ($direction) {
                $query->orderBy('last_name', $direction)
                    ->orderBy('first_name', $direction);
            }, function ($query) use ($sortableColumns, $sort, $direction) {
                $query->orderBy($sortableColumns[$sort], $direction)
                    ->orderBy('first_name');
            })
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
            'sort',
            'direction',
            'archived',
            'filters'
        ));
    }

    private function manageableUsersQuery(bool $archived = false): Builder
    {
        return User::query()
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

        $validated = $request->validate([
            'userID' => ['required', 'string', 'max:30', 'regex:/^[A-Za-z0-9][A-Za-z0-9._-]*$/', $userIdRule],
            'first_name' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[A-Za-zÀ-ÿ][A-Za-zÀ-ÿ\' .\- ]*$/u'],
            'middle_name' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-zÀ-ÿ][A-Za-zÀ-ÿ\' .\- ]*$/u'],
            'last_name' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[A-Za-zÀ-ÿ][A-Za-zÀ-ÿ\' .\- ]*$/u'],
            'suffix' => ['nullable', 'string', 'max:20', 'regex:/^[A-Za-z.\-]+$/u'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'gender' => ['nullable', Rule::in(['Male', 'Female'])],
            'email' => ['required', 'email', 'max:255', 'regex:/^[A-Za-z0-9._%+\-]+@lccdo\.edu\.ph$/i', $emailRule],
            'contact_number' => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9\s().-]{7,20}$/'],
            'role_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id')->where(fn (QueryBuilder $query) => $query->where('role_name', '!=', 'Coordinator')),
            ],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'status' => ['required', Rule::in(['Active', 'Inactive', 'Suspended'])],
            'password' => $user
                ? ['nullable', 'string', 'min:8', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/']
                : ['required', 'string', 'min:8', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'],
        ], [
            'userID.regex' => 'User ID may only contain letters, numbers, dots, underscores, and hyphens.',
            'first_name.regex' => 'First name may only contain letters, spaces, hyphens, apostrophes, and periods.',
            'middle_name.regex' => 'Middle name may only contain letters, spaces, hyphens, apostrophes, and periods.',
            'last_name.regex' => 'Last name may only contain letters, spaces, hyphens, apostrophes, and periods.',
            'suffix.regex' => 'Suffix may only contain letters, periods, and hyphens.',
            'birth_date.before_or_equal' => 'Birth date cannot be in the future.',
            'email.regex' => 'The email must end with @lccdo.edu.ph.',
            'contact_number.regex' => 'Contact number must be a valid phone number using digits and common separators only.',
            'password.regex' => 'Password must be at least 8 characters long and include both letters and numbers.',
        ]);

        foreach (['userID', 'first_name', 'middle_name', 'last_name', 'suffix', 'email', 'contact_number'] as $field) {
            if (! array_key_exists($field, $validated)) {
                continue;
            }

            $value = $validated[$field];

            if (is_string($value)) {
                $validated[$field] = trim($value);
            }
        }

        if (isset($validated['middle_name']) && $validated['middle_name'] === '') {
            $validated['middle_name'] = null;
        }

        if (isset($validated['suffix']) && $validated['suffix'] === '') {
            $validated['suffix'] = null;
        }

        return $validated;
    }

    private function preventCoordinatorDeletion(User $user): void
    {
        $user->loadMissing('role');

        abort_if(strtolower((string) $user->role?->role_name) === 'coordinator', 403);
    }
}
