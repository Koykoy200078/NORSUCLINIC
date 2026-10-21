@php
    $notifications = getNotification();
    $unreadCount = \App\Models\Notification::visibleTo(auth()->user())->whereNull('read_at')->unresolved()->count();
@endphp
<li class="px-sm-3 px-2" data-live-badge="notifications" data-unread-count="{{ $unreadCount }}">
    <div class="dropdown custom-dropdown d-flex align-items-center py-4">
        <button class="btn dropdown-toggle hide-arrow ps-2 pe-0" type="button" id="notificationBell" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
            <i class="fa-solid fa-bell text-primary fs-2"></i>
            <span id="header-notification-counter" class="badge bg-primary {{ $unreadCount ? '' : 'd-none' }}">{{ $unreadCount }}</span>
        </button>
        <div class="dropdown-menu dropdown-menu-end p-3" aria-labelledby="notificationBell">
            <h3 class="fs-5">Notifications</h3>
            <div class="inner-scroll height-270">
                @forelse($notifications as $notification)
                    <div class="border-bottom py-2">
                        <div>{{ $notification->title }}</div>
                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                        <button type="button" class="btn btn-sm btn-link" data-notification-read="{{ route('notifications.read', $notification) }}">Mark read</button>
                    </div>
                @empty
                    <p>No unread notifications.</p>
                @endforelse
            </div>
            <button type="button" class="btn btn-sm btn-primary my-2" data-notification-read="{{ route('notifications.read.all') }}">Mark all as read</button>
            @if(auth()->user()->hasAnyRole(['clinic_admin', 'staff', 'doctor']))
                <a class="d-block" href="{{ getRouteByRole('notifications.index') }}">Open notification center</a>
            @endif
        </div>
    </div>
</li>
