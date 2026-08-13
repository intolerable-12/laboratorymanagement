<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="department_code">Department code</label>
        <input
            type="text"
            id="department_code"
            name="department_code"
            value="{{ old('department_code', $department->department_code ?? '') }}"
            class="form-control admin-form-control @error('department_code') is-invalid @enderror"
            maxlength="20"
            required
        >
        @error('department_code')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="department_name">Department name</label>
        <input
            type="text"
            id="department_name"
            name="department_name"
            value="{{ old('department_name', $department->department_name ?? '') }}"
            class="form-control admin-form-control @error('department_name') is-invalid @enderror"
            maxlength="150"
            required
        >
        @error('department_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label class="form-label" for="description">Description</label>
        <textarea
            id="description"
            name="description"
            rows="5"
            class="form-control admin-form-control @error('description') is-invalid @enderror"
        >{{ old('description', $department->description ?? '') }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 text-end mt-2">
        <a href="{{ route('coordinator.departments.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
        <button type="submit" class="btn btn-primary">{{ isset($department) ? 'Save changes' : 'Create department' }}</button>
    </div>
</div>
