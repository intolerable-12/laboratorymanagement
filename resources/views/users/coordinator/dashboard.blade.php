@extends('users.coordinator.layouts.app')

@section('title', 'Coordinator Dashboard')
@section('page-title', 'Coordinator Dashboard')
@section('page-subtitle', 'Monitor laboratory operations, inventory, and maintenance at a glance')

@section('content')
    <div class="section-card bg-white mb-4">
        <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
            <div class="hero-copy">
                <div class="social-eyebrow mb-3">Coordinator dashboard</div>
                <h2>Welcome back, {{ auth()->user()?->first_name ?? 'Coordinator' }}</h2>
                <p class="mb-0 text-secondary">Here's a quick overview of the laboratory system today.</p>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('coordinator.borrow.index') }}" class="btn btn-outline-secondary px-4 rounded-pill">Manage Borrow</a>
                <a href="{{ route('coordinator.reservations.index') }}" class="btn btn-primary px-4 rounded-pill">Manage Reservation</a>
            </div>
        </div>
    </div>

    <section class="mb-4">
        <div class="d-flex align-items-center gap-2 mb-3">
            <i class="fa-solid fa-microscope text-primary"></i>
            <i class="fa-solid fa-flask text-success"></i>
            <h3 class="h4 fw-semibold mb-0 text-dark">Equipment &amp; Chemical Overview</h3>
        </div>
        <div class="row g-3 g-xl-4">
            @foreach ($equipmentStats as $metric)
                <div class="col-12 col-sm-6 col-xl-3">
                    @php
                        $metricUrl = match ($metric['label']) {
                            'Damaged equipment' => route('coordinator.equipment.index', ['condition' => 'Damaged']),
                            'Low stock equipment' => route('coordinator.equipment.index', ['low_stock' => '1']),
                            default => null,
                        };
                    @endphp
                    @if ($metricUrl)
                        <a href="{{ $metricUrl }}" class="d-block h-100 text-decoration-none" aria-label="View damaged equipment">
                    @endif
                    <div class="card metric-card border-0 h-100">
                        <div class="card-body p-4">
                            <div class="text-uppercase small fw-semibold text-secondary mb-3">{{ $metric['label'] }}</div>
                            <div class="display-6 fw-semibold mb-2 text-dark">{{ $metric['value'] }}</div>
                            <div class="small text-secondary">{{ $metric['note'] }}</div>
                            @if ($metricUrl)
                                <div class="small fw-semibold text-primary mt-3">See more <i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i></div>
                            @endif
                        </div>
                    </div>
                    @if ($metricUrl)
                        </a>
                    @endif
                </div>
            @endforeach
            @foreach ($chemicalStats as $metric)
                <div class="col-12 col-sm-6 col-xl-4">
                    @php
                        $metricUrl = match ($metric['label']) {
                            'Expired chemicals' => route('coordinator.chemicals.index', ['status' => 'Expired']),
                            'Low stock chemicals' => route('coordinator.chemicals.index', ['low_stock' => '1']),
                            default => null,
                        };
                    @endphp
                    @if ($metricUrl)
                        <a href="{{ $metricUrl }}" class="d-block h-100 text-decoration-none" aria-label="View {{ strtolower($metric['label']) }}">
                    @endif
                    <div class="card metric-card border-0 h-100">
                        <div class="card-body p-4">
                            <div class="text-uppercase small fw-semibold text-secondary mb-3">{{ $metric['label'] }}</div>
                            <div class="display-6 fw-semibold mb-2 text-dark">{{ $metric['value'] }}</div>
                            <div class="small text-secondary">{{ $metric['note'] }}</div>
                            @if ($metricUrl)
                                <div class="small fw-semibold text-primary mt-3">See more <i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i></div>
                            @endif
                        </div>
                    </div>
                    @if ($metricUrl)
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    <section class="row g-3 g-xl-4 mb-4">
        @foreach ($operationalStats as $metric)
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card metric-card border-0 h-100">
                    <div class="card-body p-4">
                        <div class="text-uppercase small fw-semibold text-secondary mb-3">{{ $metric['label'] }}</div>
                        <div class="display-6 fw-semibold mb-2 text-dark">{{ $metric['value'] }}</div>
                        <div class="small text-secondary">{{ $metric['note'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </section>

    @include('partials.laboratory-inventory-summary', ['laboratoryInventory' => $laboratoryInventory])

    <section class="row g-3 mb-4" aria-label="Equipment check-in and checkout">
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('coordinator.checkin.index') }}" class="card metric-card border-0 h-100 text-decoration-none">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <i class="fa-solid fa-arrow-right-to-bracket text-primary fs-4" aria-hidden="true"></i>
                    <div class="flex-grow-1">
                        <div class="small text-uppercase fw-semibold text-secondary">Check in equipment</div>
                        <div class="h4 fw-semibold text-dark mb-0">{{ number_format($checkinCount) }}</div>
                        <div class="small fw-semibold text-primary mt-1">See more <i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i></div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('coordinator.checkout.index') }}" class="card metric-card border-0 h-100 text-decoration-none">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <i class="fa-solid fa-arrow-right-from-bracket text-success fs-4" aria-hidden="true"></i>
                    <div class="flex-grow-1">
                        <div class="small text-uppercase fw-semibold text-secondary">Check out equipment/chemical</div>
                        <div class="h4 fw-semibold text-dark mb-0">{{ number_format($checkoutCount) }}</div>
                        <div class="small fw-semibold text-primary mt-1">See more <i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i></div>
                    </div>
                </div>
            </a>
        </div>
    </section>

    @include('partials.announcement-feed', [
        'announcements' => $announcements,
        'feedTitle' => 'Announcement Management',
        'feedSubtitle' => $publishedAnnouncementCount . ' published announcement(s) out of ' . $announcementCount . ' total.',
        'createUrl' => route('coordinator.announcements.create'),
        'manageUrl' => route('coordinator.announcements.index'),
        'createLabel' => 'Create announcement',
        'manageLabel' => 'Manage all',
    ])

    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card admin-card h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h3 class="h5 fw-semibold mb-1">Laboratory usage statistics</h3>
                            <p class="mb-0 text-secondary">Approved reservations during {{ $usagePeriodLabel }}.</p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    @if ($laboratoryUsage->isNotEmpty())
                        <div class="vstack gap-4">
                            @foreach ($laboratoryUsage as $lab)
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $lab['name'] }}</div>
                                        <div class="small text-secondary">{{ $lab['details'] }}</div>
                                    </div>
                                    <div class="fw-semibold text-dark">{{ $lab['usage'] }}%</div>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $lab['usage'] }}%;" aria-valuenow="{{ $lab['usage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-secondary">No active laboratories are available.</div>
                    @endif
                </div>
            </div>
        </div>

    </div>

    <div class="card admin-card mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <div>
                    <h3 class="h5 fw-semibold mb-1">Inventory traceability</h3>
                    <p class="mb-0 text-secondary">Recent additions, deductions, and returns for equipment and chemicals.</p>
                </div>
                <a href="{{ route('coordinator.inventory-traceability.index') }}" class="btn btn-sm btn-outline-primary">
                    View all <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-dark ps-4">Date</th>
                            <th class="text-dark">Item</th>
                            <th class="text-dark">Movement</th>
                            <th class="text-dark">Quantity</th>
                            <th class="text-dark">Recorded by</th>
                            <th class="text-dark pe-4">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($inventoryLogs as $log)
                            @php
                                $logItem = $log->item;
                                $logName = $logItem
                                    ? ($log->item_type === 'Equipment' ? $logItem->equipment_name : $logItem->chemical_name)
                                    : $log->item_type . ' #' . $log->item_id;
                                $logUnit = $logItem && $log->item_type === 'Chemical' ? $logItem->unit : ($log->item_type === 'Equipment' ? 'unit(s)' : '');
                                $changed = (float) $log->quantity_changed;
                                $actor = $log->performedBy
                                    ? trim(collect([$log->performedBy->first_name, $log->performedBy->middle_name, $log->performedBy->last_name, $log->performedBy->suffix])->filter()->implode(' '))
                                    : 'System';
                                $traceabilityUrl = $log->item_type === 'Equipment'
                                    ? route('coordinator.equipment.traceability', $log->item_id)
                                    : route('coordinator.chemicals.traceability', $log->item_id);
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-semibold text-dark">{{ $log->performed_at?->format('M j, Y') ?? '-' }}</div>
                                    <div class="small text-secondary">{{ $log->performed_at?->format('h:i A') ?? '' }}</div>
                                </td>
                                <td>
                                    <a href="{{ $traceabilityUrl }}" class="text-decoration-none">
                                        <div class="fw-semibold text-dark">{{ $logName }}</div>
                                        <div class="small text-secondary">{{ $log->item_type }}</div>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge text-bg-{{ $log->movementTone() }}">{{ $log->movementLabel() }}</span>
                                    <div class="small text-secondary mt-1">{{ $log->sourceLabel() }}</div>
                                </td>
                                <td class="{{ $changed > 0 ? 'text-success' : ($changed < 0 ? 'text-danger' : 'text-secondary') }}">
                                    <span class="fw-semibold">{{ $changed > 0 ? '+' : '' }}{{ $log->item_type === 'Equipment' ? number_format($changed, 0) : number_format($changed, 2) }}</span> {{ $logUnit }}
                                </td>
                                <td>{{ $actor }}</td>
                                <td class="pe-4 small text-secondary">{{ $log->remarks ?: 'No additional details.' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4">No inventory movements have been recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card admin-card h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <div>
                            <h3 class="h5 fw-semibold mb-1">Recent activity logs</h3>
                            <p class="mb-0 text-secondary">Latest activity recorded by the platform.</p>
                        </div>
                        <a href="{{ route('coordinator.audit-logs.index') }}" class="btn btn-sm btn-outline-primary">
                            View all <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-dark">Time</th>
                                    <th class="text-dark">User</th>
                                    <th class="text-dark">Activity</th>
                                    <th class="text-dark">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($activityLogs as $activity)
                                    <tr>
                                        <td>{{ $activity['time'] }}</td>
                                        <td>{{ $activity['user'] }}</td>
                                        <td>{{ $activity['activity'] }}</td>
                                        <td><span class="badge text-bg-{{ $activity['status_tone'] }}">{{ $activity['status'] }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-secondary py-4">No activity has been recorded yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card admin-card h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h3 class="h5 fw-semibold mb-1">Management modules</h3>
                    <p class="mb-0 text-secondary">Quick access to coordinator functions.</p>
                </div>
                <div class="card-body p-4 pt-3">
                    <div class="row g-3">
                        @foreach ($managementModules as $module)
                            <div class="col-12">
                                <a href="{{ $module['href'] }}" class="text-decoration-none">
                                    <div class="border rounded-4 p-3 bg-white d-flex justify-content-between align-items-center">
                                        <div class="fw-semibold text-dark">{{ $module['label'] }}</div>
                                        <span class="badge text-bg-light border text-secondary">Open</span>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
