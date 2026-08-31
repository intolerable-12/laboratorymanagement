@extends('users.student.layouts.app')

@section('title', 'My Borrowing Equipment')
@section('page-title', 'My Borrow')
@section('page-title', 'Check In Items')
@section('user-name', 'Student')
@section('user-role', 'Student')

@section('content')
    <div class="account-page">
        <section class="hero-banner card border-0 mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <h2 class="h3 fw-semibold mb-2 text-dark">My Borrowing Equipment</h2>
                    <p class="mb-0 text-secondary">Review your active, pending, and returned laboratory items in one place.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('student.borrow.create') }}" class="btn btn-primary px-4">New Borrow Request</a>
                </div>
            </div>
        </section>

        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
        @endif

        <div class="card section-card border-0 mb-4">
            <div class="card-body p-4 p-xl-5">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                    <div>
                        <h3 class="h4 fw-semibold mb-1 text-dark">Borrowing Overview</h3>
                        <p class="mb-0 text-secondary">Switch between compact card view and a more detailed list.</p>
                    </div>

                    <div class="btn-group" role="group" aria-label="Borrow view switcher">
                        <button type="button" class="btn btn-sm {{ $viewMode === 'card' ? 'btn-primary' : 'btn-outline-secondary' }}" data-view-toggle="card">Card View</button>
                        <button type="button" class="btn btn-sm {{ $viewMode === 'list' ? 'btn-primary' : 'btn-outline-secondary' }}" data-view-toggle="list">List View</button>
                    </div>
                </div>

                <div data-borrow-view data-view-mode="{{ $viewMode }}">
                    @php
                        $sectionLabels = [
                            'current' => ['label' => 'Current Borrowing', 'tone' => 'primary'],
                            'pending' => ['label' => 'Pending', 'tone' => 'warning'],
                            'returned' => ['label' => 'Returned', 'tone' => 'success'],
                        ];
                    @endphp

                    @foreach ($sectionLabels as $key => $meta)
                        @php
                            $entries = $sectionData[$key]->items() ?? collect();
                            $paginator = $sectionData[$key] ?? null;
                        @endphp

                        @include('users.student.borrow.partials.borrow-section', [
                            'sectionKey' => $key,
                            'sectionMeta' => $meta,
                            'entries' => $entries,
                            'paginator' => $paginator,
                        ])
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <style>
        .borrow-equipment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1rem;
            min-width: max-content;
        }

        [data-borrow-view][data-view-mode="card"] .borrow-section {
            overflow: hidden;
            padding-bottom: 0.5rem;
        }

        [data-borrow-view][data-view-mode="card"] .borrow-equipment-grid {
            width: 100%;
            min-width: 0;
            display: grid;
            grid-auto-flow: column;
            grid-auto-columns: minmax(240px, 1fr);
            gap: 1rem;
            overflow-x: auto;
            padding-bottom: 0.35rem;
        }

        [data-borrow-view][data-view-mode="card"] .borrow-equipment-card {
            min-width: 240px;
        }

        [data-borrow-view][data-view-mode="list"] .borrow-equipment-grid {
            display: flex;
            flex-direction: column;
            gap: 0.55rem;
            min-width: 100%;
        }

        [data-borrow-view][data-view-mode="list"] .borrow-list-item {
            min-width: 0;
            padding: 0.65rem 0.85rem;
            border-radius: 0.75rem;
            box-shadow: none;
        }

        [data-borrow-view][data-view-mode="list"] .borrow-list-item:hover {
            transform: none;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
        }

        [data-borrow-view][data-view-mode="list"] .borrow-list-item .borrow-equipment-meta {
            gap: 0.25rem 0.75rem;
            margin-top: 0.25rem;
            font-size: 0.76rem;
        }

        [data-borrow-view][data-view-mode="list"] .borrow-list-item .border-top {
            margin-top: 0.55rem !important;
            padding-top: 0.55rem !important;
        }

        .borrow-equipment-card {
            background: #fff;
            border: 1px solid rgba(148, 163, 184, 0.24);
            border-radius: 1rem;
            padding: 0.9rem;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .borrow-equipment-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 30px rgba(15, 23, 42, 0.08);
        }

        .borrow-equipment-image-wrap {
            width: 100%;
            height: 130px;
            overflow: hidden;
            border-radius: 0.85rem;
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            margin-bottom: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .borrow-equipment-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .placeholder-image {
            color: #64748b;
            font-size: 2.2rem;
        }

        .borrow-equipment-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem 0.9rem;
            color: #475569;
            font-size: 0.82rem;
            margin-top: 0.55rem;
        }

        .borrow-equipment-meta i {
            width: 1rem;
            color: #64748b;
        }

        .empty-state {
            background: rgba(248, 250, 252, 0.8);
            border-style: dashed;
        }

        @media (max-width: 576px) {
            .borrow-equipment-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const root = document.querySelector('[data-borrow-view]');
            if (!root) {
                return;
            }

            const updateButtons = () => {
                document.querySelectorAll('[data-view-toggle]').forEach((item) => {
                    const isActive = item.dataset.viewToggle === root.dataset.viewMode;
                    item.classList.toggle('btn-primary', isActive);
                    item.classList.toggle('btn-outline-secondary', !isActive);
                });
            };

            document.querySelectorAll('[data-view-toggle]').forEach((button) => {
                button.addEventListener('click', function () {
                    const mode = this.dataset.viewToggle;
                    const url = new URL(window.location.href);
                    url.searchParams.set('view', mode);
                    url.searchParams.delete('section');
                    ['current', 'pending', 'returned'].forEach((section) => url.searchParams.delete('page[' + section + ']'));
                    window.location.assign(url);
                });
            });

            const scrollRails = root.querySelectorAll('[data-borrow-scroll-rail]');
            const updateScrollButtons = () => {
                scrollRails.forEach((rail) => {
                    const section = rail.dataset.borrowScrollRail;
                    const maxScrollLeft = Math.max(rail.scrollWidth - rail.clientWidth, 0);
                    const previousButton = root.querySelector('[data-borrow-scroll="' + section + '"][data-scroll-direction="left"]');
                    const nextButton = root.querySelector('[data-borrow-scroll="' + section + '"][data-scroll-direction="right"]');

                    if (previousButton) {
                        previousButton.disabled = maxScrollLeft <= 1 || rail.scrollLeft <= 1;
                    }

                    if (nextButton) {
                        nextButton.disabled = maxScrollLeft <= 1 || rail.scrollLeft >= maxScrollLeft - 1;
                    }
                });
            };

            root.addEventListener('click', function (event) {
                const button = event.target.closest('[data-borrow-scroll]');
                if (!button) {
                    return;
                }

                const section = button.dataset.borrowScroll;
                const rail = root.querySelector('[data-borrow-scroll-rail="' + section + '"]');
                if (!rail) {
                    return;
                }

                const distance = Math.max(Math.round(rail.clientWidth * 0.82), 240);
                rail.scrollBy({
                    left: button.dataset.scrollDirection === 'left' ? -distance : distance,
                    behavior: 'smooth',
                });
            });

            scrollRails.forEach((rail) => rail.addEventListener('scroll', updateScrollButtons, { passive: true }));
            window.addEventListener('resize', updateScrollButtons);
            updateScrollButtons();

            root.addEventListener('click', function (event) {
                const link = event.target.closest('[data-borrow-page]');
                if (!link) {
                    return;
                }

                event.preventDefault();

                const section = link.dataset.borrowPage;
                const currentPage = Number(link.dataset.pageNumber || 1);
                const url = new URL(window.location.href);
                url.searchParams.set('view', root.dataset.viewMode || 'card');
                url.searchParams.set('section', section);
                url.searchParams.set('page[' + section + ']', String(currentPage));

                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                })
                .then((response) => response.text())
                .then((html) => {
                    const temp = document.createElement('div');
                    temp.innerHTML = html;
                    const sectionNode = temp.querySelector('[data-section-key="' + section + '"]');
                    const target = root.querySelector('[data-section-key="' + section + '"]');

                    if (sectionNode && target) {
                        target.outerHTML = sectionNode.outerHTML;
                        window.history.replaceState({}, '', url);
                    }
                })
                .catch(() => window.location.reload());
            });

            updateButtons();
        });
    </script>
@endsection
