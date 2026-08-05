<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status', '');
        $roleId = $request->query('role_id', '');
        $departmentId = $request->query('department_id', '');

        $usersQuery = User::with(['role', 'department'])
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
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($roleId !== '', fn ($query) => $query->where('role_id', $roleId))
            ->when($departmentId !== '', fn ($query) => $query->where('department_id', $departmentId));

        $users = $usersQuery->orderBy('last_name')->paginate(12);

        $roles = Role::orderBy('role_name')->get(['id', 'role_name']);
        $departments = Department::orderBy('department_name')->get(['id', 'department_name']);
        $statuses = ['Active', 'Inactive', 'Suspended'];

        $stats = [
            'total' => User::count(),
            'active' => User::where('status', 'Active')->count(),
            'inactive' => User::where('status', 'Inactive')->count(),
            'suspended' => User::where('status', 'Suspended')->count(),
        ];

        return view('users.coordinator.usermanagement.index', compact('users', 'stats', 'roles', 'departments', 'statuses', 'search', 'status', 'roleId', 'departmentId'));
    }

    public function create()
    {
        $roles = Role::orderBy('role_name')->get();
        $departments = Department::orderBy('department_name')->get();

        return view('users.coordinator.usermanagement.create', compact('roles', 'departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'userID' => ['required', 'string', 'max:30', 'unique:users,userID'],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'suffix' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', Rule::in(['Male', 'Female'])],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'role_id' => ['required', 'exists:roles,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'status' => ['required', Rule::in(['Active', 'Inactive', 'Suspended'])],
            'password' => ['required', 'string', 'min:8'],
        ]);

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
        $roles = Role::orderBy('role_name')->get();
        $departments = Department::orderBy('department_name')->get();

        return view('users.coordinator.usermanagement.edit', compact('user', 'roles', 'departments'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'userID' => ['required', 'string', 'max:30', Rule::unique('users', 'userID')->ignore($user->userNo, 'userNo')],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'suffix' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', Rule::in(['Male', 'Female'])],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->userNo, 'userNo')],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'role_id' => ['required', 'exists:roles,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'status' => ['required', Rule::in(['Active', 'Inactive', 'Suspended'])],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

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
        $user->delete();

        return redirect()->route('coordinator.users.index')->with('status', 'User deleted successfully.');
    }
}
