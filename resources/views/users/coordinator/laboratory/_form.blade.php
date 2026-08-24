@php
    $statuses = ['Available', 'Unavailable', 'Under Maintenance'];
    $imageUrl = !empty($laboratory?->image) ? asset('storage/' . $laboratory->image) : null;
    $isEditing = filled($laboratory?->getKey());
@endphp

<div class="row g-4">
    <div class="col-lg-4">
        <div class="equipment-preview-card h-100">
            <div class="laboratory-frame laboratory-frame--detail mb-3" data-image-preview-container>
                @if ($imageUrl)
                    <img src="{{ $imageUrl }}" alt="{{ $laboratory->laboratory_name ?? 'Laboratory image' }}" data-image-preview data-image-preview-initial-src="{{ $imageUrl }}">
                @else
                    <div class="laboratory-frame__placeholder" data-image-preview-placeholder>
                        <div class="laboratory-grid-card__placeholder-mark">L</div>
                        <div class="small text-secondary">No image available</div>
                    </div>
                    <img src="" alt="Laboratory image preview" class="d-none" data-image-preview data-image-preview-initial-src="">
                @endif
            </div>

            <label class="form-label" for="image">Laboratory image</label>
            <input type="file" id="image" name="image" class="form-control admin-form-control @error('image') is-invalid @enderror" accept="image/*" data-image-preview-input>
            <div class="form-text">JPEG, PNG, or WEBP up to 4 MB.</div>
            @error('image') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="col-lg-8">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="laboratory_code">Laboratory code</label>
                <input type="text" id="laboratory_code" name="laboratory_code" value="{{ old('laboratory_code', $laboratory->laboratory_code ?? '') }}" class="form-control admin-form-control @error('laboratory_code') is-invalid @enderror" maxlength="20" required @if($isEditing) readonly tabindex="-1" aria-readonly="true" @endif>
                @if($isEditing)
                    <div class="form-text">Laboratory code cannot be changed after creation.</div>
                @endif
                @error('laboratory_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="laboratory_name">Laboratory name</label>
                <input type="text" id="laboratory_name" name="laboratory_name" value="{{ old('laboratory_name', $laboratory->laboratory_name ?? '') }}" class="form-control admin-form-control @error('laboratory_name') is-invalid @enderror" required @if($isEditing) readonly tabindex="-1" aria-readonly="true" @endif>
                @if($isEditing)
                    <div class="form-text">Laboratory name cannot be changed after creation.</div>
                @endif
                @error('laboratory_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="building">Building</label>
                <input type="text" id="building" name="building" value="{{ old('building', $laboratory->building ?? '') }}" class="form-control admin-form-control @error('building') is-invalid @enderror" maxlength="100">
                @error('building') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="room_number">Room number</label>
                <input type="text" id="room_number" name="room_number" value="{{ old('room_number', $laboratory->room_number ?? '') }}" class="form-control admin-form-control @error('room_number') is-invalid @enderror" maxlength="50" required>
                @error('room_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="capacity">Capacity</label>
                <input type="number" id="capacity" name="capacity" value="{{ old('capacity', $laboratory->capacity ?? 0) }}" class="form-control admin-form-control @error('capacity') is-invalid @enderror" min="0" required>
                @error('capacity') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="status">Status</label>
                <select id="status" name="status" class="form-select admin-form-control @error('status') is-invalid @enderror" required>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(old('status', $laboratory->status ?? 'Available') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12">
                <label class="form-label" for="description">Description</label>
                <textarea id="description" name="description" rows="4" class="form-control admin-form-control @error('description') is-invalid @enderror">{{ old('description', $laboratory->description ?? '') }}</textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12 text-end mt-2">
                <a href="{{ route('coordinator.laboratories.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">{{ isset($laboratory) ? 'Save changes' : 'Create laboratory' }}</button>
            </div>
        </div>
    </div>
</div>
