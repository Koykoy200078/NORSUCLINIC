<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class NotificationController extends AppBaseController
{
    public function readNotification(Notification $notification): JsonResponse
    {
        // A user may only mark their OWN notification as read (route-model binding alone lets
        // any authenticated user flip another user's notification by id). CRUD-IDOR.
        abort_unless((int) $notification->user_id === (int) getLogInUserId(), 403);

        $notification->read_at = Carbon::now();
        $notification->save();

        return $this->sendSuccess(__('messages.flash.notification_read'));
    }

    public function readAllNotification(): JsonResponse
    {
        Notification::whereReadAt(null)->where('user_id',
            getLogInUserId())->update(['read_at' => Carbon::now()]);

        return $this->sendSuccess(__('messages.flash.all_notification_read'));
    }
}
