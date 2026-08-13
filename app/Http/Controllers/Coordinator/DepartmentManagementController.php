<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentManagementController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $departments = Department::query()
            ->withCount('users')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('department_code', 'like', '%' . $search . '%')
                        ->orWhere('department_name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('department_name')
            ->paginate(10);

        $stats = [
            'total' => Department::count(),
            'with_users' => Department::has('users')->count(),
            'empty' => Department::doesntHave('users')->count(),
        ];

        return view('users.coordinator.department.index', compact('departments', 'stats', 'search'));
    }

    public function create()
    {
        return view('users.coordinator.department.create');
    }

    public function store(Request $request)
    {
        $department = Department::create($this->validateDepartment($request));

        return redirect()
            ->route('coordinator.departments.show', $department)
            ->with('status', 'Department created successfully.');
    }

    public function show(Department $department)
    {
        $department->loadCount('users');
        $assignedUsers = $department->users()
            ->with('role')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(8);

        return view('users.coordinator.department.show', compact('department', 'assignedUsers'));
    }

    public function edit(Department $department)
    {
        return view('users.coordinator.department.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $department->update($this->validateDepartment($request, $department));

        return redirect()
            ->route('coordinator.departments.index')
            ->with('status', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        if ($department->users()->exists()) {
            return redirect()
                ->route('coordinator.departments.index')
                ->with('error', 'Remove or reassign users before deleting this department.');
        }

        $department->delete();

        return redirect()
            ->route('coordinator.departments.index')
            ->with('status', 'Department deleted successfully.');
    }

    private function validateDepartment(Request $request, ?Department $department = null): array
    {
        $departmentKey = $department?->getKey();

        return $request->validate([
            'department_code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('departments', 'department_code')->ignore($departmentKey),
            ],
            'department_name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('departments', 'department_name')->ignore($departmentKey),
            ],
            'description' => ['nullable', 'string'],
        ]);
    }
}
