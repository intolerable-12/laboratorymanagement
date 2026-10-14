<aside class="admin-sidebar coordinator-sidebar" id="adminSidebar" aria-labelledby="facilitatorSidebarLabel">
    <div class="coordinator-sidebar__header border-bottom">
        <a href="{{ route('facilitator.dashboard') }}" class="coordinator-sidebar__brand text-decoration-none" aria-label="LabCentral home">
            <img src="{{ asset('images/pnglogo.png') }}" alt="LabCentral logo"
                class="coordinator-sidebar__brand-mark rounded-3 bg-white border">
            <div class="coordinator-sidebar__brand-copy">
                <div class="fw-semibold text-dark" id="facilitatorSidebarLabel">LabCentral</div>
                <small class="text-secondary">Laboratory In-charge Dashboard</small>
            </div>
        </a>

        <button type="button" class="btn btn-light border sidebar-toggle-btn coordinator-sidebar__toggle"
            data-admin-sidebar-toggle aria-controls="adminSidebar" aria-label="Collapse sidebar" aria-expanded="true"
            title="Collapse sidebar">
            <i class="fa-solid fa-chevron-left" data-sidebar-toggle-icon aria-hidden="true"></i>
        </button>
    </div>

    @php
        $sidebarUser = auth()->user()?->loadMissing(['role', 'department']);
        $sidebarRole = $sidebarUser?->role?->role_name ?? 'Laboratory In-charge';
        $facilitatorCampus = $sidebarUser?->campusName();

        $isDashboard = request()->routeIs('facilitator.dashboard');
        $isLaboratoriesGroup = request()->routeIs('facilitator.laboratories.*');
        $isEquipmentGroup = request()->routeIs('facilitator.equipment.*', 'facilitator.equipment.categories.*')
            && ! request()->routeIs('facilitator.equipment.traceability*');
        $isChemicalGroup = request()->routeIs('facilitator.chemicals.*', 'facilitator.chemical.categories.*')
            && ! request()->routeIs('facilitator.chemicals.traceability*');
        $isInventoryAlerts = request()->routeIs('facilitator.inventory-alerts.*');
        $isSupplierGroup = request()->routeIs('facilitator.suppliers.*');
        $isTraceability = request()->routeIs('facilitator.inventory-traceability.*', 'facilitator.equipment.traceability*', 'facilitator.chemicals.traceability*');
        $isInventoryGroup = $isEquipmentGroup || $isChemicalGroup || $isInventoryAlerts || $isSupplierGroup || $isTraceability;
        $isReservationsGroup = request()->routeIs('facilitator.reservations.*');
        $isReservationsIndex = request()->routeIs('facilitator.reservations.index');
        $isReservationsCalendar = request()->routeIs('facilitator.reservations.calendar');
        $isBorrowGroup = request()->routeIs('facilitator.borrow.*');
        $isBorrowIndex = request()->routeIs('facilitator.borrow.index');
        $isBorrowCalendar = request()->routeIs('facilitator.borrow.calendar');
        $isCheckoutGroup = request()->routeIs('facilitator.checkout.*');
        $isCheckinGroup = request()->routeIs('facilitator.checkin.*');
        $isTransactionHistory = request()->routeIs('facilitator.transaction-history.*');
        $isRequestHistory = request()->routeIs('facilitator.requesthistory.*');
        $isForumGroup = request()->routeIs('facilitator.forum.*');
        $isAnnouncementsGroup = request()->routeIs('facilitator.announcements.*');
        $isFeedbackIndex = request()->routeIs('facilitator.feedback.index');
        $isFeedbackQuestionnaires = request()->routeIs('facilitator.feedback.questionnaires.*');
        $isFeedbackGroup = request()->routeIs('facilitator.feedback.*');
        $isCommunicationGroup = $isFeedbackGroup || $isForumGroup || $isAnnouncementsGroup;
        $isAcademicPeriods = request()->routeIs('facilitator.academic-periods.*');
        $isReports = request()->routeIs('facilitator.reports.*');
        $isMyAccount = request()->routeIs('facilitator.myaccount');

        $isReservationRequestActive = request()->routeIs(
            'facilitator.reservations.index',
            'facilitator.reservations.show',
            'facilitator.reservations.edit',
            'facilitator.reservations.create'
        );

        $isBorrowRequestActive = request()->routeIs(
            'facilitator.borrow.index',
            'facilitator.borrow.show',
            'facilitator.borrow.create'
        );

        $isFacilitatorReservationGroup = $isReservationsCalendar || $isReservationRequestActive;
        $isFacilitatorBorrowGroup = $isBorrowCalendar || $isBorrowRequestActive;
        $isFacilitatorScanGroup = $isCheckoutGroup || $isCheckinGroup || $isTransactionHistory;

        $pendingReservationRequests = \App\Models\Reservation::query()
            ->forCampus($facilitatorCampus)
            ->where(function ($query) {
                $query->where('status', 'Instructor Approved')
                    ->orWhere(function ($query) {
                        $query->where('status', 'Pending')
                            ->whereHas('user.role', fn ($roleQuery) => $roleQuery->where('role_name', 'Instructor'));
                    });
            })
            ->count();
        $pendingBorrowRequests = \App\Models\BorrowTransaction::query()
            ->forCampus($facilitatorCampus)
            ->where(function ($query) {
                $query->where('status', 'Instructor Approved')
                    ->orWhere(function ($query) {
                        $query->where('status', 'Pending')
                            ->whereHas('borrower.role', fn ($roleQuery) => $roleQuery->where('role_name', 'Instructor'));
                    });
            })
            ->count();
        $pendingUserAccountRequests = \App\Models\UserAccountRequest::pending()->count();
    @endphp

    <div class="coordinator-sidebar__body p-0 d-flex flex-column">
        <div class="p-3 p-lg-4 border-bottom">
            <nav class="nav nav-pills flex-column gap-1">
                <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isDashboard ? 'active' : '' }}"
                    href="{{ route('facilitator.dashboard') }}" title="Dashboard">
                    <span class="sidebar-item__icon"><i class="fa-solid fa-gauge-high"></i></span>
                    <span class="sidebar-item__label">Dashboard</span>
                </a>

                <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isLaboratoriesGroup ? 'active' : '' }}"
                    href="{{ route('facilitator.laboratories.index') }}" title="Laboratory">
                    <span class="sidebar-item__icon"><i class="fa-solid fa-flask-vial"></i></span>
                    <span class="sidebar-item__label">Laboratory</span>
                </a>

                <button
                    class="nav-link rounded-3 py-2 px-3 border-0 text-start d-flex align-items-center justify-content-between"
                    type="button" data-bs-toggle="collapse" data-bs-target="#facilitatorInventoryMenu"
                    aria-expanded="{{ $isInventoryGroup ? 'true' : 'false' }}" aria-controls="facilitatorInventoryMenu"
                    title="Inventory">
                    <span class="d-flex align-items-center gap-2">
                        <span class="sidebar-item__icon"><i class="fa-solid fa-layer-group"></i></span>
                        <span class="sidebar-item__label">Inventory</span>
                    </span>
                    <span class="sidebar-item__chevron small" aria-hidden="true"><i class="fa-solid fa-chevron-down"></i></span>
                </button>
                <div class="collapse {{ $isInventoryGroup ? 'show' : '' }}" id="facilitatorInventoryMenu">
                    <div class="nav nav-pills flex-column gap-1 ms-3 ps-2 border-start">
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isEquipmentGroup ? 'active' : '' }}"
                            href="{{ route('facilitator.equipment.index') }}" title="Equipment">
                            <span class="sidebar-item__icon"><i class="fa-solid fa-microscope"></i></span>
                            <span class="sidebar-item__label">Equipment</span>
                        </a>
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isChemicalGroup ? 'active' : '' }}"
                            href="{{ route('facilitator.chemicals.index') }}" title="Chemical">
                            <span class="sidebar-item__icon"><i class="fa-solid fa-flask"></i></span>
                            <span class="sidebar-item__label">Chemical</span>
                        </a>
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isInventoryAlerts ? 'active' : '' }}"
                            href="{{ route('facilitator.inventory-alerts.index') }}" title="Inventory Alert Settings">
                            <span class="sidebar-item__icon"><i class="fa-solid fa-bell"></i></span>
                            <span class="sidebar-item__label">Inventory Alert Settings</span>
                        </a>
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isTraceability ? 'active' : '' }}"
                            href="{{ route('facilitator.inventory-traceability.index') }}" title="Traceability">
                            <span class="sidebar-item__icon"><i class="fa-solid fa-clock-rotate-left"></i></span>
                            <span class="sidebar-item__label">Traceability</span>
                        </a>
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isSupplierGroup ? 'active' : '' }}"
                            href="{{ route('facilitator.suppliers.index') }}" title="Suppliers">
                            <span class="sidebar-item__icon"><i class="fa-solid fa-truck-field"></i></span>
                            <span class="sidebar-item__label">Suppliers</span>
                        </a>
                    </div>
                </div>

                <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isRequestHistory ? 'active' : '' }}"
                    href="{{ route('facilitator.requesthistory.index') }}" title="Request History">
                    <span class="sidebar-item__icon"><i class="fa-solid fa-clock-rotate-left"></i></span>
                    <span class="sidebar-item__label">Request History</span>
                </a>

                <button
                    class="nav-link rounded-3 py-2 px-3 border-0 text-start d-flex align-items-center justify-content-between"
                    type="button" data-bs-toggle="collapse" data-bs-target="#facilitatorReservationRequestMenu"
                    aria-expanded="{{ $isFacilitatorReservationGroup ? 'true' : 'false' }}" aria-controls="facilitatorReservationRequestMenu"
                    title="Reservation Requests">
                    <span class="d-flex align-items-center gap-2">
                        <span class="sidebar-item__icon"><i class="fa-solid fa-clipboard-list"></i></span>
                        <span class="sidebar-item__label">Reservation</span>
                    </span>
                    <span class="sidebar-item__chevron small" aria-hidden="true"><i
                            class="fa-solid fa-chevron-down"></i></span>
                </button>
                <div class="collapse {{ $isFacilitatorReservationGroup ? 'show' : '' }}" id="facilitatorReservationRequestMenu">
                    <div class="nav nav-pills flex-column gap-1 ms-3 ps-2 border-start">
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isReservationsCalendar ? 'active' : '' }}"
                            href="{{ route('facilitator.reservations.calendar') }}" title="Reservation Calendar">
                            <span class="sidebar-item__icon"><i class="fa-solid fa-calendar-days"></i></span>
                            <span class="sidebar-item__label">Reservation Calendar</span>
                        </a>
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isReservationRequestActive ? 'active' : '' }}"
                            href="{{ route('facilitator.reservations.index') }}" title="Reservation Requests">
                            <span class="d-flex align-items-center gap-2 flex-grow-1">
                                <span class="sidebar-item__icon"><i class="fa-solid fa-calendar-check"></i></span>
                                <span class="sidebar-item__label">Reservation Requests</span>
                            </span>
                            @if ($pendingReservationRequests > 0)
                                <span class="badge rounded-pill text-bg-danger ms-auto">
                                    {{ $pendingReservationRequests > 99 ? '99+' : $pendingReservationRequests }}
                                </span>
                            @endif
                        </a>
                    </div>
                </div>

                <button
                    class="nav-link rounded-3 py-2 px-3 border-0 text-start d-flex align-items-center justify-content-between"
                    type="button" data-bs-toggle="collapse" data-bs-target="#facilitatorBorrowRequestMenu"
                    aria-expanded="{{ $isFacilitatorBorrowGroup ? 'true' : 'false' }}" aria-controls="facilitatorBorrowRequestMenu"
                    title="Borrow Requests">
                    <span class="d-flex align-items-center gap-2">
                        <span class="sidebar-item__icon"><i class="fa-solid fa-clipboard-list"></i></span>
                        <span class="sidebar-item__label">Borrow</span>
                    </span>
                    <span class="sidebar-item__chevron small" aria-hidden="true"><i
                            class="fa-solid fa-chevron-down"></i></span>
                </button>
                <div class="collapse {{ $isFacilitatorBorrowGroup ? 'show' : '' }}" id="facilitatorBorrowRequestMenu">
                    <div class="nav nav-pills flex-column gap-1 ms-3 ps-2 border-start">
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isBorrowCalendar ? 'active' : '' }}"
                            href="{{ route('facilitator.borrow.calendar') }}" title="Borrow Calendar">
                            <span class="sidebar-item__icon"><i class="fa-solid fa-calendar-days"></i></span>
                            <span class="sidebar-item__label">Borrow Calendar</span>
                        </a>
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isBorrowRequestActive ? 'active' : '' }}"
                            href="{{ route('facilitator.borrow.index') }}" title="Borrow Requests">
                            <span class="d-flex align-items-center gap-2 flex-grow-1">
                                <span class="sidebar-item__icon"><i class="fa-solid fa-boxes-stacked"></i></span>
                                <span class="sidebar-item__label">Borrow Requests</span>
                            </span>
                            @if ($pendingBorrowRequests > 0)
                                <span class="badge rounded-pill text-bg-danger ms-auto">
                                    {{ $pendingBorrowRequests > 99 ? '99+' : $pendingBorrowRequests }}
                                </span>
                            @endif
                        </a>
                    </div>
                </div>

                <button
                    class="nav-link rounded-3 py-2 px-3 border-0 text-start d-flex align-items-center justify-content-between"
                    type="button" data-bs-toggle="collapse" data-bs-target="#facilitatorScannerMenu"
                    aria-expanded="{{ $isFacilitatorScanGroup ? 'true' : 'false' }}" aria-controls="facilitatorScannerMenu"
                    title="Requests">
                    <span class="d-flex align-items-center gap-2">
                        <span class="sidebar-item__icon"><i class="fa-solid fa-barcode"></i></span>
                        <span class="sidebar-item__label">Scan Barcode</span>
                    </span>
                    <span class="sidebar-item__chevron small" aria-hidden="true"><i
                            class="fa-solid fa-chevron-down"></i></span>
                </button>
                <div class="collapse {{ $isFacilitatorScanGroup ? 'show' : '' }}" id="facilitatorScannerMenu">
                    <div class="nav nav-pills flex-column gap-1 ms-3 ps-2 border-start">
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isCheckoutGroup ? 'active' : '' }}"
                            href="{{ route('facilitator.checkout.index') }}" title="Checkout Items">
                            <span class="sidebar-item__icon"><i class="fa-solid fa-barcode"></i></span>
                            <span class="sidebar-item__label">Checkout Items</span>
                        </a>
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isCheckinGroup ? 'active' : '' }}"
                            href="{{ route('facilitator.checkin.index') }}" title="Check In Items">
                            <span class="d-flex align-items-center gap-2 flex-grow-1">
                                <span class="sidebar-item__icon"><i class="fa-solid fa-rotate-left"></i></span>
                                <span class="sidebar-item__label">Check In Items</span>
                            </span>
                        </a>
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isTransactionHistory ? 'active' : '' }}"
                            href="{{ route('facilitator.transaction-history.index') }}" title="Transaction History">
                            <span class="sidebar-item__icon"><i class="fa-solid fa-clock-rotate-left"></i></span>
                            <span class="sidebar-item__label">Transaction History</span>
                        </a>
                    </div>
                </div>

                <button
                    class="nav-link rounded-3 py-2 px-3 border-0 text-start d-flex align-items-center justify-content-between"
                    type="button" data-bs-toggle="collapse" data-bs-target="#facilitatorCommunicationMenu"
                    aria-expanded="{{ $isCommunicationGroup ? 'true' : 'false' }}"
                    aria-controls="facilitatorCommunicationMenu" title="Communication">
                    <span class="d-flex align-items-center gap-2">
                        <span class="sidebar-item__icon"><i class="fa-solid fa-message"></i></span>
                        <span class="sidebar-item__label">Communication</span>
                    </span>
                    <span class="sidebar-item__chevron small" aria-hidden="true"><i class="fa-solid fa-chevron-down"></i></span>
                </button>
                <div class="collapse {{ $isCommunicationGroup ? 'show' : '' }}" id="facilitatorCommunicationMenu">
                    <div class="nav nav-pills flex-column gap-1 ms-3 ps-2 border-start">
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isAnnouncementsGroup ? 'active' : '' }}"
                            href="{{ route('facilitator.announcements.index') }}" title="Announcements">
                            <span class="sidebar-item__icon"><i class="fa-solid fa-bullhorn"></i></span>
                            <span class="sidebar-item__label">Announcements</span>
                        </a>
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isForumGroup ? 'active' : '' }}"
                            href="{{ route('facilitator.forum.index') }}" title="Forum">
                            <span class="sidebar-item__icon"><i class="fa-solid fa-comments"></i></span>
                            <span class="sidebar-item__label">Forum</span>
                        </a>
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isFeedbackIndex ? 'active' : '' }}"
                            href="{{ route('facilitator.feedback.index') }}" title="Feedback">
                            <span class="sidebar-item__icon"><i class="fa-solid fa-message"></i></span>
                            <span class="sidebar-item__label">Feedback</span>
                        </a>
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isFeedbackQuestionnaires ? 'active' : '' }}"
                            href="{{ route('facilitator.feedback.questionnaires.index') }}" title="Questionnaires">
                            <span class="sidebar-item__icon"><i class="fa-solid fa-clipboard-question"></i></span>
                            <span class="sidebar-item__label">Questionnaire</span>
                        </a>
                    </div>
                </div>

                <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isAcademicPeriods ? 'active' : '' }}"
                    href="{{ route('facilitator.academic-periods.index') }}" title="School Year & Semester">
                    <span class="sidebar-item__icon"><i class="fa-solid fa-calendar-days"></i></span>
                    <span class="sidebar-item__label">School Year & Semester</span>
                </a>

                <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isReports ? 'active' : '' }}"
                    href="{{ route('facilitator.reports.index') }}" title="Reports">
                    <span class="sidebar-item__icon"><i class="fa-solid fa-chart-column"></i></span>
                    <span class="sidebar-item__label">Reports</span>
                </a>

                <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isMyAccount ? 'active' : '' }}"
                    href="{{ route('facilitator.myaccount') }}" title="My Account">
                    <span class="sidebar-item__icon"><i class="fa-solid fa-user"></i></span>
                    <span class="sidebar-item__label">My Account</span>
                </a>
            </nav>
        </div>
    </div>
</aside>
