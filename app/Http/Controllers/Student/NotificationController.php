<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SentNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of student notifications or return JSON for topbar dropdown.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
            $notifications = SentNotification::forUser($user)
                ->latest()
                ->take(8)
                ->get()
                ->map(function ($n) use ($user) {
                    return [
                        'id'         => $n->id,
                        'title'      => $n->title,
                        'message'    => $n->message,
                        'action_url' => $n->action_url ?: route('student.classes.today'),
                        'created_at' => $n->created_at ? $n->created_at->diffForHumans() : '',
                        'is_read'    => $user->isNotificationRead($n->id, $n->created_at),
                    ];
                });

            $unreadCount = SentNotification::forUser($user)
                ->latest()
                ->take(30)
                ->get()
                ->filter(fn($n) => !$user->isNotificationRead($n->id, $n->created_at))
                ->count();

            return response()->json([
                'success'       => true,
                'notifications' => $notifications,
                'unread_count'  => $unreadCount,
            ]);
        }

        $notifications = SentNotification::forUser($user)->latest()->paginate(15);

        return view('student.notifications.index', compact('notifications'));
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(Request $request, SentNotification $notification)
    {
        $user = auth()->user();
        if ($user) {
            $user->markNotificationAsRead($notification->id);
        }

        return response()->json([
            'success' => true,
            'message' => 'নোটিফিকেশন পঠিত হিসেবে চিহ্নিত করা হয়েছে।',
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(Request $request)
    {
        $user = auth()->user();
        if ($user) {
            $now = now();
            $user->forceFill(['notifications_read_at' => $now])->save();
            session(['notifications_read_at' => $now]);
        }

        return response()->json([
            'success' => true,
            'message' => 'সকল নোটিফিকেশন পঠিত হিসেবে চিহ্নিত করা হয়েছে।',
        ]);
    }
}
