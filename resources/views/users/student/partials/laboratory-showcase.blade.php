<section class="section-card bg-white mb-4">
    <div class="card-body p-4 p-xl-5">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4">
            <div>
                <h3 class="h4 fw-semibold mb-1 text-dark">Your campus laboratories</h3>
                <p class="small text-secondary mb-0">Get familiar with the spaces where you will reserve or borrow resources.</p>
            </div>
            <a href="{{ route('student.laboratories.index') }}" class="btn btn-outline-primary btn-sm">View directory</a>
        </div>

        <div class="row g-3">
            @forelse ($laboratories as $laboratory)
                @php($imagePath = $laboratory->primaryImagePath())
                <div class="col-12 col-md-4">
                    <a href="{{ route('student.laboratories.show', $laboratory) }}" class="student-laboratory-photo-trigger text-decoration-none">
                        <div class="border rounded-4 overflow-hidden h-100">
                            @if ($imagePath)
                                <img src="{{ asset('storage/' . $imagePath) }}" alt="{{ $laboratory->laboratory_name }}" class="student-laboratory-photo-trigger__image w-100" style="height: 150px; object-fit: cover;">
                            @else
                                <div class="d-flex align-items-center justify-content-center bg-light text-secondary" style="height: 150px;"><i class="fa-solid fa-flask fs-2"></i></div>
                            @endif
                            <div class="p-3">
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <div class="fw-semibold text-dark">{{ $laboratory->laboratory_name }}</div>
                                    <span class="badge rounded-pill text-bg-info">{{ $laboratory->campus }} campus</span>
                                </div>
                                <div class="small text-secondary mt-1"><i class="fa-solid fa-location-dot me-1"></i>{{ $laboratory->building ?: 'Location not specified' }} · Room {{ $laboratory->room_number }}</div>
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-12 text-secondary small">No laboratories have been added for your campus yet.</div>
            @endforelse
        </div>
    </div>
</section>
