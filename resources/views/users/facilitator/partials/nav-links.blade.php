@php
    $activeLink = $active ?? 'dashboard';
@endphp

<div class="role-nav nav nav-pills flex-nowrap overflow-auto gap-2 pb-1">
    <a class="nav-link {{ $activeLink === 'dashboard' ? 'active' : '' }}" href="{{ route('facilitator.dashboard') }}">Dashboard</a>
    <a class="nav-link {{ $activeLink === 'reservation' ? 'active' : '' }}" href="{{ route('facilitator.reservations.index') }}">Reservation</a>
    <a class="nav-link {{ $activeLink === 'reservation-calendar' ? 'active' : '' }}" href="{{ route('facilitator.reservations.calendar') }}">Reservation Calendar</a>
    <a class="nav-link {{ $activeLink === 'borrow' ? 'active' : '' }}" href="{{ route('facilitator.borrow.index') }}">Borrow</a>
    <a class="nav-link {{ $activeLink === 'forum' ? 'active' : '' }}" href="{{ route('facilitator.forum.index') }}">Forum</a>
    <a class="nav-link {{ $activeLink === 'inventory' ? 'active' : '' }}" href="#">Inventory</a>
    <a class="nav-link {{ $activeLink === 'barcode' ? 'active' : '' }}" href="#">Barcode Scanner</a>
    <a class="nav-link {{ $activeLink === 'activity' ? 'active' : '' }}" href="#">Activity Log</a>
    <a class="nav-link {{ $activeLink === 'report' ? 'active' : '' }}" href="#">Report Logs</a>
</div>