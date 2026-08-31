<aside class="admin-sidebar coordinator-sidebar" id="adminSidebar" aria-labelledby="adminSidebarLabel">
    <div class="coordinator-sidebar__header border-bottom">
        <a href="#" class="coordinator-sidebar__brand text-decoration-none" aria-label="LabCentral home">
            <img src="{{ asset('images/pnglogo.png') }}" alt="LabCentral logo"
                class="coordinator-sidebar__brand-mark rounded-3 bg-white border">
            <div class="coordinator-sidebar__brand-copy">
                <div class="fw-semibold text-dark" id="adminSidebarLabel">LabCentral</div>
                <small class="text-secondary">Coordinator Dashboard</small>
            </div>
        </a>

        <button type="button" class="btn btn-light border sidebar-toggle-btn coordinator-sidebar__toggle"
            data-admin-sidebar-toggle aria-controls="adminSidebar" aria-label="Collapse sidebar" aria-expanded="true"
            title="Collapse sidebar">
            <i class="fa-solid fa-chevron-left" data-sidebar-toggle-icon aria-hidden="true"></i>
        </button>
    </div>

    @php
        $isDashboard = request()->routeIs('coordinator.dashboard');

        $isLaboratoriesIndex = request()->routeIs('coordinator.laboratories.index');
        $isLaboratoriesGroup = request()->routeIs('coordinator.laboratories.*');

        $isEquipmentGroup = request()->routeIs('coordinator.equipment.*', 'coordinator.equipment.categories.*');
        $isChemicalGroup = request()->routeIs('coordinator.chemicals.*', 'coordinator.chemical.categories.*');

        $isUserManagementGroup = request()->routeIs('coordinator.users.*', 'coordinator.departments.*');

        $isAnnouncementsIndex = request()->routeIs('coordinator.announcements.index');

        $isReservationsIndex = request()->routeIs('coordinator.reservations.index');
        $isReservationsCalendar = request()->routeIs('coordinator.reservations.calendar');
        $isBorrowIndex = request()->routeIs('coordinator.borrow.index');
        $isBorrowCalendar = request()->routeIs('coordinator.borrow.calendar');
        $isReservationsGroup = request()->routeIs('coordinator.reservations.*');
        $isBorrowGroup = request()->routeIs('coordinator.borrow.*');
        $isRequestGroup = $isReservationsGroup || $isBorrowGroup;

        $isForumIndex = request()->routeIs('coordinator.forum.index');
        $isForumGroup = request()->routeIs('coordinator.forum.*');
        $isFeedbackIndex = request()->routeIs('coordinator.feedback.index');
        $isFeedbackQuestionnaires = request()->routeIs('coordinator.feedback.questionnaires.*');
        $isFeedbackGroup = request()->routeIs('coordinator.feedback.*');

        $pendingReservationRequests = \App\Models\Reservation::where('status', 'Facilitator Approved')->count();
        $pendingBorrowRequests = \App\Models\BorrowTransaction::where('status', 'Facilitator Approved')->count();
        $pendingUserAccountRequests = \App\Models\UserAccountRequest::pending()->count();
    @endphp

    <div class="coordinator-sidebar__body p-0 d-flex flex-column">
        <div class="p-3 p-lg-4 border-bottom">
            <nav class="nav nav-pills flex-column gap-1">
                <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isDashboard ? 'active' : '' }}"
                    href="{{ route('coordinator.dashboard') }}" title="Dashboard">
                    <span class="sidebar-item__icon"><i class="fa-solid fa-gauge-high"></i></span>
                    <span class="sidebar-item__label">Dashboard</span>
                </a>

                <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ request()->routeIs('coordinator.laboratories.*') ? 'active' : '' }}"
                    href="{{ route('coordinator.laboratories.index') }}" title="Laboratory">
                    <span class="sidebar-item__icon">
                        <i class="fa-solid fa-flask-vial"></i>
                    </span>
                    <span class="sidebar-item__label">Laboratory</span>
                </a>

                <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isEquipmentGroup ? 'active' : '' }}"
                    href="{{ route('coordinator.equipment.index') }}" title="Equipment">
                    <span class="sidebar-item__icon"><i class="fa-solid fa-screwdriver-wrench"></i></span>
                    <span class="sidebar-item__label">Equipment</span>
                </a>

                <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isChemicalGroup ? 'active' : '' }}"
                    href="{{ route('coordinator.chemicals.index') }}" title="Chemical">
                    <span class="sidebar-item__icon"><i class="fa-solid fa-flask"></i></span>
                    <span class="sidebar-item__label">Chemical</span>
                </a>

                <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isUserManagementGroup ? 'active' : '' }}"
                    href="{{ route('coordinator.users.index') }}" title="User Management">
                    <span class="d-flex align-items-center gap-2 flex-grow-1">
                        <span class="sidebar-item__icon"><i class="fa-solid fa-users"></i></span>
                        <span class="sidebar-item__label">User Management</span>
                    </span>
                    @if ($pendingUserAccountRequests > 0)
                        <span class="badge rounded-pill text-bg-danger ms-auto">
                            {{ $pendingUserAccountRequests > 99 ? '99+' : $pendingUserAccountRequests }}
                        </span>
                    @endif
                </a>

                <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ request()->routeIs('coordinator.announcements.*') ? 'active' : '' }}"
                    href="{{ route('coordinator.announcements.index') }}" title="Announcements">
                    <span class="sidebar-item__icon"><i class="fa-solid fa-bullhorn"></i></span>
                    <span class="sidebar-item__label">Announcements</span>
                </a>

                <button
                    class="nav-link rounded-3 py-2 px-3 border-0 text-start d-flex align-items-center justify-content-between"
                    type="button" data-bs-toggle="collapse" data-bs-target="#coordinatorRequestMenu"
                    aria-expanded="{{ $isRequestGroup ? 'true' : 'false' }}" aria-controls="coordinatorRequestMenu"
                    title="Requests">
                    <span class="d-flex align-items-center gap-2">
                        <span class="sidebar-item__icon"><i class="fa-solid fa-clipboard-list"></i></span>
                        <span class="sidebar-item__label">Requests</span>
                    </span>
                    <span class="sidebar-item__chevron small" aria-hidden="true"><i
                            class="fa-solid fa-chevron-down"></i></span>
                </button>
                <div class="collapse {{ $isRequestGroup ? 'show' : '' }}" id="coordinatorRequestMenu">
                    <div class="nav nav-pills flex-column gap-1 ms-3 ps-2 border-start">
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isReservationsCalendar ? 'active' : '' }}"
                            href="{{ route('coordinator.reservations.calendar') }}" title="Reservation Calendar">
                            <span class="sidebar-item__icon"><i class="fa-solid fa-calendar-days"></i></span>
                            <span class="sidebar-item__label">Reservation Calendar</span>
                        </a>
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isBorrowCalendar ? 'active' : '' }}"
                            href="{{ route('coordinator.borrow.calendar') }}" title="Borrow Calendar">
                            <span class="sidebar-item__icon"><i class="fa-solid fa-calendar-plus"></i></span>
                            <span class="sidebar-item__label">Borrow Calendar</span>
                        </a>
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isReservationsIndex ? 'active' : '' }}"
                            href="{{ route('coordinator.reservations.index') }}" title="Reservation Requests">
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
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isBorrowIndex ? 'active' : '' }}"
                            href="{{ route('coordinator.borrow.index') }}" title="Borrowing Requests">
                            <span class="d-flex align-items-center gap-2 flex-grow-1">
                                <span class="sidebar-item__icon"><i class="fa-solid fa-boxes-stacked"></i></span>
                                <span class="sidebar-item__label">Borrowing Requests</span>
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
                    type="button" data-bs-toggle="collapse" data-bs-target="#coordinatorFeedbackMenu"
                    aria-expanded="{{ $isFeedbackGroup ? 'true' : 'false' }}" aria-controls="coordinatorFeedbackMenu"
                    title="Feedback">
                    <span class="d-flex align-items-center gap-2">
                        <span class="sidebar-item__icon"><i class="fa-solid fa-message"></i></span>
                        <span class="sidebar-item__label">Feedback</span>
                    </span>
                    <span class="sidebar-item__chevron small" aria-hidden="true"><i
                            class="fa-solid fa-chevron-down"></i></span>
                </button>
                <div class="collapse {{ $isFeedbackGroup ? 'show' : '' }}" id="coordinatorFeedbackMenu">
                    <div class="nav nav-pills flex-column gap-1 ms-3 ps-2 border-start">
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isFeedbackIndex ? 'active' : '' }}"
                            href="{{ route('coordinator.feedback.index') }}" title="Feedback">
                            <span class="sidebar-item__icon"><i class="fa-solid fa-message"></i></span>
                            <span class="sidebar-item__label">Feedback</span>
                        </a>
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isFeedbackQuestionnaires ? 'active' : '' }}"
                            href="{{ route('coordinator.feedback.questionnaires.index') }}" title="Questionnaires">
                            <span class="sidebar-item__icon"><i class="fa-solid fa-clipboard-question"></i></span>
                            <span class="sidebar-item__label">Questionnaire</span>
                        </a>
                    </div>
                </div>

                <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isForumIndex ? 'active' : '' }}"
                    href="{{ route('coordinator.forum.index') }}" title="Forum">
                    <span class="sidebar-item__icon">
                        <i class="fa-solid fa-comments"></i>
                    </span>
                    <span class="sidebar-item__label">Forum</span>
                </a>

                <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2" href="#" title="Reports">
                    <span class="sidebar-item__icon"><i class="fa-solid fa-chart-column"></i></span>
                    <span class="sidebar-item__label">Reports</span>
                </a>
            </nav>
        </div>
    </div>
</aside>
