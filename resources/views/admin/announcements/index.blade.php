@extends('layouts.admin')

@section('header', 'Announcements')

@section('content')
    <div class="flex justify-end mb-6">
        <a href="{{ route('admin.announcements.create') }}"
            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-teal-500 to-cyan-500 text-white font-bold rounded-xl hover:from-teal-600 hover:to-cyan-600 transition shadow-lg">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            New Announcement
        </a>
    </div>

    <div class="space-y-4">
        @forelse ($announcements as $announcement)
            <div class="bg-white rounded-2xl shadow-lg border-2 {{ $announcement->is_pinned ? 'border-amber-200' : 'border-gray-100' }} p-6">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            @if($announcement->is_pinned)
                                <span class="text-amber-500" title="Pinned">📌</span>
                            @endif
                            {{ $announcement->title }}
                        </h3>
                        <p class="text-xs text-gray-500">
                            {{ $announcement->branch->name ?? 'All branches' }} ·
                            Posted {{ $announcement->created_at->format('d M, Y') }}
                            @if($announcement->expires_on) · Expires {{ $announcement->expires_on->format('d M, Y') }} @endif
                        </p>
                    </div>
                    <div class="flex gap-3 text-sm">
                        <a href="{{ route('admin.announcements.edit', $announcement) }}" class="text-primary-600 hover:text-primary-800">Edit</a>
                        <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST" onsubmit="return confirm('Delete this announcement?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                        </form>
                    </div>
                </div>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $announcement->body }}</p>
            </div>
        @empty
            <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-12 text-center text-gray-500">
                No announcements yet.
            </div>
        @endforelse
    </div>

    @if($announcements->hasPages())
        <div class="mt-6">{{ $announcements->links() }}</div>
    @endif
@endsection
