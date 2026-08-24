@php
    $hazards = ['Non-Hazardous', 'Flammable', 'Corrosive', 'Oxidizer', 'Toxic', 'Explosive', 'Compressed Gas', 'Irritant', 'Environmental Hazard'];
    $statuses = ['Available', 'Low Stock', 'Expired', 'Disposed', 'Unavailable'];
    $unitOptions = $unitOptions ?? ['ml', 'cc', 'liter', 'kg', 'g'];
    $storageLocations = $storageLocations ?? ['Cabinet 1', 'Cabinet 2', 'Flammable storage', 'Freezers', 'Racks', 'Shelf A', 'Shelf B', 'Cold room', 'Other'];
    $imageUrl = !empty($chemical?->image) ? asset('storage/' . $chemical->image) : null;
    $manufacturedDate = old('manufactured_date', optional($chemical?->manufactured_date)->format('Y-m-d'));
    $receivedDate = old('received_date', optional($chemical?->received_date)->format('Y-m-d'));
    $expirationDate = old('expiration_date', optional($chemical?->expiration_date)->format('Y-m-d'));
@endphp

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="equipment-preview-card h-100">
            @if ($imageUrl)
                <img src="{{ $imageUrl }}" alt="{{ $chemical->chemical_name ?? 'Chemical image' }}" class="equipment-preview rounded-4 mb-3" data-image-preview data-image-preview-initial-src="{{ $imageUrl }}">
            @else
                <div class="equipment-image-placeholder rounded-4 d-flex flex-column align-items-center justify-content-center text-center px-4 py-5 mb-3" data-image-preview-placeholder>
                    <div class="equipment-image-placeholder__icon">
                        <i class="fa-solid fa-vial-circle-exclamation fa-lg" aria-hidden="true"></i>
                    </div>
                    <div class="fw-semibold">No image uploaded</div>
                    <div class="small text-secondary">Add a photo to make the inventory easier to scan.</div>
                </div>
                <img src="" alt="Chemical image preview" class="equipment-preview rounded-4 mb-3 d-none" data-image-preview data-image-preview-initial-src="">
            @endif

            <label class="form-label" for="image">Chemical image</label>
            <input type="file" id="image" name="image" class="form-control admin-form-control @error('image') is-invalid @enderror" accept="image/*" data-image-preview-input>
            <div class="form-text">JPEG, PNG, or WEBP up to 4 MB.</div>
            @error('image') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="col-lg-8">
        <div class="row g-3">
            <div class="col-12">
                <div class="alert alert-info border-0 rounded-4 mb-0">
                    The chemical code is generated automatically when you save this record and cannot be edited later.
                    @if (!empty($chemical?->chemical_code))
                        <div class="mt-2 fw-semibold text-dark">Current chemical code: {{ $chemical->chemical_code }}</div>
                    @endif
                    @if (!empty($chemical?->barcode))
                        <div class="fw-semibold text-dark">Current barcode: {{ $chemical->barcode }}</div>
                    @endif
                </div>
            </div>

            <div class="col-md-12">
                <label class="form-label" for="chemical_name">Chemical name</label>
                <input type="text" id="chemical_name" name="chemical_name" value="{{ old('chemical_name', $chemical->chemical_name ?? '') }}" class="form-control admin-form-control @error('chemical_name') is-invalid @enderror" required>
                @error('chemical_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="category_id">Category</label>
                <select id="category_id" name="category_id" class="form-select admin-form-control @error('category_id') is-invalid @enderror" required>
                    <option value="">Select category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $chemical->category_id ?? '') == $category->id)>{{ $category->category_name }}</option>
                    @endforeach
                </select>
                @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="laboratory_id">Laboratory</label>
                <select id="laboratory_id" name="laboratory_id" class="form-select admin-form-control @error('laboratory_id') is-invalid @enderror" required>
                    <option value="">Select laboratory</option>
                    @foreach ($laboratories as $laboratory)
                        <option value="{{ $laboratory->id }}" @selected(old('laboratory_id', $chemical->laboratory_id ?? '') == $laboratory->id)>{{ $laboratory->laboratory_name }}</option>
                    @endforeach
                </select>
                @error('laboratory_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="supplier_id">Supplier</label>
                <select id="supplier_id" name="supplier_id" class="form-select admin-form-control @error('supplier_id') is-invalid @enderror">
                    <option value="">No supplier</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected(old('supplier_id', $chemical->supplier_id ?? '') == $supplier->id)>{{ $supplier->supplier_name }}</option>
                    @endforeach
                </select>
                @error('supplier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="quantity">Quantity</label>
                        <input type="number" id="quantity" name="quantity" value="{{ old('quantity', $chemical->quantity ?? 0) }}" class="form-control admin-form-control @error('quantity') is-invalid @enderror" min="0" step="0.01" required>
                        @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="unit">Unit</label>
                        <select id="unit" name="unit" class="form-select admin-form-control @error('unit') is-invalid @enderror" required>
                            <option value="">Select unit</option>
                            @foreach ($unitOptions as $unit)
                                <option value="{{ $unit }}" @selected(old('unit', $chemical->unit ?? '') === $unit)>{{ $unit }}</option>
                            @endforeach
                        </select>
                        @error('unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label" for="manufactured_date">Manufactured date</label>
                <input type="date" id="manufactured_date" name="manufactured_date" value="{{ $manufacturedDate }}" class="form-control admin-form-control @error('manufactured_date') is-invalid @enderror">
                @error('manufactured_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label" for="received_date">Received date</label>
                <input type="date" id="received_date" name="received_date" value="{{ $receivedDate }}" @if ($manufacturedDate) min="{{ $manufacturedDate }}" @endif class="form-control admin-form-control @error('received_date') is-invalid @enderror">
                @error('received_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label" for="expiration_date">Expiration date</label>
                <input type="date" id="expiration_date" name="expiration_date" value="{{ $expirationDate }}" @if ($receivedDate) min="{{ $receivedDate }}" @elseif ($manufacturedDate) min="{{ $manufacturedDate }}" @endif class="form-control admin-form-control @error('expiration_date') is-invalid @enderror">
                @error('expiration_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label" for="hazard_classification">Hazard classification</label>
                <select id="hazard_classification" name="hazard_classification" class="form-select admin-form-control @error('hazard_classification') is-invalid @enderror" required>
                    @foreach ($hazards as $hazard)
                        <option value="{{ $hazard }}" @selected(old('hazard_classification', $chemical->hazard_classification ?? 'Non-Hazardous') === $hazard)>{{ $hazard }}</option>
                    @endforeach
                </select>
                @error('hazard_classification') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label" for="status">Status</label>
                <select id="status" name="status" class="form-select admin-form-control @error('status') is-invalid @enderror" required>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(old('status', $chemical->status ?? 'Available') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="storage_location">Storage location</label>
                <select id="storage_location" name="storage_location" class="form-select admin-form-control @error('storage_location') is-invalid @enderror">
                    <option value="">Select storage location</option>
                    @foreach ($storageLocations as $storageLocation)
                        <option value="{{ $storageLocation }}" @selected(old('storage_location', $chemical->storage_location ?? '') === $storageLocation)>{{ $storageLocation }}</option>
                    @endforeach
                </select>
                @error('storage_location') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <input type="hidden" name="minimum_stock" value="15">

            <div class="col-12">
                <div class="alert alert-info border-0 rounded-4 mb-0">
                    The barcode will be generated automatically when you save this chemical.
                    @if (!empty($chemical?->barcode))
                        <div class="mt-2 fw-semibold text-dark">Current barcode: {{ $chemical->barcode }}</div>
                    @endif
                </div>
            </div>

            <div class="col-12">
                <label class="form-label" for="description">Description</label>
                <textarea id="description" name="description" rows="4" class="form-control admin-form-control @error('description') is-invalid @enderror">{{ old('description', $chemical->description ?? '') }}</textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12">
                <label class="form-label" for="remarks">Remarks</label>
                <textarea id="remarks" name="remarks" rows="4" class="form-control admin-form-control @error('remarks') is-invalid @enderror">{{ old('remarks', $chemical->remarks ?? '') }}</textarea>
                @error('remarks') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12 text-end mt-2">
                <a href="{{ route('coordinator.chemicals.index', request()->query()) }}" class="btn btn-outline-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">{{ isset($chemical) ? 'Save changes' : 'Create chemical' }}</button>
            </div>
        </div>
    </div>
</div>
