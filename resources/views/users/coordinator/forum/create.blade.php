@extends('users.coordinator.layouts.app')

@section('title', 'New Forum Post')
@section('page-title', 'New Forum Post')

@section('content')
    <div class="hero-banner social-hero rounded-4 p-4 p-lg-5 mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <div class="social-eyebrow mb-3">Forum composer</div>
                <h2 class="display-6 fw-semibold text-dark mb-3">Start a discussion</h2>
                <p class="lead text-secondary mb-0">Create a post and share an update, question, or laboratory discussion.</p>
            </div>

            <a href="{{ route('coordinator.forum.index') }}" class="btn btn-outline-secondary px-4 rounded-pill">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to feed
            </a>
        </div>
    </div>

    <div class="row g-4 align-items-start">
        <div class="col-xl-8">
            <div class="card social-card border-0">
                <div class="card-body p-4 p-xl-5">
                    <form method="POST" action="{{ route('coordinator.forum.store') }}" class="vstack gap-4">
                        @csrf

                        <div>
                            <label for="title" class="form-label fw-semibold text-dark">Title</label>
                            <input type="text" id="title" name="title" value="{{ old('title') }}" class="form-control social-input @error('title') is-invalid @enderror" placeholder="What do you want to discuss?" required>
                            @error('title')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label for="category" class="form-label fw-semibold text-dark">Category</label>
                            <select id="category" name="category" class="form-select social-input @error('category') is-invalid @enderror" required>
                                <option value="">Select category</option>
                                @foreach ($categories as $option)
                                    <option value="{{ $option }}" @selected(old('category', 'General') === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                            @error('category')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        @include('partials.rich-text-editor', [
                            'name' => 'content',
                            'label' => 'Content',
                            'required' => true,
                            'id' => 'content',
                            'placeholder' => 'Share your question, explanation, or update.',
                            'hint' => 'Rich text is supported.',
                        ])

                        <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
                            <a href="{{ route('coordinator.forum.index') }}" class="btn btn-outline-secondary px-4 rounded-pill">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4 rounded-pill">Publish post</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card social-card border-0 sticky-xl-top social-sticky-card">
                <div class="card-body p-4 vstack gap-3">
                    <div>
                        <div class="social-eyebrow mb-2">Before you post</div>
                        <h3 class="h5 fw-semibold mb-0 text-dark">Make the feed useful for everyone</h3>
                    </div>

                    <div class="social-promo-item">
                        <div class="fw-semibold text-dark">Be specific</div>
                        <div class="small text-secondary">Include enough detail so readers can answer quickly.</div>
                    </div>

                    <div class="social-promo-item">
                        <div class="fw-semibold text-dark">Choose the right category</div>
                        <div class="small text-secondary">A clear category helps others find the discussion faster.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
