@extends('layouts.admin')

@section('header', 'Resident Requests')

@section('content')
    <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="w-full border-gray-300 rounded-lg text-sm focus:ring-primary-500 focus:border-primary-500">
                    <option value="">All statuses</option>
                    @foreach (\App\Models\Request::STATUSES as $value => $label)
                        <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Type</label>
                <select name="type" class="w-full border-gray-300 rounded-lg text-sm focus:ring-primary-500 focus:border-primary-500">
                    <option value="">All types</option>
                    @foreach (\App\Models\Request::TYPES as $value => $label)
                        <option value="{{ $value }}" {{ request('type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-primary-700 transition">Filter</button>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-to-r from-rose-50 to-pink-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Resident</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Subject</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Submitted</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse ($requests as $request)
                    <tr class="hover:bg-rose-50/50">
                        <td class="px-6 py-4 text-sm">
                            <div class="font-medium text-gray-900">{{ $request->customer->name ?? 'Unknown' }}</div>
                            <div class="text-xs text-gray-500">{{ $request->customer->customer_code ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $request->type_label }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $request->subject }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $request->created_at->format('d M, Y') }}</td>
                        <td class="px-6 py-4">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'approved' => 'bg-green-100 text-green-800',
                                    'rejected' => 'bg-red-100 text-red-800',
                                    'completed' => 'bg-blue-100 text-blue-800',
                                ];
                            @endphp
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$request->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $request->status_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            <a href="{{ route('admin.requests.show', $request) }}" class="text-primary-600 hover:text-primary-800 font-medium">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">No requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($requests->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">{{ $requests->links() }}</div>
        @endif
    </div>
@endsection
