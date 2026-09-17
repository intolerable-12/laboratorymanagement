<div class="row g-3">
    <div class="col-md-8">
        <label for="semester_name" class="form-label fw-semibold">Semester name</label>
        <input type="text" id="semester_name" name="semester_name" value="{{ old('semester_name', $semester->semester_name ?? '') }}" class="form-control @error('semester_name') is-invalid @enderror" maxlength="30" placeholder="e.g. 1st Semester" required>
        @error('semester_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label for="display_order" class="form-label fw-semibold">Display order</label>
        <input type="number" id="display_order" name="display_order" value="{{ old('display_order', $semester->display_order ?? 1) }}" min="1" max="255" class="form-control @error('display_order') is-invalid @enderror" required>
        @error('display_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 text-end mt-2">
        <a href="{{ route('coordinator.academic-periods.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
        <button type="submit" class="btn btn-primary">{{ isset($semester) ? 'Save changes' : 'Create semester' }}</button>
    </div>
</div>
