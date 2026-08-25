@extends($layout)

@section('title', 'Notifications')
@section('page-title', 'Notifications')
@section('page-subtitle', 'Track request alerts and approval updates')

@section('content')
    <div class="notifications-page">
        @if (session('status'))
            <div class="alert alert-success mb-4">{{ session('status') }}</div>
        @endif

        <section class="card border-0 mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <h2 class="h3 fw-semibold mb-2 text-dark">Notifications</h2>
                    <p class="mb-0 text-secondary">Review new reservation and borrow updates without leaving your dashboard.</p>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <div class="text-end">
                        <div class="small text-secondary">Unread</div>
                        <div class="display-6 fw-semibold mb-0 text-dark">{{ $unreadCount }}</div>
                    </div>

                    @if ($unreadCount > 0)
                        <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary">Mark all as read</button>
                        </form>
                    @endif
                </div>
            </div>
        </section>

        <section class="card border-0 shadow-sm">
            <div class="card-body p-0">
                @forelse ($notifications as $notification)
                    <a href="{{ route('notifications.show', $notification) }}" class="notification-row d-block text-decoration-none border-bottom {{ $notification->is_read ? '' : 'notification-row--unread' }}">
                        <div class="p-4 p-xl-4 d-flex flex-column flex-md-row justify-content-between gap-3">
                            <div class="d-flex gap-3">
                                <span class="notification-row__badge" aria-hidden="true"></span>
                                <div>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <h3 class="h5 fw-semibold mb-0 text-dark">{{ $notification->title }}</h3>
                                        <span class="badge text-bg-light border text-secondary">{{ $notification->type }}</span>
                                        @if (! $notification->is_read)
                                            <span class="badge text-bg-primary">New</span>
                                        @endif
                                    </div>
                                    <p class="mb-1 text-secondary">{{ $notification->message }}</p>
                                    <div class="small text-secondary">Sent {{ $notification->sent_at?->diffForHumans() ?? 'just now' }}</div>
                                </div>
                            </div>

                            <div class="text-md-end">
                                <div class="small text-secondary">Open request</div>
                                <div class="fw-semibold text-dark">
                                    {{ $notification->reference_type ? class_basename($notification->reference_type) : 'Notification' }}
                                </div>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="p-5 text-center text-secondary">
                        You do not have any notifications yet.
                    </div>
                @endforelse
            </div>
        </section>

        <div class="mt-3">
            {{ $notifications->links() }}
        </div>
    </div>
@endsection
