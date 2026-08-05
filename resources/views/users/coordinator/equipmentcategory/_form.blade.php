<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="category_code">Category code</label>
        <input type="text" id="category_code" name="category_code" value="{{ old('category_code', $equipmentCategory->category_code ?? '') }}" class="form-control admin-form-control @error('category_code') is-invalid @enderror" maxlength="30" required>
        @error('category_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="category_name">Category name</label>
        <input type="text" id="category_name" name="category_name" value="{{ old('category_name', $equipmentCategory->category_name ?? '') }}" class="form-control admin-form-control @error('category_name') is-invalid @enderror" maxlength="150" required>
        @error('category_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label" for="description">Description</label>
        <textarea id="description" name="description" rows="5" class="form-control admin-form-control @error('description') is-invalid @enderror">{{ old('description', $equipmentCategory->description ?? '') }}</textarea>
        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12 text-end mt-2">
        <a href="{{ route('coordinator.equipment.categories.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
        <button type="submit" class="btn btn-primary">{{ isset($equipmentCategory) ? 'Save changes' : 'Create category' }}</button>
    </div>
</div>