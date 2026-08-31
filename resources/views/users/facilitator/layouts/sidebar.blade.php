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
        $isReservationsCalendar = request()->routeIs('facilitator.reservations.calendar');
        $isBorrowGroup = request()->routeIs('facilitator.borrow.*');
        $isBorrowCalendar = request()->routeIs('facilitator.borrow.calendar');
        $isCheckoutGroup = request()->routeIs('facilitator.checkout.*');
        $isCheckinGroup = request()->routeIs('facilitator.checkin.*');
        $isForumGroup = request()->routeIs('facilitator.forum.*');
        $isMyAccount = request()->routeIs('facilitator.myaccount');
    @endphp

    <div class="coordinator-sidebar__body p-0 d-flex flex-column">
        <div class="p-3 p-lg-4 border-bottom">
            <nav class="nav nav-pills flex-column gap-1">
                <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isDashboard ? 'active' : '' }}"
                    href="{{ route('facilitator.dashboard') }}" title="Dashboard">
                    <span class="sidebar-item__icon"><i class="fa-solid fa-gauge-high"></i></span>
                    <span class="sidebar-item__label">Dashboard</span>
                </a>

                <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isReservationsGroup && !$isReservationsCalendar ? 'active' : '' }}"
                    href="{{ route('facilitator.reservations.index') }}" title="Reservations">
                    <span class="sidebar-item__icon"><i class="fa-solid fa-calendar-check"></i></span>
                    <span class="sidebar-item__label">Reservations</span>
                </a>

                <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isReservationsCalendar ? 'active' : '' }}"
                    href="{{ route('facilitator.reservations.calendar') }}" title="Reservation Calendar">
                    <span class="sidebar-item__icon"><i class="fa-solid fa-calendar-days"></i></span>
                    <span class="sidebar-item__label">Reservation Calendar</span>
                </a>

                <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isBorrowGroup && !$isBorrowCalendar ? 'active' : '' }}"
                    href="{{ route('facilitator.borrow.index') }}" title="Borrowing">
                    <span class="sidebar-item__icon"><i class="fa-solid fa-box-open"></i></span>
                    <span class="sidebar-item__label">Borrowing</span>
                </a>

                <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isBorrowCalendar ? 'active' : '' }}"
                    href="{{ route('facilitator.borrow.calendar') }}" title="Borrow Calendar">
                    <span class="sidebar-item__icon"><i class="fa-solid fa-calendar-plus"></i></span>
                    <span class="sidebar-item__label">Borrow Calendar</span>
                </a>

                <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isCheckoutGroup ? 'active' : '' }}"
                    href="{{ route('facilitator.checkout.index') }}" title="Checkout Items">
                    <span class="sidebar-item__icon"><i class="fa-solid fa-barcode"></i></span>
                    <span class="sidebar-item__label">Checkout Items</span>
                </a>

                <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isCheckinGroup ? 'active' : '' }}"
                    href="{{ route('facilitator.checkin.index') }}" title="Check In Items">
                    <span class="sidebar-item__icon"><i class="fa-solid fa-rotate-left"></i></span>
                    <span class="sidebar-item__label">Check In Items</span>
                </a>

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
