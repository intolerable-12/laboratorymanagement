<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="category_code">Category code</label>
        {{-- Prefers old input after a failed validation, otherwise the model
             value in edit mode. If blank on edit, the controller may not be
             passing the model as $equipmentCategory. --}}
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
        {{-- Optional field — no required attribute. Falls back to an empty
             textarea when there's no stored value. --}}
        <textarea id="description" name="description" rows="5" class="form-control admin-form-control @error('description') is-invalid @enderror">{{ old('description', $equipmentCategory->description ?? '') }}</textarea>
        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12 text-end mt-2">
        {{-- Cancel hardcodes 'coordinator.' — no $routePrefix variable here,
             unlike the equipment/chemical partials. If facilitator access is
             ever added, this link needs updating. --}}
        <a href="{{ route('coordinator.equipment.categories.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
        {{-- Submit label toggles based on whether the model was passed into
             the partial. Passing a model with is_requestable=true etc. would
             also flip the form into edit mode, so make sure the controller
             only passes $equipmentCategory on the edit route. --}}
        <button type="submit" class="btn btn-primary">{{ isset($equipmentCategory) ? 'Save changes' : 'Create category' }}</button>
    </div>
</div>