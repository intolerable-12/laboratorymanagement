<div class="row g-3">
    <div class="col-12">
        <label for="school_year" class="form-label fw-semibold">School year</label>
        <input type="text" id="school_year" name="school_year" value="{{ old('school_year', $schoolYear->school_year ?? '') }}" class="form-control @error('school_year') is-invalid @enderror" maxlength="20" placeholder="e.g. 2026-2027" required>
        @error('school_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="start_date" class="form-label fw-semibold">Start date</label>
        <input type="date" id="start_date" name="start_date" value="{{ old('start_date', isset($schoolYear) ? $schoolYear->start_date?->format('Y-m-d') : '') }}" class="form-control @error('start_date') is-invalid @enderror" required>
        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="end_date" class="form-label fw-semibold">End date</label>
        <input type="date" id="end_date" name="end_date" value="{{ old('end_date', isset($schoolYear) ? $schoolYear->end_date?->format('Y-m-d') : '') }}" class="form-control @error('end_date') is-invalid @enderror" required>
        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 text-end mt-2">
        <a href="{{ route('coordinator.academic-periods.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
        <button type="submit" class="btn btn-primary">{{ isset($schoolYear) ? 'Save changes' : 'Create school year' }}</button>
    </div>
</div>
