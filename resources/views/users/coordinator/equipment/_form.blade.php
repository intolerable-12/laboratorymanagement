@php
    $conditions = ['Excellent', 'Good', 'Fair', 'Damaged', 'Under Repair', 'Condemned'];
    $statuses = ['Available', 'Borrowed', 'Reserved', 'Unavailable', 'Maintenance'];
    $imageUrl = !empty($equipment?->image) ? asset('storage/' . $equipment->image) : null;
@endphp

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="equipment-preview-card h-100">
            @if ($imageUrl)
                <img src="{{ $imageUrl }}" alt="{{ $equipment->equipment_name ?? 'Equipment image' }}" class="equipment-preview rounded-4 mb-3">
            @else
                <div class="equipment-image-placeholder rounded-4 d-flex flex-column align-items-center justify-content-center text-center px-4 py-5 mb-3">
                    <div class="equipment-image-placeholder__icon">+</div>
                    <div class="fw-semibold">No image uploaded</div>
                    <div class="small text-secondary">Add a photo to make the inventory easier to scan.</div>
                </div>
            @endif

            <label class="form-label" for="image">Equipment image</label>
            <input type="file" id="image" name="image" class="form-control admin-form-control @error('image') is-invalid @enderror" accept="image/*">
            <div class="form-text">JPEG, PNG, or WEBP up to 4 MB.</div>
            @error('image') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="col-lg-8">
        <div class="row g-3">
            <div class="col-12">
                <div class="alert alert-info border-0 rounded-4 mb-0">
                    The equipment code is generated automatically when you save this record and cannot be edited later.
                    @if (!empty($equipment?->equipment_code))
                        <div class="mt-2 fw-semibold text-dark">Current equipment code: {{ $equipment->equipment_code }}</div>
                    @endif
                </div>
            </div>

            <div class="col-md-12">
                <label class="form-label" for="equipment_name">Equipment name</label>
                <input type="text" id="equipment_name" name="equipment_name" value="{{ old('equipment_name', $equipment->equipment_name ?? '') }}" class="form-control admin-form-control @error('equipment_name') is-invalid @enderror" required>
                @error('equipment_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="category_id">Category</label>
                <select id="category_id" name="category_id" class="form-select admin-form-control @error('category_id') is-invalid @enderror" required>
                    <option value="">Select category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $equipment->category_id ?? '') == $category->id)>{{ $category->category_name }}</option>
                    @endforeach
                </select>
                @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="laboratory_id">Laboratory</label>
                <select id="laboratory_id" name="laboratory_id" class="form-select admin-form-control @error('laboratory_id') is-invalid @enderror" required>
                    <option value="">Select laboratory</option>
                    @foreach ($laboratories as $laboratory)
                        <option value="{{ $laboratory->id }}" @selected(old('laboratory_id', $equipment->laboratory_id ?? '') == $laboratory->id)>{{ $laboratory->laboratory_name }}</option>
                    @endforeach
                </select>
                @error('laboratory_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="supplier_id">Supplier</label>
                <select id="supplier_id" name="supplier_id" class="form-select admin-form-control @error('supplier_id') is-invalid @enderror">
                    <option value="">No supplier</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected(old('supplier_id', $equipment->supplier_id ?? '') == $supplier->id)>{{ $supplier->supplier_name }}</option>
                    @endforeach
                </select>
                @error('supplier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="brand">Brand</label>
                <input type="text" id="brand" name="brand" value="{{ old('brand', $equipment->brand ?? '') }}" class="form-control admin-form-control @error('brand') is-invalid @enderror" maxlength="150">
                @error('brand') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="model">Model</label>
                <input type="text" id="model" name="model" value="{{ old('model', $equipment->model ?? '') }}" class="form-control admin-form-control @error('model') is-invalid @enderror" maxlength="150">
                @error('model') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="serial_number">Serial number</label>
                <input type="text" id="serial_number" name="serial_number" value="{{ old('serial_number', $equipment->serial_number ?? '') }}" class="form-control admin-form-control @error('serial_number') is-invalid @enderror" maxlength="150">
                @error('serial_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="purchase_date">Purchase date</label>
                <input type="date" id="purchase_date" name="purchase_date" value="{{ old('purchase_date', optional($equipment?->purchase_date)->format('Y-m-d')) }}" class="form-control admin-form-control @error('purchase_date') is-invalid @enderror">
                @error('purchase_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label" for="unit_cost">Unit cost</label>
                <input type="number" id="unit_cost" name="unit_cost" value="{{ old('unit_cost', $equipment->unit_cost ?? '') }}" class="form-control admin-form-control @error('unit_cost') is-invalid @enderror" min="0" step="0.01">
                @error('unit_cost') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label" for="quantity">Quantity</label>
                <input type="number" id="quantity" name="quantity" value="{{ old('quantity', $equipment->quantity ?? 0) }}" class="form-control admin-form-control @error('quantity') is-invalid @enderror" min="0" required>
                @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label" for="available_quantity">Available quantity</label>
                <input type="number" id="available_quantity" name="available_quantity" value="{{ old('available_quantity', $equipment->available_quantity ?? 0) }}" class="form-control admin-form-control @error('available_quantity') is-invalid @enderror" min="0" required>
                @error('available_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label" for="minimum_stock">Minimum stock</label>
                <input type="number" id="minimum_stock" name="minimum_stock" value="{{ old('minimum_stock', $equipment->minimum_stock ?? 1) }}" class="form-control admin-form-control @error('minimum_stock') is-invalid @enderror" min="0" required>
                @error('minimum_stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label" for="condition">Condition</label>
                <select id="condition" name="condition" class="form-select admin-form-control @error('condition') is-invalid @enderror" required>
                    @foreach ($conditions as $condition)
                        <option value="{{ $condition }}" @selected(old('condition', $equipment->condition ?? 'Good') === $condition)>{{ $condition }}</option>
                    @endforeach
                </select>
                @error('condition') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label" for="status">Status</label>
                <select id="status" name="status" class="form-select admin-form-control @error('status') is-invalid @enderror" required>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(old('status', $equipment->status ?? 'Available') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="storage_location">Storage location</label>
                <input type="text" id="storage_location" name="storage_location" value="{{ old('storage_location', $equipment->storage_location ?? '') }}" class="form-control admin-form-control @error('storage_location') is-invalid @enderror">
                @error('storage_location') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12">
                <div class="alert alert-info border-0 rounded-4 mb-0">
                    The barcode will be generated automatically when you save this equipment.
                    @if (!empty($equipment?->barcode))
                        <div class="mt-2 fw-semibold text-dark">Current barcode: {{ $equipment->barcode }}</div>
                    @endif
                </div>
            </div>

            <div class="col-12">
                <label class="form-label" for="description">Description</label>
                <textarea id="description" name="description" rows="4" class="form-control admin-form-control @error('description') is-invalid @enderror">{{ old('description', $equipment->description ?? '') }}</textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12">
                <label class="form-label" for="remarks">Remarks</label>
                <textarea id="remarks" name="remarks" rows="4" class="form-control admin-form-control @error('remarks') is-invalid @enderror">{{ old('remarks', $equipment->remarks ?? '') }}</textarea>
                @error('remarks') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12 text-end mt-2">
                <a href="{{ route('coordinator.equipment.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">{{ isset($equipment) ? 'Save changes' : 'Create equipment' }}</button>
            </div>
        </div>
    </div>
</div>