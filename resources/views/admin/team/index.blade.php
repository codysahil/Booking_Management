@extends('layouts.admin')

@section('header', 'Team')

@section('content')
    <div class="flex justify-end mb-6">
        <a href="{{ route('admin.team.create') }}"
            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-rose-500 to-pink-500 text-white font-bold rounded-xl hover:from-rose-600 hover:to-pink-600 transition shadow-lg">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add Team Member
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-to-r from-rose-50 to-pink-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @foreach ($team as $member)
                    <tr class="hover:bg-rose-50/50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $member->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $member->email }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $member->role_label }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $member->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $member->is_active ? 'Active' : 'Disabled' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            <a href="{{ route('admin.team.edit', $member) }}" class="text-primary-600 hover:text-primary-800 mr-3">Edit</a>
                            @if($member->id !== auth()->id())
                                <form action="{{ route('admin.team.destroy', $member) }}" method="POST" class="inline" onsubmit="return confirm('Remove this team member?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">Remove</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
