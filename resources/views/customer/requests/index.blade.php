@extends('layouts.customer')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-teal-50 via-cyan-50 to-violet-50 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-display font-bold text-gray-900">My Requests</h1>
                <a href="{{ route('customer.requests.create') }}"
                    class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-teal-500 to-cyan-500 text-white font-bold rounded-xl hover:from-teal-600 hover:to-cyan-600 transition shadow-lg">
                    New Request
                </a>
            </div>

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl mb-6 text-sm">{{ session('success') }}</div>
            @endif

            <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 overflow-hidden">
                <div class="divide-y divide-gray-100">
                    @forelse ($requests as $request)
                        @php
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'approved' => 'bg-green-100 text-green-800',
                                'rejected' => 'bg-red-100 text-red-800',
                                'completed' => 'bg-blue-100 text-blue-800',
                            ];
                        @endphp
                        <a href="{{ route('customer.requests.show', $request) }}" class="block p-5 hover:bg-teal-50/50 transition">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $request->subject }}</p>
                                    <p class="text-xs text-gray-500">{{ $request->type_label }} · {{ $request->created_at->format('d M, Y') }}</p>
                                </div>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$request->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $request->status_label }}
                                </span>
                            </div>
                        </a>
                    @empty
                        <div class="p-12 text-center text-gray-500">You haven't submitted any requests yet.</div>
                    @endforelse
                </div>
            </div>

            @if($requests->hasPages())
                <div class="mt-6">{{ $requests->links() }}</div>
            @endif
        </div>
    </div>
@endsection
