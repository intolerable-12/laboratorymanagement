@extends('layouts.app')

@section('title', 'New Feedback')
@section('user-name', 'Student')
@section('user-role', 'Student')

@section('nav-links')
    @include('users.student.partials.nav-links', ['active' => 'feedback'])
@endsection

@section('content')
    <div class="account-page">
        <section class="hero-banner social-hero card border-0 mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
                <div class="hero-copy">
                    <div class="social-eyebrow mb-3">Feedback composer</div>
                    <h2 class="display-6 fw-semibold mb-3 text-dark">Write feedback that reads like a real review</h2>
                    <p class="lead text-secondary mb-0">Use rich text, choose the right visibility, and explain the experience clearly.</p>
                </div>
                <a href="{{ route('student.feedback.index') }}" class="btn btn-outline-secondary px-4 rounded-pill">Back to feedback</a>
            </div>
        </section>

        <div class="row g-4 align-items-start">
            <div class="col-xl-8">
                <div class="card social-card border-0">
                    <div class="card-body p-4 p-xl-5">
                        <form method="POST" action="{{ route('student.feedback.store') }}" class="vstack gap-4">
                            @csrf

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="feedback_type" class="form-label fw-semibold text-dark">Feedback Type</label>
                                    <select id="feedback_type" name="feedback_type" class="form-select social-input @error('feedback_type') is-invalid @enderror">
                                        <option value="">Select type</option>
                                        <option value="Lab Service" @selected(old('feedback_type') === 'Lab Service')>Lab Service</option>
                                        <option value="System" @selected(old('feedback_type', 'System') === 'System')>System</option>
                                    </select>
                                    @error('feedback_type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="laboratory_id" class="form-label fw-semibold text-dark">Laboratory</label>
                                    <select id="laboratory_id" name="laboratory_id" class="form-select social-input @error('laboratory_id') is-invalid @enderror">
                                        <option value="">Select laboratory</option>
                                        @foreach ($laboratories as $laboratory)
                                            <option value="{{ $laboratory->id }}" @selected(old('laboratory_id') == $laboratory->id)>
                                                {{ $laboratory->laboratory_name }} ({{ $laboratory->laboratory_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('laboratory_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="rating" class="form-label fw-semibold text-dark">Rating</label>
                                    <select id="rating" name="rating" class="form-select social-input @error('rating') is-invalid @enderror">
                                        <option value="">Select rating</option>
                                        @for ($rating = 1; $rating <= 5; $rating++)
                                            <option value="{{ $rating }}" @selected((string) old('rating') === (string) $rating)>{{ $rating }}</option>
                                        @endfor
                                    </select>
                                    @error('rating')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="visibility" class="form-label fw-semibold text-dark">Visibility</label>
                                    <select id="visibility" name="visibility" class="form-select social-input @error('visibility') is-invalid @enderror">
                                        <option value="Private" @selected(old('visibility', 'Private') === 'Private')>Private</option>
                                        <option value="Public" @selected(old('visibility') === 'Public')>Public</option>
                                    </select>
                                    @error('visibility')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4 d-flex align-items-end">
                                    <div class="form-check mt-4">
                                        <input type="checkbox" id="is_anonymous" name="is_anonymous" value="1" class="form-check-input" @checked(old('is_anonymous'))>
                                        <label class="form-check-label fw-semibold text-dark" for="is_anonymous">Anonymous</label>
                                    </div>
                                </div>
                            </div>

                            @include('partials.rich-text-editor', [
                                'name' => 'comments',
                                'label' => 'Comments',
                                'id' => 'comments',
                                'placeholder' => 'Describe the experience, what worked, what did not, and any suggestion for improvement.',
                                'hint' => 'Use rich text if you want to format your feedback like a short review.',
                            ])

                            <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
                                <a href="{{ route('student.feedback.index') }}" class="btn btn-outline-secondary px-4 rounded-pill">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4 rounded-pill">Submit feedback</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card social-card border-0 sticky-xl-top social-sticky-card">
                    <div class="card-body p-4 vstack gap-3">
                        <div>
                            <div class="social-eyebrow mb-2">Feedback tips</div>
                            <h3 class="h5 fw-semibold mb-0 text-dark">Make the review useful</h3>
                        </div>

                        <div class="social-promo-item">
                            <div class="fw-semibold text-dark">Rate the experience honestly</div>
                            <div class="small text-secondary">The rating helps readers understand the tone instantly.</div>
                        </div>

                        <div class="social-promo-item">
                            <div class="fw-semibold text-dark">Explain the context</div>
                            <div class="small text-secondary">Mention the laboratory or system behavior that mattered most.</div>
                        </div>

                        <div class="social-promo-item">
                            <div class="fw-semibold text-dark">Choose public or private</div>
                            <div class="small text-secondary">Set visibility based on whether the feedback should be seen by everyone.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection