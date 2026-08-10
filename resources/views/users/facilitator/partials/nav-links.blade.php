@php
    $activeLink = $active ?? 'dashboard';
@endphp

<div class="role-nav nav nav-pills flex-nowrap overflow-auto gap-2 pb-1">
    <a class="nav-link {{ $activeLink === 'dashboard' ? 'active' : '' }}" href="{{ route('facilitator.dashboard') }}">Dashboard</a>
    <a class="nav-link {{ $activeLink === 'reservation' ? 'active' : '' }}" href="{{ route('facilitator.reservations.index') }}">Reservation</a>
    <a class="nav-link {{ $activeLink === 'borrow' ? 'active' : '' }}" href="{{ route('facilitator.borrow.index') }}">Borrow</a>
    <a class="nav-link {{ $activeLink === 'inventory' ? 'active' : '' }}" href="#">Inventory</a>
    <a class="nav-link {{ $activeLink === 'barcode' ? 'active' : '' }}" href="#">Barcode Scanner</a>
    <a class="nav-link {{ $activeLink === 'activity' ? 'active' : '' }}" href="#">Activity Log</a>
    <a class="nav-link {{ $activeLink === 'report' ? 'active' : '' }}" href="#">Report Logs</a>
    <a class="nav-link {{ $activeLink === 'notification' ? 'active' : '' }}" href="{{ route('notifications.index') }}">Notifications</a>
    <a class="nav-link {{ $activeLink === 'myaccount' ? 'active' : '' }}" href="{{ route('facilitator.myaccount') }}">My Account</a>
</div>