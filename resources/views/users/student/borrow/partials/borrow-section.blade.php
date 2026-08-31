@php
    $meta = $sectionMeta ?? ['label' => 'Items', 'tone' => 'primary'];
    $entries = collect($entries ?? []);
@endphp

<section class="mb-4 borrow-section" data-section-key="{{ $sectionKey }}">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h4 class="h5 fw-semibold text-dark mb-0">{{ $meta['label'] }}</h4>
        @if ($viewMode === 'card' && $entries->isNotEmpty())
            <div class="btn-group btn-group-sm" role="group" aria-label="Navigate {{ strtolower($meta['label']) }}">
                <button type="button" class="btn btn-outline-secondary" data-borrow-scroll="{{ $sectionKey }}" data-scroll-direction="left" aria-label="Previous {{ strtolower($meta['label']) }}" title="Previous">
                    <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary" data-borrow-scroll="{{ $sectionKey }}" data-scroll-direction="right" aria-label="Next {{ strtolower($meta['label']) }}" title="Next">
                    <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                </button>
            </div>
        @endif
        <span class="badge rounded-pill text-bg-{{ $meta['tone'] }} px-3 py-2">{{ $paginator->total() }}</span>
    </div>

    @if ($entries->isEmpty())
        <div class="empty-state rounded-4 border border-dashed p-4 text-center text-secondary small">
            No {{ strtolower($meta['label']) }} items in this category.
        </div>
    @else
        <div class="borrow-equipment-grid" data-borrow-group="{{ $sectionKey }}" data-borrow-scroll-rail="{{ $sectionKey }}">
            @foreach ($entries as $entry)
                @php
                    $transaction = $entry['transaction'];
                    $item = $entry['item'];
                    $image = $entry['image'];
                    $statusTone = $entry['status_tone'];
                @endphp

                <article class="borrow-equipment-card {{ $viewMode === 'list' ? 'borrow-list-item' : '' }}">
                    @if ($viewMode === 'card')
                        <div class="borrow-equipment-image-wrap">
                            @if ($image)
                                <img src="{{ $image }}" alt="{{ $entry['name'] }}" class="borrow-equipment-image">
                            @else
                                <div class="borrow-equipment-image placeholder-image">
                                    <i class="fa-solid fa-box-open"></i>
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                        <div>
                            <div class="text-uppercase small text-secondary mb-1">{{ $entry['item_type'] }}</div>
                            <h5 class="fw-semibold text-dark mb-0">{{ $entry['name'] }}</h5>
                        </div>
                        <span class="badge text-bg-{{ $statusTone }} rounded-pill">{{ $entry['status'] }}</span>
                    </div>

                    <div class="borrow-equipment-meta">
                        <span><i class="fa-solid fa-location-dot"></i> {{ $entry['lab_name'] }}</span>
                        <span><i class="fa-solid fa-box"></i> Qty {{ $entry['quantity'] }}</span>
                    </div>

                    <div class="borrow-equipment-meta">
                        <span><i class="fa-solid fa-calendar-plus"></i> {{ $entry['borrowed_at'] }}</span>
                        <span><i class="fa-solid fa-clock"></i> Due {{ $entry['due_at'] }}</span>
                    </div>

                    @if ($entry['status'] === 'Returned')
                        <div class="borrow-equipment-meta text-success">
                            <span><i class="fa-solid fa-circle-check"></i> Returned {{ $entry['returned_at'] }}</span>
                        </div>
                    @elseif ($transaction->remarks)
                        <div class="small text-secondary mt-2">{{ Str::limit($transaction->remarks, 80) }}</div>
                    @endif

                    <div class="mt-3 d-flex justify-content-between align-items-center border-top pt-3">
                        <span class="small text-secondary fw-medium">{{ $entry['borrow_no'] }}</span>
                        <a href="{{ route('student.borrow.show', $transaction) }}" class="btn btn-sm btn-outline-primary">View</a>
                    </div>
                </article>
            @endforeach
        </div>

        @if ($viewMode === 'list' && $paginator->hasPages())
            <div class="mt-4 d-flex justify-content-center flex-wrap gap-2 borrow-pagination">
                @php
                    $currentPage = $paginator->currentPage();
                    $lastPage = $paginator->lastPage();
                    $pageNumbers = range(1, $lastPage);
                @endphp

                @foreach ($pageNumbers as $pageNumber)
                    <a href="#"
                       class="btn btn-sm {{ $pageNumber === $currentPage ? 'btn-primary' : 'btn-outline-secondary' }}"
                       data-borrow-page="{{ $sectionKey }}"
                       data-page-number="{{ $pageNumber }}">
                        {{ $pageNumber }}
                    </a>
                @endforeach
            </div>
        @endif
    @endif
</section>
