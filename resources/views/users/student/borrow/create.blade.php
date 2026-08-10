@extends('layouts.app')

@section('title', 'New Borrow Request')
@section('user-name', 'Student')
@section('user-role', 'Student')

@section('nav-links')
    @include('users.student.partials.nav-links', ['active' => 'borrow'])
@endsection

@section('content')
    <div class="account-page">
        <section class="hero-banner card border-0 mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <h2 class="h3 fw-semibold mb-2 text-dark">Create a Borrow Request</h2>
                    <p class="mb-0 text-secondary">Request equipment and chemicals without choosing a laboratory first.</p>
                </div>
                <a href="{{ route('student.borrow.index') }}" class="btn btn-outline-secondary px-4">Back to Requests</a>
            </div>
        </section>

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
                Please review the highlighted fields and selected item quantities.
            </div>
        @endif

        <form method="POST" action="{{ route('student.borrow.store') }}">
            @csrf

            <div class="card section-card border-0 mb-4">
                <div class="card-body p-4 p-xl-5">
                    <h3 class="h4 fw-semibold mb-4 text-dark">Borrow Details</h3>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Borrowed At</label>
                            <input type="datetime-local" name="borrowed_at" value="{{ old('borrowed_at') }}" min="{{ $borrowDateMin }}" data-weekday-only="true" class="form-control @error('borrowed_at') is-invalid @enderror">
                            @error('borrowed_at')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div class="form-text">Weekends are not available for borrow requests.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Due At</label>
                            <input type="datetime-local" name="due_at" value="{{ old('due_at') }}" min="{{ $borrowDateMin }}" data-weekday-only="true" class="form-control @error('due_at') is-invalid @enderror">
                            @error('due_at')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div class="form-text">Due dates must also fall on weekdays.</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark">Remarks</label>
                            <textarea name="remarks" rows="3" class="form-control @error('remarks') is-invalid @enderror" placeholder="Optional notes for the instructor">{{ old('remarks') }}</textarea>
                            @error('remarks')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card section-card border-0 mb-4" data-reservation-tabs>
                <div class="card-body p-4 p-xl-5">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                        <div>
                            <h3 class="h4 fw-semibold mb-1 text-dark">Requested Items</h3>
                            <p class="mb-0 text-secondary">Browse all available items 10 at a time. Switching pages keeps your selections on the form.</p>
                        </div>
                        <div class="d-inline-flex btn-group reservation-tab-switcher" role="tablist" aria-label="Requested items tabs">
                            <button type="button" class="btn btn-outline-primary {{ $activeTab === 'equipment' ? 'active' : '' }}" data-reservation-tab-button data-target="equipment" aria-pressed="{{ $activeTab === 'equipment' ? 'true' : 'false' }}">Equipment</button>
                            <button type="button" class="btn btn-outline-primary {{ $activeTab === 'chemical' ? 'active' : '' }}" data-reservation-tab-button data-target="chemical" aria-pressed="{{ $activeTab === 'chemical' ? 'true' : 'false' }}">Chemical</button>
                        </div>
                    </div>

                    @error('items')<div class="alert alert-danger border-0 rounded-4 mb-4">{{ $message }}</div>@enderror

                    <div class="tab-content">
                        <div class="tab-pane fade {{ $activeTab === 'equipment' ? 'show active' : '' }}" id="equipment-tab" data-reservation-tab-pane="equipment">
                            @include('users.student.borrow.partials.equipment-tab', ['equipmentItems' => $equipmentItems])
                        </div>

                        <div class="tab-pane fade {{ $activeTab === 'chemical' ? 'show active' : '' }}" id="chemical-tab" data-reservation-tab-pane="chemical">
                            @include('users.student.borrow.partials.chemical-tab', ['chemicalItems' => $chemicalItems])
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
                <a href="{{ route('student.borrow.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-4">Submit Borrow Request</button>
            </div>
        </form>
    </div>
@endsection