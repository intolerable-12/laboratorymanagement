@extends(request()->routeIs('facilitator.*') ? 'users.facilitator.layouts.app' : 'users.coordinator.layouts.app')

@php
    $routePrefix = request()->routeIs('facilitator.*') ? 'facilitator' : 'coordinator';
@endphp

@section('title', 'Edit Laboratory')
@section('page-title', 'Edit Laboratory')
@section('page-subtitle', 'Update laboratory details and manage its photo gallery')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">Please review the highlighted fields and try again.</div>
    @endif

    <div class="section-card">
        <div class="card-body p-4 p-xl-5">
            <form method="POST" action="{{ route($routePrefix.'.laboratories.update', $laboratory) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('users.coordinator.laboratory._form', [
                    'laboratory' => $laboratory,
                    'formAction' => route($routePrefix.'.laboratories.update', $laboratory),
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

                <form method="POST" action="{{ route($routePrefix.'.laboratories.images.destroy', $laboratory) }}" onsubmit="return confirm('Delete the selected laboratory photos?');">
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
