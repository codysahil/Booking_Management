@extends('layouts.admin')

@section('header', 'Notifications')

@section('content')
    <div class="flex justify-end mb-6">
        <form method="POST" action="{{ route('admin.notifications.read-all') }}">
            @csrf @method('PATCH')
            <button type="submit" class="text-sm text-primary-600 hover:text-primary-800 font-medium">Mark all as read</button>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 overflow-hidden">
        <div class="divide-y divide-gray-100">
            @forelse ($notifications as $notification)
                @php
                    $kindIcons = ['booking' => '🛏️', 'request' => '📝', 'payment' => '💰', 'info' => 'ℹ️'];
                @endphp
                <a href="{{ route('admin.notifications.read', ['notification' => $notification->id, 'url' => $notification->data['url'] ?? route('admin.notifications.index')]) }}"
                    class="flex items-start gap-4 p-5 hover:bg-rose-50/50 transition {{ $notification->read_at ? '' : 'bg-rose-50/30' }}">
                    <span class="text-2xl">{{ $kindIcons[$notification->data['kind'] ?? 'info'] ?? 'ℹ️' }}</span>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-900">{{ $notification->data['title'] ?? 'Notification' }}</p>
                        <p class="text-sm text-gray-600">{{ $notification->data['message'] ?? '' }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    @unless($notification->read_at)
                        <span class="w-2 h-2 mt-2 rounded-full bg-rose-500"></span>
                    @endunless
                </a>
            @empty
                <div class="p-12 text-center text-gray-500">No notifications yet.</div>
            @endforelse
        </div>
        @if($notifications->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">{{ $notifications->links() }}</div>
        @endif
    </div>
@endsection
