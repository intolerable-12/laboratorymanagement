@extends('users.coordinator.layouts.app')

@section('title', 'Announcement Management')
@section('page-title', 'Announcement Management')
@section('page-subtitle', 'Create, update, and remove announcements shown on the role dashboards')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h2 class="h4 fw-semibold mb-1 text-dark">Announcements</h2>
            <p class="mb-0 text-secondary">Manage who sees each announcement and keep the dashboard feeds current.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('coordinator.announcements.create') }}" class="btn btn-primary px-4">
                <i class="fa-solid fa-plus me-2"></i>Create announcement
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
    @endif

    <div class="row g-3 g-xl-4 mb-4">
        <div class="col-12 col-sm-4">
            <div class="card admin-card border-0 h-100">
                <div class="card-body p-4">
                    <div class="small text-uppercase fw-semibold text-secondary mb-2">All</div>
                    <div class="display-6 fw-semibold text-dark mb-1">{{ $totals['all'] }}</div>
                    <div class="small text-secondary">Total announcements</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card admin-card border-0 h-100">
                <div class="card-body p-4">
                    <div class="small text-uppercase fw-semibold text-secondary mb-2">Published</div>
                    <div class="display-6 fw-semibold text-dark mb-1">{{ $totals['published'] }}</div>
                    <div class="small text-secondary">Visible in dashboards</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card admin-card border-0 h-100">
                <div class="card-body p-4">
                    <div class="small text-uppercase fw-semibold text-secondary mb-2">Drafts</div>
                    <div class="display-6 fw-semibold text-dark mb-1">{{ $totals['draft'] }}</div>
                    <div class="small text-secondary">Saved but hidden</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card admin-card border-0 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('coordinator.announcements.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-dark" for="audience">Audience</label>
                    <select id="audience" name="audience" class="form-select admin-form-control">
                        <option value="all">All audiences</option>
                        @foreach ($audienceOptions as $value => $label)
                            <option value="{{ $value }}" @selected($audience === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-dark" for="status">Status</label>
                    <select id="status" name="status" class="form-select admin-form-control">
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 text-md-end">
                    <button type="submit" class="btn btn-outline-secondary px-4">
                        <i class="fa-solid fa-filter me-2"></i>Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card admin-card border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Announcement</th>
                            <th scope="col">Audience</th>
                            <th scope="col">Schedule</th>
                            <th scope="col">Status</th>
                            <th scope="col">Updated</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($announcements as $announcement)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-start gap-3">
                                        @if ($announcement->coverImageUrl())
                                            <img src="{{ $announcement->coverImageUrl() }}" alt="{{ $announcement->title }}" class="rounded-3 border announcement-list-thumb">
                                        @endif
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $announcement->title }}</div>
                                            <div class="small text-secondary">{{ $announcement->excerpt(120) }}</div>
                                            <div class="small text-secondary mt-1">By {{ trim(($announcement->postedBy?->first_name ?? '') . ' ' . ($announcement->postedBy?->last_name ?? '')) ?: 'System' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach ($announcement->audienceLabels() as $audienceLabel)
                                            <span class="badge text-bg-primary-subtle text-primary-emphasis border">{{ $audienceLabel }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $announcement->start_date?->format('M d, Y') ?? 'Anytime' }}</div>
                                    <div class="small text-secondary">{{ $announcement->end_date?->format('M d, Y') ?? 'Open ended' }}</div>
                                </td>
                                <td>
                                    <span class="badge text-bg-{{ $announcement->is_published ? 'success' : 'secondary' }}">{{ $announcement->is_published ? 'Published' : 'Draft' }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $announcement->updated_at?->format('M d, Y') ?? '—' }}</div>
                                    <div class="small text-secondary">{{ $announcement->updated_at?->format('h:i A') ?? '' }}</div>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group" aria-label="Announcement actions">
                                        <a href="{{ route('coordinator.announcements.show', $announcement) }}" class="btn btn-outline-secondary" title="View">
                                            <i class="fa-solid fa-eye me-1"></i>
                                        </a>
                                        <a href="{{ route('coordinator.announcements.edit', $announcement) }}" class="btn btn-outline-primary" title="Edit">
                                            <i class="fa-solid fa-pen-to-square me-1"></i>
                                        </a>
                                    </div>
                                    <form action="{{ route('coordinator.announcements.destroy', $announcement) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger ms-1" title="Delete" onclick="return confirm('Delete this announcement?');">
                                            <i class="fa-solid fa-trash-can me-1"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-5">No announcements found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $announcements->links('pagination::bootstrap-5') }}
    </div>
@endsection