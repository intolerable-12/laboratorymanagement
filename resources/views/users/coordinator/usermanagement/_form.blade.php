@php
    $statuses = ['Active'];
    $genders = ['Male', 'Female'];
    $selectedRoleId = old('role_id', $user->role_id ?? '');
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="userID">User ID</label>
        <input type="text" id="userID" name="userID" value="{{ old('userID', $user->userID ?? '') }}" class="form-control admin-form-control @error('userID') is-invalid @enderror" maxlength="30" required>
        @error('userID') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="email">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email', $user->email ?? '') }}" class="form-control admin-form-control @error('email') is-invalid @enderror" placeholder="name@lccdo.edu.ph" required>
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="first_name">First name</label>
        <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $user->first_name ?? '') }}" class="form-control admin-form-control @error('first_name') is-invalid @enderror" required>
        @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="middle_name">Middle name</label>
        <input type="text" id="middle_name" name="middle_name" value="{{ old('middle_name', $user->middle_name ?? '') }}" class="form-control admin-form-control @error('middle_name') is-invalid @enderror">
        @error('middle_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="last_name">Last name</label>
        <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $user->last_name ?? '') }}" class="form-control admin-form-control @error('last_name') is-invalid @enderror" required>
        @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="suffix">Suffix</label>
        <input type="text" id="suffix" name="suffix" value="{{ old('suffix', $user->suffix ?? '') }}" class="form-control admin-form-control @error('suffix') is-invalid @enderror">
        @error('suffix') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="birth_date">Birth date</label>
        <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date', optional($user?->birth_date)->format('Y-m-d')) }}" class="form-control admin-form-control @error('birth_date') is-invalid @enderror">
        @error('birth_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="gender">Gender</label>
        <select id="gender" name="gender" class="form-select admin-form-control @error('gender') is-invalid @enderror">
            <option value="">Select gender</option>
            @foreach ($genders as $optionGender)
                <option value="{{ $optionGender }}" @selected(old('gender', $user->gender ?? '') === $optionGender)>{{ $optionGender }}</option>
            @endforeach
        </select>
        @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="contact_number">Contact number</label>
        <input type="text" id="contact_number" name="contact_number" value="{{ old('contact_number', $user->contact_number ?? '') }}" class="form-control admin-form-control @error('contact_number') is-invalid @enderror">
        @error('contact_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="role_id">Role</label>
        <select id="role_id" name="role_id" class="form-select admin-form-control @error('role_id') is-invalid @enderror" required>
            <option value="">Select role</option>
            @foreach ($roles as $roleOption)
                <option value="{{ $roleOption->id }}" data-role-name="{{ strtolower($roleOption->role_name) }}" @selected(old('role_id', $user->role_id ?? '') == $roleOption->id)>{{ $roleOption->role_name }}</option>
            @endforeach
        </select>
        @error('role_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div id="department-field" class="col-md-4" @if (!filled($selectedRoleId)) hidden @endif>
        <label class="form-label" for="department_id">Department</label>
        <select id="department_id" name="department_id" class="form-select admin-form-control @error('department_id') is-invalid @enderror">
            <option value="">No department</option>
            @foreach ($departments as $department)
                <option value="{{ $department->id }}" data-department-code="{{ strtoupper($department->department_code) }}" data-department-name="{{ strtoupper($department->department_name) }}" @selected(old('department_id', $user->department_id ?? '') == $department->id)>{{ $department->department_name }}</option>
            @endforeach
        </select>
        <div id="department-help" class="form-text"></div>
        @error('department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="campus">Campus assignment</label>
        <select id="campus" name="campus" class="form-select admin-form-control @error('campus') is-invalid @enderror">
            <option value="">Derived from department</option>
            @foreach (\App\Support\Campus::values() as $campus)
                <option value="{{ $campus }}" @selected(old('campus', $user->campus ?? '') === $campus)>{{ $campus }} campus</option>
            @endforeach
        </select>
        <div class="form-text">Required only for Laboratory In-charge accounts.</div>
        @error('campus') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="status">Status</label>
        <select id="status" name="status" class="form-select admin-form-control @error('status') is-invalid @enderror" required>
            @foreach ($statuses as $statusOption)
                <option value="{{ $statusOption }}" @selected(old('status', $user->status ?? 'Active') === $statusOption)>{{ $statusOption }}</option>
            @endforeach
        </select>
        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="password">Password</label>
        <input type="password" id="password" name="password" class="form-control admin-form-control @error('password') is-invalid @enderror" {{ isset($user) ? '' : 'required' }}>
        <div class="form-text">
            {{ isset($user) ? 'Leave blank to keep the current password.' : 'Password must be at least 8 characters.' }}
        </div>
        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12 text-end mt-3">
        <a href="{{ route('coordinator.users.index', request()->query()) }}" class="btn btn-outline-secondary me-2">Cancel</a>
        <button type="submit" class="btn btn-primary">{{ isset($user) ? 'Save changes' : 'Create user' }}</button>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const roleSelect = document.getElementById('role_id');
            const departmentField = document.getElementById('department-field');
            const departmentSelect = document.getElementById('department_id');
            const departmentHelp = document.getElementById('department-help');

            if (!roleSelect || !departmentField || !departmentSelect) {
                return;
            }

            const restrictedDepartments = {
                student: new Set(['SHS', 'COL']),
                instructor: new Set(['JHS', 'SHS', 'COL']),
                'laboratory in-charge': new Set(['JHS', 'SHS', 'COL']),
            };
            const departmentOptions = Array.from(departmentSelect.options).map(function (option) {
                return option.cloneNode(true);
            });

            const departmentType = function (option) {
                const code = (option.dataset.departmentCode || '').trim().toUpperCase();
                const name = (option.dataset.departmentName || '').trim().toUpperCase();

                if (code === 'JHS' || name.includes('JUNIOR HIGH')) {
                    return 'JHS';
                }

                if (code === 'SHS' || name.includes('SENIOR HIGH')) {
                    return 'SHS';
                }

                if (code === 'COL' || name.includes('COLLEGE')) {
                    return 'COL';
                }

                return code;
            };

            const updateDepartments = function () {
                const selectedRole = roleSelect.options[roleSelect.selectedIndex]?.dataset.roleName || '';
                const allowedDepartments = restrictedDepartments[selectedRole] || null;
                const selectedDepartment = departmentSelect.value;

                departmentField.hidden = selectedRole === '';
                departmentField.setAttribute('aria-hidden', selectedRole === '' ? 'true' : 'false');

                if (selectedRole === '') {
                    departmentSelect.value = '';
                    departmentSelect.required = false;
                    departmentHelp.textContent = '';
                    return;
                }

                const visibleOptions = departmentOptions.filter(function (option) {
                    return !option.value || !allowedDepartments || allowedDepartments.has(departmentType(option));
                });

                while (departmentSelect.firstChild) {
                    departmentSelect.removeChild(departmentSelect.firstChild);
                }

                visibleOptions.forEach(function (option) {
                    departmentSelect.appendChild(option.cloneNode(true));
                });

                if (selectedDepartment && allowedDepartments) {
                    if (Array.from(departmentSelect.options).some(function (option) {
                        return option.value === selectedDepartment;
                    })) {
                        departmentSelect.value = selectedDepartment;
                    } else {
                        departmentSelect.value = '';
                    }
                }

                departmentSelect.required = selectedRole === 'student' || selectedRole === 'instructor';

                if (selectedRole === 'student') {
                    departmentHelp.textContent = 'Student accounts may be assigned to SHS or College only.';
                } else if (selectedRole === 'instructor' || selectedRole === 'laboratory in-charge') {
                    departmentHelp.textContent = 'Available departments: JHS, SHS, and College.';
                } else {
                    departmentHelp.textContent = '';
                }
            };

            roleSelect.addEventListener('change', updateDepartments);
            updateDepartments();
        });
    </script>
@endpush
