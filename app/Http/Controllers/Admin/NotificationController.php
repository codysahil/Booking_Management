<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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

        $url = $request->string('url')->toString();

        // Only ever redirect to a same-site relative path — never a full URL or a
        // protocol-relative "//host" one, which browsers treat as off-site too.
        if ($url !== '' && Str::startsWith($url, '/') && ! Str::startsWith($url, '//')) {
            return redirect($url);
        }

        return back();
    }

    public function markAllRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }
}
