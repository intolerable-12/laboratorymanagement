@php
    $statuses = ['Available', 'Unavailable', 'Under Maintenance'];
    $imageUrl = $laboratory?->primaryImagePath() ? asset('storage/' . $laboratory->primaryImagePath()) : null;
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

            <label class="form-label" for="images">Laboratory photos</label>
            <input type="file" id="images" name="images[]" class="form-control admin-form-control @error('images') is-invalid @enderror @error('images.*') is-invalid @enderror" accept="image/jpeg,image/png,image/webp" multiple data-laboratory-images-input>
            <div class="form-text">Add one or several JPEG, PNG, or WEBP photos. Each photo may be up to 4 MB.</div>
            @error('images') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            @error('images.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            <div class="row g-2 mt-2" data-laboratory-new-preview></div>
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
                <input type="text" id="laboratory_name" name="laboratory_name" value="{{ old('laboratory_name', $laboratory->laboratory_name ?? '') }}" class="form-control admin-form-control @error('laboratory_name') is-invalid @enderror" maxlength="255" required>
                @error('laboratory_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="building">Building</label>
                <input type="text" id="building" name="building" value="{{ old('building', $laboratory->building ?? '') }}" class="form-control admin-form-control @error('building') is-invalid @enderror" maxlength="100">
                @error('building') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="campus">Campus</label>
                <select id="campus" name="campus" class="form-select admin-form-control @error('campus') is-invalid @enderror" required>
                    @foreach (\App\Support\Campus::values() as $campus)
                        <option value="{{ $campus }}" @selected(old('campus', $laboratory->campus ?? 'Main') === $campus)>{{ $campus }} campus</option>
                    @endforeach
                </select>
                @error('campus') <div class="invalid-feedback">{{ $message }}</div> @enderror
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

@once
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.querySelector('[data-laboratory-images-input]');
            const preview = document.querySelector('[data-laboratory-new-preview]');

            if (!input || !preview) {
                return;
            }

            input.addEventListener('change', function () {
                preview.innerHTML = '';

                Array.from(input.files).forEach(function (file) {
                    const column = document.createElement('div');
                    column.className = 'col-6 col-md-4';

                    const card = document.createElement('div');
                    card.className = 'border rounded-3 overflow-hidden bg-light';

                    const image = document.createElement('img');
                    image.src = URL.createObjectURL(file);
                    image.alt = file.name;
                    image.className = 'w-100';
                    image.style.height = '120px';
                    image.style.objectFit = 'cover';

                    const label = document.createElement('div');
                    label.className = 'small text-secondary text-truncate px-2 py-2';
                    label.textContent = file.name;

                    card.appendChild(image);
                    card.appendChild(label);
                    column.appendChild(card);
                    preview.appendChild(column);
                });
            });
        });
    </script>
@endonce
