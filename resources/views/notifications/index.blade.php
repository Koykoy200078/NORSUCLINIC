@extends('layouts.app')
@section('title', 'Notification center')
@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Notification center</h1>
    <p>Stock warnings use the inventory alert thresholds and a 30-day expiry window. Read alerts stay read while the same condition remains active. Resolved alerts remain in history.</p>
    <div class="d-flex flex-wrap gap-3 mb-4">
        @foreach(['unread' => 'Unread', 'read' => 'Read', 'all' => 'All / history'] as $value => $label)
            <a class="btn {{ $filter === $value ? 'btn-primary' : 'btn-light' }}" href="{{ getRouteByRole('notifications.index', ['filter' => $value]) }}">{{ $label }}</a>
        @endforeach
        <button type="button" class="btn btn-outline-primary" data-notification-read="{{ route('notifications.read.all') }}">Mark all as read</button>
    </div>
    <div data-notification-center aria-live="polite">
        @forelse($notifications as $notification)
            <article class="card mb-3"><div class="card-body">
                <h2 class="fs-5">{{ $notification->title }}</h2>
                <p>{{ $notification->alert_details }}</p>
                <p class="text-muted">{{ $notification->created_at->format('M j, Y H:i') }} · {{ $notification->read_at ? 'Read' : 'Unread' }}{{ $notification->resolved_at ? ' · Resolved' : '' }}</p>
                @if(!$notification->resolved_at && $notification->destination)
                    <a href="{{ $notification->destination }}" class="btn btn-sm btn-light">Open record</a>
                @endif
                @if(!$notification->read_at)
                    <button type="button" class="btn btn-sm btn-primary" data-notification-read="{{ route('notifications.read', $notification) }}">Mark read</button>
                @endif
            </div></article>
        @empty
            <p>No notifications in this view.</p>
        @endforelse
        {{ $notifications->links() }}
    </div>
</div>
@endsection
