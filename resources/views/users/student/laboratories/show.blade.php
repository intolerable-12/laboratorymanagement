@extends('users.student.layouts.app')

@section('title', $laboratory->laboratory_name)
@section('page-title', $laboratory->laboratory_name)
@section('page-subtitle', 'Laboratory information and location')

@section('content')
    @php
        $imagePath = $laboratory->primaryImagePath();
        $photoItems = $laboratory->images->map(fn ($image, $index) => [
            'url' => asset('storage/' . $image->image_path),
            'alt' => $laboratory->laboratory_name . ' photo ' . ($index + 1),
        ])->values();

        if ($photoItems->isEmpty() && $imagePath) {
            $photoItems = collect([[
                'url' => asset('storage/' . $imagePath),
                'alt' => $laboratory->laboratory_name,
            ]]);
        }
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <a href="{{ route('student.laboratories.index') }}" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Laboratory directory</a>
        <span class="badge rounded-pill text-bg-info">{{ $laboratory->campus }} campus</span>
    </div>

    <section class="card section-card border-0 overflow-hidden mb-4">
        <div class="row g-0">
            <div class="col-lg-7">
                @if ($imagePath)
                    <button type="button" class="student-laboratory-photo-trigger border-0 p-0 w-100 h-100 bg-transparent" data-laboratory-photo-trigger data-photo-index="0" aria-label="Open laboratory photos">
                        <img src="{{ asset('storage/' . $imagePath) }}" alt="{{ $laboratory->laboratory_name }}" class="w-100 h-100" style="min-height: 360px; max-height: 520px; object-fit: cover;">
                    </button>
                @else
                    <div class="d-flex align-items-center justify-content-center bg-light text-secondary h-100" style="min-height: 360px;">
                        <div class="text-center"><i class="fa-solid fa-flask fs-1 mb-2"></i><div>No laboratory photos yet</div></div>
                    </div>
                @endif
            </div>
            <div class="col-lg-5">
                <div class="card-body p-4 p-xl-5 h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <div class="small text-uppercase text-secondary mb-1">{{ $laboratory->laboratory_code }}</div>
                            <h1 class="h3 fw-semibold text-dark mb-0">{{ $laboratory->laboratory_name }}</h1>
                        </div>
                        <span class="badge text-bg-{{ $laboratory->status === 'Available' ? 'success' : ($laboratory->status === 'Under Maintenance' ? 'warning' : 'secondary') }}">{{ $laboratory->status }}</span>
                    </div>

                    <div class="vstack gap-3 mb-4">
                        <div><div class="small text-secondary">Location</div><div class="fw-semibold text-dark">{{ $laboratory->building ?: 'Building not specified' }}</div></div>
                        <div><div class="small text-secondary">Room</div><div class="fw-semibold text-dark">{{ $laboratory->room_number }}</div></div>
                        <div><div class="small text-secondary">Capacity</div><div class="fw-semibold text-dark">{{ $laboratory->capacity }} people</div></div>
                    </div>

                    <p class="text-secondary mb-4">{{ $laboratory->description ?: 'No additional laboratory description has been provided.' }}</p>

                    <div class="row g-2 mt-auto">
                        <div class="col-6"><a href="{{ route('student.reservations.create', ['laboratory_id' => $laboratory->id]) }}" class="btn btn-primary w-100">Reserve</a></div>
                        <div class="col-6"><a href="{{ route('student.borrow.create', ['laboratory_id' => $laboratory->id]) }}" class="btn btn-outline-primary w-100">Borrow equipment</a></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if ($laboratory->images->count() > 1)
        <section class="section-card mb-4">
            <div class="card-body p-4 p-xl-5">
                <h2 class="h5 fw-semibold text-dark mb-3">Laboratory photos</h2>
                <div class="row g-3">
                    @foreach ($laboratory->images as $image)
                        <div class="col-6 col-md-4 col-xl-3">
                            <button type="button" class="student-laboratory-photo-trigger border-0 p-0 w-100 rounded-3" data-laboratory-photo-trigger data-photo-index="{{ $loop->index }}" aria-label="Open laboratory photo {{ $loop->iteration }}">
                                <img src="{{ asset('storage/' . $image->image_path) }}" alt="{{ $laboratory->laboratory_name }} photo {{ $loop->iteration }}" class="w-100 rounded-3" style="height: 150px; object-fit: cover;">
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="row g-4">
        <div class="col-md-6">
            <div class="card section-card border-0 h-100">
                <div class="card-body p-4">
                    <div class="small text-secondary mb-1">Equipment records</div>
                    <div class="display-6 fw-semibold text-dark">{{ $laboratory->equipment_count }}</div>
                    <a href="{{ route('student.inventory.equipment.index') }}" class="small text-decoration-none">Browse campus equipment <i class="fa-solid fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card section-card border-0 h-100">
                <div class="card-body p-4">
                    <div class="small text-secondary mb-1">Chemical records</div>
                    <div class="display-6 fw-semibold text-dark">{{ $laboratory->chemicals_count }}</div>
                    <a href="{{ route('student.inventory.chemicals.index') }}" class="small text-decoration-none">Browse campus chemicals <i class="fa-solid fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </section>

    @if ($photoItems->isNotEmpty())
        <div class="modal fade student-laboratory-lightbox" id="laboratoryPhotoModal" tabindex="-1" aria-labelledby="laboratoryPhotoModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content border-0 bg-dark text-white">
                    <div class="modal-header border-0">
                        <h2 class="modal-title h5" id="laboratoryPhotoModalLabel">{{ $laboratory->laboratory_name }}</h2>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close photos"></button>
                    </div>
                    <div class="modal-body position-relative p-2 p-md-4">
                        <button type="button" class="student-laboratory-lightbox__nav student-laboratory-lightbox__nav--previous" data-laboratory-photo-previous aria-label="Previous laboratory photo">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <img src="" alt="" class="student-laboratory-lightbox__image rounded-3" data-laboratory-photo-display>
                        <button type="button" class="student-laboratory-lightbox__nav student-laboratory-lightbox__nav--next" data-laboratory-photo-next aria-label="Next laboratory photo">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                        <div class="text-center small text-white-50 mt-3" data-laboratory-photo-counter></div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const photos = @json($photoItems);
                const modalElement = document.getElementById('laboratoryPhotoModal');
                const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                const display = modalElement.querySelector('[data-laboratory-photo-display]');
                const counter = modalElement.querySelector('[data-laboratory-photo-counter]');
                const previous = modalElement.querySelector('[data-laboratory-photo-previous]');
                const next = modalElement.querySelector('[data-laboratory-photo-next]');
                let currentIndex = 0;

                const renderPhoto = function (index) {
                    currentIndex = (index + photos.length) % photos.length;
                    display.src = photos[currentIndex].url;
                    display.alt = photos[currentIndex].alt;
                    counter.textContent = (currentIndex + 1) + ' / ' + photos.length;
                    previous.hidden = photos.length < 2;
                    next.hidden = photos.length < 2;
                };

                document.querySelectorAll('[data-laboratory-photo-trigger]').forEach(function (trigger) {
                    trigger.addEventListener('click', function () {
                        renderPhoto(Number(trigger.dataset.photoIndex || 0));
                        modal.show();
                    });
                });

                previous.addEventListener('click', function () {
                    renderPhoto(currentIndex - 1);
                });

                next.addEventListener('click', function () {
                    renderPhoto(currentIndex + 1);
                });

                document.addEventListener('keydown', function (event) {
                    if (!modalElement.classList.contains('show')) {
                        return;
                    }

                    if (event.key === 'ArrowLeft') {
                        renderPhoto(currentIndex - 1);
                    }

                    if (event.key === 'ArrowRight') {
                        renderPhoto(currentIndex + 1);
                    }
                });
            });
        </script>
    @endif
@endsection
