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
        $sidebarUser = auth()->user()?->loadMissing('role');
        $sidebarRole = $sidebarUser?->role?->role_name ?? 'Laboratory In-charge';

        $isDashboard = request()->routeIs('facilitator.dashboard');
        $isReservationsGroup = request()->routeIs('facilitator.reservations.*');
        $isReservationsIndex = request()->routeIs('facilitator.reservations.index');
        $isReservationsCalendar = request()->routeIs('facilitator.reservations.calendar');
        $isBorrowGroup = request()->routeIs('facilitator.borrow.*');
        $isBorrowIndex = request()->routeIs('facilitator.borrow.index');
        $isBorrowCalendar = request()->routeIs('facilitator.borrow.calendar');
        $isCheckoutGroup = request()->routeIs('facilitator.checkout.*');
        $isCheckinGroup = request()->routeIs('facilitator.checkin.*');
        $isForumGroup = request()->routeIs('facilitator.forum.*');
        $isMyAccount = request()->routeIs('facilitator.myaccount');

        $isFacilitatorReservationGroup = $isReservationsGroup;
        $isFacilitatorBorrowGroup = $isBorrowGroup;
        $isFacilitatorScanGroup = $isCheckoutGroup || $isCheckinGroup;

        $pendingReservationRequests = \App\Models\Reservation::where('status', 'Instructor Approved')->count();
        $pendingBorrowRequests = \App\Models\BorrowTransaction::where('status', 'Instructor Approved')->count();
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
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isReservationsIndex ? 'active' : '' }}"
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
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isBorrowIndex ? 'active' : '' }}"
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
                    </div>
                </div>

                <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isForumGroup ? 'active' : '' }}"
                    href="{{ route('facilitator.forum.index') }}" title="Forum">
                    <span class="sidebar-item__icon"><i class="fa-solid fa-comments"></i></span>
                    <span class="sidebar-item__label">Forum</span>
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
