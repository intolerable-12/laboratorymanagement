<div class="feedback-image-upload" data-feedback-image-upload data-max-images="3">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-end gap-2 mb-2">
        <div>
            <label for="feedback-images" class="form-label fw-semibold text-dark mb-1">Add images <span class="text-secondary fw-normal">(optional)</span></label>
            <div class="small text-secondary">Attach up to 3 JPG, PNG, or WEBP images (5 MB each) to help the reviewer understand the issue.</div>
        </div>
        <span class="feedback-image-upload__count" data-feedback-image-count>0 / 3 selected</span>
    </div>

    <input type="file" id="feedback-images" name="images[]" class="form-control social-input @error('images') is-invalid @enderror @error('images.*') is-invalid @enderror" accept="image/jpeg,image/png,image/webp" multiple data-feedback-image-input>

    @error('images')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
    @error('images.*')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror

    <div class="feedback-image-upload__preview mt-3" data-feedback-image-preview aria-live="polite" hidden></div>
</div>
