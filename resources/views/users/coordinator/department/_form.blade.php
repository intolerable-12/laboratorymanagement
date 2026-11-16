<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="department_code">Department code</label>
        {{-- Prefers old input after a failed validation, otherwise the model
             value in edit mode. If blank on edit, confirm the controller
             passes the model as $department. --}}
        <input
            type="text"
            id="department_code"
            name="department_code"
            value="{{ old('department_code', $department->department_code ?? '') }}"
            class="form-control admin-form-control @error('department_code') is-invalid @enderror"
            maxlength="20"
            required
        >
        {{-- Validation message for the department_code field. --}}
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

    <div class="col-md-6">
        <label class="form-label" for="campus">Campus</label>
        {{-- Options come from a support class (\App\Support\Campus::values())
             rather than being hardcoded — so adding a new campus only requires
             updating that class. Default is 'Main' when creating. --}}
        <select id="campus" name="campus" class="form-select admin-form-control @error('campus') is-invalid @enderror" required>
            @foreach (\App\Support\Campus::values() as $campus)
                <option value="{{ $campus }}" @selected(old('campus', $department->campus ?? 'Main') === $campus)>{{ $campus }} campus</option>
            @endforeach
        </select>
        @error('campus') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label" for="description">Description</label>
        {{-- Optional field — no required attribute. Falls back to empty
             textarea when there's no stored value. --}}
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
        {{-- Cancel routes directly to the departments index. Note this form
             hardcodes 'coordinator.' rather than using a $routePrefix variable
             like other forms in this codebase — if facilitator access is ever
             added, this needs updating. --}}
        <a href="{{ route('coordinator.departments.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
        {{-- Submit label toggles between create and edit based on whether the
             model was passed into the partial. --}}
        <button type="submit" class="btn btn-primary">{{ isset($department) ? 'Save changes' : 'Create department' }}</button>
    </div>
</div>