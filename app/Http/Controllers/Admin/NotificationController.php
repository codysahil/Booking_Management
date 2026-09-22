<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()->notifications()->paginate(20);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function markRead(Request $request, string $notification)
    {
        $record = Auth::user()->notifications()->findOrFail($notification);
        $record->markAsRead();

        return $request->has('url')
            ? redirect($request->string('url'))
            : back();
    }

    public function markAllRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }
}
