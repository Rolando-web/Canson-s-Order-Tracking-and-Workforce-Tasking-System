<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationsController extends Controller
{
    /**
     * Display the notifications page.
     */
    public function index()
    {
        $user = auth()->user();

        $notifications = Notification::forUser($user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $unreadCount = $notifications->where('is_read', false)->count();

        return view('pages.notifications', compact('notifications', 'unreadCount'));
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $notification->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        Notification::forUser(auth()->id())->unread()->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Get the unread count (for AJAX polling).
     */
    public function unreadCount()
    {
        $count = Notification::forUser(auth()->id())->unread()->count();

        return response()->json(['count' => $count]);
    }
}
