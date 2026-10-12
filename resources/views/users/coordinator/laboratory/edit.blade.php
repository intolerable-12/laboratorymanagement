@extends('users.coordinator.layouts.app')

@section('title', 'Edit Laboratory')
@section('page-title', 'Edit Laboratory')
@section('page-subtitle', 'Update laboratory details and manage its photo gallery')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">Please review the highlighted fields and try again.</div>
    @endif

    <div class="hero-banner laboratory-hero rounded-4 p-4 p-lg-5 mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-white border mb-3">
                    <span class="badge rounded-pill text-bg-primary">Edit laboratory</span>
                    <span class="badge rounded-pill text-bg-info">{{ $laboratory->campus }} campus</span>
                    <span class="small text-secondary">Update room details, status, or photos</span>
                </div>

                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="equipment-hero__accent"><span class="h4 mb-0 fw-semibold">L</span></div>
                    <div>
                        <h2 class="display-6 fw-semibold text-dark mb-2">Edit {{ $laboratory->laboratory_name }}.</h2>
                        <p class="lead text-secondary mb-0" style="max-width: 58rem;">Add more photos from the form, then remove one or several existing photos below.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="section-card p-4 h-100">
                    <div class="small text-uppercase text-secondary mb-2">Editing tips</div>
                    <ul class="mb-0 text-secondary ps-3">
                        <li>Add one or several photos without replacing existing photos.</li>
                        <li>Select one photo to delete only that photo.</li>
                        <li>Review status before saving.</li>
                        <li>Capacity is stored as a number.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="section-card">
        <div class="card-body p-4 p-xl-5">
            <form method="POST" action="{{ route('coordinator.laboratories.update', $laboratory) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('users.coordinator.laboratory._form', [
                    'laboratory' => $laboratory,
                    'formAction' => route('coordinator.laboratories.update', $laboratory),
                    'formMethod' => 'PUT',
                ])
            </form>
        </div>
    </div>

    @if ($laboratory->images->isNotEmpty())
        <div class="section-card mt-4">
            <div class="card-body p-4 p-xl-5">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
                    <div>
                        <h3 class="h5 fw-semibold mb-1 text-dark">Manage laboratory photos</h3>
                        <p class="small text-secondary mb-0">Select one or several photos to remove from this laboratory.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('coordinator.laboratories.images.destroy', $laboratory) }}" onsubmit="return confirm('Delete the selected laboratory photos?');">
                    @csrf
                    @method('DELETE')
                    <div class="row g-3">
                        @foreach ($laboratory->images as $image)
                            <div class="col-6 col-md-4 col-xl-3">
                                <label class="d-block border rounded-4 overflow-hidden h-100 bg-light">
                                    <img src="{{ asset('storage/' . $image->image_path) }}" alt="{{ $laboratory->laboratory_name }} photo {{ $loop->iteration }}" class="w-100" style="height: 160px; object-fit: cover;">
                                    <span class="d-flex align-items-center gap-2 p-3 small text-secondary">
                                        <input type="checkbox" name="image_ids[]" value="{{ $image->id }}" class="form-check-input mt-0">
                                        Photo {{ $loop->iteration }}
                                    </span>
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="fa-solid fa-trash-can me-1"></i> Delete selected photos
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection
