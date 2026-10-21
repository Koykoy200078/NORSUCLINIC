<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class NotificationController extends AppBaseController
{
    public function index(\Illuminate\Http\Request $request): \Illuminate\Contracts\View\View
    {
        $filter = in_array($request->query('filter'), ['all', 'read', 'unread'], true) ? $request->query('filter') : 'unread';
        $query = Notification::visibleTo($request->user());
        if ($filter === 'unread') {
            $query->whereNull('read_at')->unresolved();
        } elseif ($filter === 'read') {
            $query->whereNotNull('read_at');
        }

        return view('notifications.index', ['notifications' => $query->latest('id')->paginate(25)->withQueryString(), 'filter' => $filter]);
    }

    public function readNotification(Notification $notification): JsonResponse
    {
        // A user may only mark their OWN notification as read (route-model binding alone lets
        // any authenticated user flip another user's notification by id). CRUD-IDOR.
        abort_unless((int) $notification->user_id === (int) getLogInUserId(), 403);
        abort_unless(Notification::visibleTo(auth()->user())->whereKey($notification->id)->exists(), 403);

        $notification->read_at = Carbon::now();
        $notification->save();

        return $this->sendSuccess(__('messages.flash.notification_read'));
    }

    public function readAllNotification(): JsonResponse
    {
        Notification::visibleTo(auth()->user())->whereNull('read_at')->update(['read_at' => Carbon::now()]);

        return $this->sendSuccess(__('messages.flash.all_notification_read'));
    }
}
