@extends('layouts.customer')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-display font-bold text-gray-900 mb-8">Announcements</h1>

            <div class="space-y-4">
                @forelse ($announcements as $announcement)
                    <div class="bg-white rounded-xl shadow-lg border-l-4 {{ $announcement->is_pinned ? 'border-amber-400' : 'border-primary-400' }} p-6">
                        <p class="font-bold text-gray-900">
                            @if($announcement->is_pinned) 📌 @endif
                            {{ $announcement->title }}
                        </p>
                        <p class="text-xs text-gray-400 mb-2">{{ $announcement->created_at->format('d M, Y') }}</p>
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $announcement->body }}</p>
                    </div>
                @empty
                    <div class="bg-white rounded-xl shadow-lg p-12 text-center text-gray-500">No announcements right now.</div>
                @endforelse
            </div>

            @if($announcements->hasPages())
                <div class="mt-6">{{ $announcements->links() }}</div>
            @endif
        </div>
    </div>
@endsection
