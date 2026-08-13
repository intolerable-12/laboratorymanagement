@extends('layouts.app')

@section('title', 'New Forum Post')
@section('user-name', 'Laboratory In-charge')
@section('user-role', 'Laboratory In-charge')

@section('nav-links')
    @include('users.facilitator.partials.nav-links', ['active' => 'forum'])
@endsection

@section('content')
    <div class="account-page">
        <section class="hero-banner social-hero card border-0 mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
                <div class="hero-copy">
                    <div class="social-eyebrow mb-3">Forum composer</div>
                    <h2 class="display-6 fw-semibold mb-3 text-dark">Start a discussion people actually want to read</h2>
                    <p class="lead text-secondary mb-0">Pick a category, write with rich text, and publish into the feed.</p>
                </div>
                <a href="{{ route('facilitator.forum.index') }}" class="btn btn-outline-secondary px-4 rounded-pill">Back to feed</a>
            </div>
        </section>

        <div class="row g-4 align-items-start">
            <div class="col-xl-8">
                <div class="card social-card border-0">
                    <div class="card-body p-4 p-xl-5">
                        <form method="POST" action="{{ route('facilitator.forum.store') }}" class="vstack gap-4">
                            @csrf

                            <div>
                                <label for="title" class="form-label fw-semibold text-dark">Title</label>
                                <input type="text" id="title" name="title" value="{{ old('title') }}" class="form-control social-input @error('title') is-invalid @enderror" placeholder="What do you want to discuss?">
                                @error('title')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="category" class="form-label fw-semibold text-dark">Category</label>
                                    <select id="category" name="category" class="form-select social-input @error('category') is-invalid @enderror">
                                        <option value="">Select category</option>
                                        @foreach ($categories as $option)
                                            <option value="{{ $option }}" @selected(old('category', 'General') === $option)>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                    @error('category')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <div class="social-tips card border-0 h-100">
                                        <div class="card-body p-3">
                                            <div class="small text-secondary mb-1">Posting tip</div>
                                            <div class="fw-semibold text-dark">Use the category to help others find the right discussion faster.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @include('partials.rich-text-editor', [
                                'name' => 'content',
                                'label' => 'Content',
                                'id' => 'content',
                                'placeholder' => 'Share your question, explanation, or update. You can format text, add lists, and link resources.',
                                'hint' => 'Rich text is supported.',
                            ])

                            <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
                                <a href="{{ route('facilitator.forum.index') }}" class="btn btn-outline-secondary px-4 rounded-pill">Cancel</a>
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
                            <h3 class="h5 fw-semibold mb-0 text-dark">Make the feed better for everyone</h3>
                        </div>

                        <div class="social-promo-item">
                            <div class="fw-semibold text-dark">Be specific</div>
                            <div class="small text-secondary">Include enough detail so readers can answer quickly.</div>
                        </div>

                        <div class="social-promo-item">
                            <div class="fw-semibold text-dark">Choose the right category</div>
                            <div class="small text-secondary">Questions, equipment, and laboratory posts all feel different.</div>
                        </div>

                        <div class="social-promo-item">
                            <div class="fw-semibold text-dark">Use formatting wisely</div>
                            <div class="small text-secondary">Bold key details, add bullet points, and keep the tone readable.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
