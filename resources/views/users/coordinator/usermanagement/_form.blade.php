@php
    $statuses = ['Active'];
    $genders = ['Male', 'Female'];
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
                <option value="{{ $roleOption->id }}" @selected(old('role_id', $user->role_id ?? '') == $roleOption->id)>{{ $roleOption->role_name }}</option>
            @endforeach
        </select>
        @error('role_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="department_id">Department</label>
        <select id="department_id" name="department_id" class="form-select admin-form-control @error('department_id') is-invalid @enderror">
            <option value="">No department</option>
            @foreach ($departments as $department)
                <option value="{{ $department->id }}" @selected(old('department_id', $user->department_id ?? '') == $department->id)>{{ $department->department_name }}</option>
            @endforeach
        </select>
        @error('department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
