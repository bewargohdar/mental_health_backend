<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $notifications = auth()->user()
            ->notifications()
            ->when($request->unread_only, fn($q) => $q->whereNull('read_at'))
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return $this->success($notifications);
    }

    public function unreadCount(): JsonResponse
    {
        $count = auth()->user()->unreadNotifications()->count();

        return $this->success(['count' => $count]);
    }

    public function markAsRead(string $id): JsonResponse
    {
        $notification = auth()->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        return $this->success(null, 'Notification marked as read.');
    }

    public function markAllAsRead(): JsonResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return $this->success(null, 'All notifications marked as read.');
    }

    public function destroy(string $id): JsonResponse
    {
        $notification = auth()->user()
            ->notifications()
            ->findOrFail($id);

        $notification->delete();

        return $this->success(null, 'Notification deleted.');
    }

    public function clear(): JsonResponse
    {
        auth()->user()->notifications()->delete();

        return $this->success(null, 'All notifications cleared.');
    }
}
