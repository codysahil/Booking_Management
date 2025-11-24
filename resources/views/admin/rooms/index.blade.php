@extends('layouts.admin')

@section('header', 'Manage Rooms & Beds')

@section('content')
    <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100">
        <!-- Header with Search, Filter and Add Button -->
        <div class="p-6 border-b-2 border-gray-100">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex flex-col sm:flex-row gap-3 flex-1">
                    <div class="relative flex-1 max-w-md">
                        <input type="text" id="searchInput" placeholder="Search rooms..." 
                            class="w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:border-rose-500 focus:ring-0 transition-colors">
                        <svg class="absolute left-3 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <select id="branchFilter" class="px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-rose-500 focus:ring-0 transition-colors">
                        <option value="">All Branches</option>
                        @foreach(\App\Models\Branch::all() as $branch)
                            <option value="{{ $branch->name }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <a href="{{ route('admin.rooms.create') }}"
                    class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-pink-500 to-purple-500 text-white font-bold rounded-xl hover:from-pink-600 hover:to-purple-600 transition-all duration-300 shadow-lg hover:shadow-xl">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add New Room
                </a>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-pink-50 to-purple-50">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Room
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Branch
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Type
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Capacity
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Beds Status
                        </th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100" id="roomTableBody">
                    @forelse($rooms as $room)
                        <tr class="hover:bg-pink-50/50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gradient-to-br from-pink-400 to-purple-400 rounded-xl flex items-center justify-center text-white font-bold mr-3">
                                        {{ substr($room->room_number, -2) }}
                                    </div>
                                    <div class="text-sm font-bold text-gray-900">{{ $room->room_number }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-600">{{ $room->branch->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $room->type === 'AC' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $room->type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-700">
                                    {{ $room->capacity }} Sharing
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $vacantBeds = $room->beds->where('status', 'vacant')->count();
                                    $totalBeds = $room->beds->count();
                                @endphp
                                <div class="flex items-center space-x-2">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                        {{ $vacantBeds }} Vacant
                                    </span>
                                    <span class="text-xs text-gray-500">/ {{ $totalBeds }} Total</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.rooms.edit', $room) }}"
                                    class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors mr-2">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Edit
                                </a>
                                <form action="{{ route('admin.rooms.destroy', $room) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                        class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors"
                                        onclick="return confirm('Are you sure you want to delete this room and all its beds?')">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3zM14 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1h-4a1 1 0 01-1-1v-3z"></path>
                                    </svg>
                                    <p class="text-gray-500 text-lg font-medium">No rooms found</p>
                                    <p class="text-gray-400 text-sm mt-1">Add rooms to your branches to get started</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($rooms->hasPages())
            <div class="px-6 py-4 border-t-2 border-gray-100">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-sm text-gray-600">
                        Showing <span class="font-semibold text-gray-900">{{ $rooms->firstItem() }}</span> 
                        to <span class="font-semibold text-gray-900">{{ $rooms->lastItem() }}</span> 
                        of <span class="font-semibold text-gray-900">{{ $rooms->total() }}</span> rooms
                    </div>
                    <div class="flex items-center space-x-2">
                        {{-- Previous Button --}}
                        @if ($rooms->onFirstPage())
                            <span class="px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
                                Previous
                            </span>
                        @else
                            <a href="{{ $rooms->previousPageUrl() }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border-2 border-gray-200 rounded-lg hover:bg-pink-50 hover:border-pink-300 hover:text-pink-600 transition-all">
                                Previous
                            </a>
                        @endif

                        {{-- Page Numbers --}}
                        @foreach ($rooms->getUrlRange(1, $rooms->lastPage()) as $page => $url)
                            @if ($page == $rooms->currentPage())
                                <span class="px-4 py-2 text-sm font-bold text-white bg-gradient-to-r from-pink-500 to-purple-500 rounded-lg">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border-2 border-gray-200 rounded-lg hover:bg-pink-50 hover:border-pink-300 hover:text-pink-600 transition-all">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach

                        {{-- Next Button --}}
                        @if ($rooms->hasMorePages())
                            <a href="{{ $rooms->nextPageUrl() }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border-2 border-gray-200 rounded-lg hover:bg-pink-50 hover:border-pink-300 hover:text-pink-600 transition-all">
                                Next
                            </a>
                        @else
                            <span class="px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
                                Next
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script>
        // Search and filter functionality
        const searchInput = document.getElementById('searchInput');
        const branchFilter = document.getElementById('branchFilter');
        const tableRows = document.querySelectorAll('#roomTableBody tr');

        function filterTable() {
            const searchValue = searchInput.value.toLowerCase();
            const branchValue = branchFilter.value.toLowerCase();
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const matchesSearch = text.includes(searchValue);
                const matchesBranch = !branchValue || text.includes(branchValue);
                
                row.style.display = (matchesSearch && matchesBranch) ? '' : 'none';
            });
        }

        searchInput.addEventListener('keyup', filterTable);
        branchFilter.addEventListener('change', filterTable);
    </script>
@endsection
