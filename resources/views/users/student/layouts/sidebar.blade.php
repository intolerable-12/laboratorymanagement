<aside class="admin-sidebar coordinator-sidebar" id="adminSidebar" aria-labelledby="studentSidebarLabel">
    <div class="coordinator-sidebar__header border-bottom">
        <a href="{{ route('student.dashboard') }}" class="coordinator-sidebar__brand text-decoration-none" aria-label="LabCentral home">
            <img src="{{ asset('images/pnglogo.png') }}" alt="LabCentral logo"
                class="coordinator-sidebar__brand-mark rounded-3 bg-white border">
            <div class="coordinator-sidebar__brand-copy">
                <div class="fw-semibold text-dark" id="studentSidebarLabel">LabCentral</div>
                <small class="text-secondary">Student Dashboard</small>
            </div>
        </a>

        <button type="button" class="btn btn-light border sidebar-toggle-btn coordinator-sidebar__toggle"
            data-admin-sidebar-toggle aria-controls="adminSidebar" aria-label="Collapse sidebar" aria-expanded="true"
            title="Collapse sidebar">
            <i class="fa-solid fa-chevron-left" data-sidebar-toggle-icon aria-hidden="true"></i>
        </button>
    </div>

    @php
        $sidebarUser = auth()->user()?->loadMissing('role');
        $sidebarRole = $sidebarUser?->role?->role_name ?? 'Student';

        $isDashboard = request()->routeIs('student.dashboard');

        $isInventoryIndex = request()->routeIs('student.inventory.index');
        $isInventoryGroup = request()->routeIs('student.inventory.*');

        $isReservationsGroup = request()->routeIs('student.reservations.*');
        $isReservationsIndex = request()->routeIs('student.reservations.index');
        $isReservationCalendar = request()->routeIs('student.reservations.calendar');

        $isReservationRequestActive = request()->routeIs(
            'student.reservations.index',
            'student.reservations.show',
            'student.reservations.edit',
            'student.reservations.create'
        );

        $isStudentReservationGroup = $isReservationCalendar || $isReservationRequestActive;

        $isEquipmentGroup = request()->routeIs('student.inventory.equipment.*');
        $isChemicalGroup = request()->routeIs('student.inventory.chemicals.*');

        $isStudentInventoryGroup = $isEquipmentGroup || $isChemicalGroup;

        $isBorrowGroup = request()->routeIs('student.borrow.*');
        $isBorrowIndex = request()->routeIs('student.borrow.index');
        $isBorrowCalendar = request()->routeIs('student.borrow.calendar');

        $isBorrowRequestActive = request()->routeIs(
            'student.borrow.index',
            'student.borrow.show',
            'student.borrow.create'
        );

        $isStudentBorrowGroup = $isBorrowCalendar || $isBorrowRequestActive;

        $isForumIndex = request()->routeIs('student.forum.index');
        $isForumGroup = request()->routeIs('student.forum.*');

        $isFeedbackIndex = request()->routeIs('student.feedback.index');
        $isFeedbackCreate = request()->routeIs('student.feedback.create');
        $isQuestionnairesIndex = request()->routeIs('student.feedback.questionnaires.index');
        $isQuestionnairesGroup = request()->routeIs('student.feedback.questionnaires.*');
        $isFeedbackCreate = request()->routeIs('student.feedback.create');

        $isFeedbackActiveGroup = request()->routeIs(
            'student.feedback.index',
            'student.feedback.show',
            'student.feedback.create'
        );

        $isCommunicationGroup = $isFeedbackActiveGroup || $isQuestionnairesGroup || $isForumGroup;

        $isMyAccount = request()->routeIs('student.myaccount');
    @endphp

    <div class="coordinator-sidebar__body p-0 d-flex flex-column">
        <div class="p-3 p-lg-4 border-bottom">
            <nav class="nav nav-pills flex-column gap-1">
                <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isDashboard ? 'active' : '' }}"
                    href="{{ route('student.dashboard') }}" title="Dashboard">
                    <span class="sidebar-item__icon"><i class="fa-solid fa-gauge-high"></i></span>
                    <span class="sidebar-item__label">Dashboard</span>
                </a>

                <button
                    class="nav-link rounded-3 py-2 px-3 border-0 text-start d-flex align-items-center justify-content-between"
                    type="button" data-bs-toggle="collapse" data-bs-target="#studentInventoryMenu"
                    aria-expanded="{{ $isStudentInventoryGroup ? 'true' : 'false' }}" aria-controls="studentInventoryMenu"
                    title="Inventory">
                    <span class="d-flex align-items-center gap-2">
                        <span class="sidebar-item__icon"><i class="fa-solid fa-layer-group"></i></span>
                        <span class="sidebar-item__label">Inventory</span>
                    </span>
                    <span class="sidebar-item__chevron small" aria-hidden="true"><i
                            class="fa-solid fa-chevron-down"></i></span>
                </button>
                <div class="collapse {{ $isStudentInventoryGroup ? 'show' : '' }}" id="studentInventoryMenu">
                    <div class="nav nav-pills flex-column gap-1 ms-3 ps-2 border-start">
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isEquipmentGroup ? 'active' : '' }}"
                            href="{{ route('student.inventory.equipment.index') }}" title="Equipment">
                            <span class="sidebar-item__icon"><i class="fa-solid fa-microscope"></i></span>
                            <span class="sidebar-item__label">Equipment</span>
                        </a>
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isChemicalGroup ? 'active' : '' }}"
                            href="{{ route('student.inventory.chemicals.index') }}" title="Chemical">
                            <span class="d-flex align-items-center gap-2 flex-grow-1">
                                <span class="sidebar-item__icon"><i class="fa-solid fa-vial"></i></span>
                                <span class="sidebar-item__label">Chemical</span>
                            </span>
                        </a>
                    </div>
                </div>

                <button
                    class="nav-link rounded-3 py-2 px-3 border-0 text-start d-flex align-items-center justify-content-between"
                    type="button" data-bs-toggle="collapse" data-bs-target="#studentReservationMenu"
                    aria-expanded="{{ $isStudentReservationGroup ? 'true' : 'false' }}" aria-controls="studentReservationMenu"
                    title="Requests">
                    <span class="d-flex align-items-center gap-2">
                        <span class="sidebar-item__icon"><i class="fa-solid fa-clipboard-list"></i></span>
                        <span class="sidebar-item__label">Reservation</span>
                    </span>
                    <span class="sidebar-item__chevron small" aria-hidden="true"><i
                            class="fa-solid fa-chevron-down"></i></span>
                </button>
                <div class="collapse {{ $isStudentReservationGroup ? 'show' : '' }}" id="studentReservationMenu">
                    <div class="nav nav-pills flex-column gap-1 ms-3 ps-2 border-start">
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isReservationCalendar ? 'active' : '' }}"
                            href="{{ route('student.reservations.calendar') }}" title="Reservation Calendar">
                            <span class="sidebar-item__icon"><i class="fa-solid fa-calendar-days"></i></span>
                            <span class="sidebar-item__label">Reservation Calendar</span>
                        </a>
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isReservationRequestActive ? 'active' : '' }}"
                            href="{{ route('student.reservations.index') }}" title="Reservations">
                            <span class="d-flex align-items-center gap-2 flex-grow-1">
                                <span class="sidebar-item__icon"><i class="fa-solid fa-calendar-check"></i></span>
                                <span class="sidebar-item__label">Reservations Request</span>
                            </span>
                        </a>
                    </div>
                </div>

                <button
                    class="nav-link rounded-3 py-2 px-3 border-0 text-start d-flex align-items-center justify-content-between"
                    type="button" data-bs-toggle="collapse" data-bs-target="#studentBorrowMenu"
                    aria-expanded="{{ $isStudentBorrowGroup ? 'true' : 'false' }}" aria-controls="studentBorrowMenu"
                    title="Requests">
                    <span class="d-flex align-items-center gap-2">
                        <span class="sidebar-item__icon"><i class="fa-solid fa-clipboard-list"></i></span>
                        <span class="sidebar-item__label">Borrow</span>
                    </span>
                    <span class="sidebar-item__chevron small" aria-hidden="true"><i
                            class="fa-solid fa-chevron-down"></i></span>
                </button>
                <div class="collapse {{ $isStudentBorrowGroup ? 'show' : '' }}" id="studentBorrowMenu">
                    <div class="nav nav-pills flex-column gap-1 ms-3 ps-2 border-start">
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isBorrowCalendar ? 'active' : '' }}"
                            href="{{ route('student.borrow.calendar') }}" title="Borrow Calendar">
                            <span class="sidebar-item__icon"><i class="fa-solid fa-calendar-days"></i></span>
                            <span class="sidebar-item__label">Borrow Calendar</span>
                        </a>
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isBorrowRequestActive ? 'active' : '' }}"
                            href="{{ route('student.borrow.index') }}" title="Borrow">
                            <span class="d-flex align-items-center gap-2 flex-grow-1">
                                <span class="sidebar-item__icon"><i class="fa-solid fa-calendar-check"></i></span>
                                <span class="sidebar-item__label">Borrow Request</span>
                            </span>
                        </a>
                    </div>
                </div>

                <button
                    class="nav-link rounded-3 py-2 px-3 border-0 text-start d-flex align-items-center justify-content-between"
                    type="button" data-bs-toggle="collapse" data-bs-target="#studentFeedbackMenu"
                    aria-expanded="{{ $isCommunicationGroup ? 'true' : 'false' }}"
                    aria-controls="studentFeedbackMenu" title="Feedback">
                    <span class="d-flex align-items-center gap-2">
                        <span class="sidebar-item__icon"><i class="fa-solid fa-message"></i></span>
                        <span class="sidebar-item__label">Communication</span>
                    </span>
                    <span class="sidebar-item__chevron small" aria-hidden="true"><i class="fa-solid fa-chevron-down"></i></span>
                </button>
                <div class="collapse {{ $isCommunicationGroup ? 'show' : '' }}" id="studentFeedbackMenu">
                    <div class="nav nav-pills flex-column gap-1 ms-3 ps-2 border-start">
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isForumGroup ? 'active' : '' }}"
                            href="{{ route('student.forum.index') }}" title="Forun">
                            <span class="sidebar-item__icon"><i class="fa-solid fa-comments"></i></span>
                            <span class="sidebar-item__label">Forum</span>
                        </a>
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isFeedbackActiveGroup ? 'active' : '' }}"
                            href="{{ route('student.feedback.index') }}" title="Feedback">
                            <span class="sidebar-item__icon"><i class="fa-solid fa-message"></i></span>
                            <span class="sidebar-item__label">Feedback</span>
                        </a>
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isQuestionnairesGroup ? 'active' : '' }}"
                            href="{{ route('student.feedback.questionnaires.index') }}" title="Questionnaires">
                            <span class="sidebar-item__icon"><i class="fa-solid fa-clipboard-question"></i></span>
                            <span class="sidebar-item__label">Questionnaires</span>
                        </a>
                    </div>
                </div>

                <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isMyAccount ? 'active' : '' }}"
                    href="{{ route('student.myaccount') }}" title="My Account">
                    <span class="sidebar-item__icon"><i class="fa-solid fa-user"></i></span>
                    <span class="sidebar-item__label">My Account</span>
                </a>
            </nav>
        </div>
    </div>
</aside>
