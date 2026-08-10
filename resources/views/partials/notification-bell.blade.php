@php
    $notificationSummary = $notificationSummary ?? ['unreadCount' => 0, 'items' => collect()];
    $unreadCount = (int) data_get($notificationSummary, 'unreadCount', 0);
    $notifications = data_get($notificationSummary, 'items', collect());
@endphp

<div class="dropdown notification-dropdown">
    <button
        class="btn notification-bell {{ $unreadCount > 0 ? 'notification-bell--active' : 'notification-bell--empty' }} position-relative d-inline-flex align-items-center justify-content-center"
        type="button"
        data-bs-toggle="dropdown"
        aria-expanded="false"
        aria-label="{{ $unreadCount > 0 ? 'Open notifications' : 'No unread notifications' }}"
    >
        <svg class="notification-bell__icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M12 2a7 7 0 0 0-7 7v3.2c0 1.05-.27 2.08-.78 2.98L3.1 16.9A1 1 0 0 0 4 18.5h16a1 1 0 0 0 .9-1.6l-1.12-1.72c-.51-.9-.78-1.93-.78-2.98V9a7 7 0 0 0-7-7Zm0 20a3 3 0 0 0 2.83-2h-5.66A3 3 0 0 0 12 22Z" />
        </svg>

        @if ($unreadCount > 0)
            <span class="notification-bell__count">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
        @endif
        <span class="visually-hidden">Unread notifications</span>
    </button>

    <div class="dropdown-menu dropdown-menu-end shadow-lg notification-menu">
        <div class="notification-menu__header d-flex align-items-center justify-content-between gap-2 px-3 py-2 border-bottom">
            <div>
                <div class="fw-semibold text-dark">Notifications</div>
                <small class="text-secondary">{{ $unreadCount }} unread</small>
            </div>

            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-primary notification-menu__action">Mark all read</button>
                </form>
            @endif
        </div>

        <div class="notification-menu__list">
            @forelse ($notifications as $notification)
                <a class="dropdown-item notification-menu__item {{ $notification->is_read ? '' : 'is-unread' }}" href="{{ route('notifications.show', $notification) }}">
                    <div class="d-flex gap-3 align-items-start">
                        <span class="notification-menu__dot" aria-hidden="true"></span>
                        <div class="flex-grow-1" style="min-width: 0;">
                            <div class="d-flex align-items-start justify-content-between gap-3">
                                <div class="fw-semibold text-dark text-truncate">{{ $notification->title }}</div>
                                <small class="text-secondary text-nowrap">{{ $notification->sent_at?->diffForHumans() ?? 'just now' }}</small>
                            </div>
                            <div class="small text-secondary text-truncate">{{ \Illuminate\Support\Str::limit($notification->message, 95) }}</div>
                            <div class="small text-uppercase fw-semibold text-secondary mt-1 notification-menu__type">{{ $notification->type }}</div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="px-3 py-4 text-center text-secondary small">You do not have any notifications yet.</div>
            @endforelse
        </div>

        <div class="border-top p-2">
            <a class="btn btn-sm btn-light border w-100" href="{{ route('notifications.index') }}">View all notifications</a>
        </div>
    </div>
</div>