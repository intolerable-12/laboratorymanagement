@php
    $activeLink = $active ?? 'dashboard';
@endphp

<div class="role-nav nav nav-pills flex-nowrap overflow-auto gap-2 pb-1">
    <a class="nav-link {{ $activeLink === 'dashboard' ? 'active' : '' }}" href="{{ route('student.dashboard') }}">Dashboard</a>
    <a class="nav-link {{ $activeLink === 'forum' ? 'active' : '' }}" href="{{ route('student.forum.index') }}">Forum</a>
    <a class="nav-link {{ $activeLink === 'reservation' ? 'active' : '' }}" href="{{ route('student.reservations.index') }}">Reservation</a>
    <a class="nav-link {{ $activeLink === 'borrow' ? 'active' : '' }}" href="{{ route('student.borrow.index') }}">Borrowing item</a>
    <a class="nav-link {{ $activeLink === 'activity' ? 'active' : '' }}" href="#">Activity Log</a>
    <a class="nav-link {{ $activeLink === 'report' ? 'active' : '' }}" href="#">Report</a>
    <a class="nav-link {{ $activeLink === 'notification' ? 'active' : '' }}" href="{{ route('notifications.index') }}">Notifications</a>
    <a class="nav-link {{ $activeLink === 'feedback' ? 'active' : '' }}" href="{{ route('student.feedback.index') }}">Feedback</a>
    <a class="nav-link {{ $activeLink === 'myaccount' ? 'active' : '' }}" href="{{ route('student.myaccount') }}">My Account</a>
</div>