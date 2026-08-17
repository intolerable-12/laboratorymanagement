@php
    $selectedAudiences = collect(old('audiences', $announcement->audiences ?? []))
        ->filter()
        ->values()
        ->all();

    $currentImages = $announcement->imageUrls();
@endphp

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 bg-light h-100">
            <div class="card-body p-4">
                <h3 class="h5 fw-semibold text-dark mb-3">Images</h3>

                @if (!empty($currentImages))
                    <div class="announcement-image-grid mb-3">
                        @foreach ($currentImages as $image)
                            <div class="announcement-image-grid__item">
                                <img src="{{ $image }}" alt="Announcement image" class="img-fluid rounded-4 border">
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="announcement-image-empty rounded-4 border bg-white text-center text-secondary p-4 mb-3">
                        No images uploaded yet.
                    </div>
                @endif

                <label class="form-label fw-semibold text-dark" for="images">Attach images</label>
                <input type="file" id="images" name="images[]" class="form-control admin-form-control @error('images') is-invalid @enderror" accept="image/*" multiple>
                <div class="form-text">JPEG, PNG, and WEBP files up to 4 MB each. Uploading new files adds them to the gallery.</div>
                @error('images') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 bg-white">
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold text-dark" for="title">Title</label>
                        <input type="text" id="title" name="title" value="{{ old('title', $announcement->title ?? '') }}" class="form-control admin-form-control @error('title') is-invalid @enderror" required maxlength="255">
                        @error('title') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold text-dark mb-2">Display on</label>
                        <div class="row g-2">
                            @foreach ($audienceOptions as $value => $label)
                                <div class="col-12 col-md-4">
                                    <div class="form-check announcement-target-option">
                                        <input class="form-check-input" type="checkbox" name="audiences[]" id="audience_{{ $value }}" value="{{ $value }}" @checked(in_array($value, $selectedAudiences, true))>
                                        <label class="form-check-label fw-semibold text-dark" for="audience_{{ $value }}">{{ $label }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="form-text">Choose one destination or select multiple audiences.</div>
                        @error('audiences') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        @error('audiences.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark" for="start_date">Start date</label>
                        <input type="date" id="start_date" name="start_date" value="{{ old('start_date', optional($announcement->start_date)->format('Y-m-d')) }}" class="form-control admin-form-control @error('start_date') is-invalid @enderror">
                        @error('start_date') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark" for="end_date">End date</label>
                        <input type="date" id="end_date" name="end_date" value="{{ old('end_date', optional($announcement->end_date)->format('Y-m-d')) }}" class="form-control admin-form-control @error('end_date') is-invalid @enderror">
                        @error('end_date') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        @include('partials.rich-text-editor', [
                            'name' => 'content',
                            'label' => 'Announcement content',
                            'value' => $announcement->content ?? '',
                            'placeholder' => 'Write the announcement details here...',
                            'hint' => 'Use the editor to format important notices and links.',
                        ])
                    </div>

                    <div class="col-12">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="send_email" value="0">
                                    <input class="form-check-input" type="checkbox" id="send_email" name="send_email" value="1" @checked(old('send_email', $announcement->send_email ?? true))>
                                    <label class="form-check-label fw-semibold text-dark" for="send_email">Send email notification</label>
                                </div>
                                <div class="form-text">Notify selected users by email when the announcement is published.</div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="is_published" value="0">
                                    <input class="form-check-input" type="checkbox" id="is_published" name="is_published" value="1" @checked(old('is_published', $announcement->is_published ?? true))>
                                    <label class="form-check-label fw-semibold text-dark" for="is_published">Publish immediately</label>
                                </div>
                                <div class="form-text">Uncheck this to save the announcement as a draft.</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 text-end pt-2">
                        <a href="{{ route('coordinator.announcements.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">{{ isset($announcement?->id) ? 'Save changes' : 'Create announcement' }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
