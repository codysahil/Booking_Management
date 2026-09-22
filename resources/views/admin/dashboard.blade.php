@extends('layouts.admin')

@section('header', 'Dashboard')

@section('content')
    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-teal-500 via-cyan-500 to-violet-500 rounded-3xl p-8 mb-8 text-white shadow-xl">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-3xl font-display font-bold mb-2">Welcome back! 👋</h2>
                <p class="text-white/90">Here's what's happening with your hostels today.</p>
            </div>
            <form method="GET" class="flex items-center gap-2">
                <select name="branch_id" onchange="this.form.submit()"
                    class="text-sm rounded-lg border-0 bg-white/20 text-white placeholder-white/70 focus:ring-2 focus:ring-white/50">
                    <option value="" class="text-gray-900">All branches</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" class="text-gray-900" {{ (string) $branchId === (string) $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-teal-100">
            <p class="text-sm font-medium text-gray-500 mb-1">Occupancy</p>
            <p class="text-3xl font-bold text-gray-900">{{ $stats['occupancy_rate'] }}%</p>
            <p class="text-xs text-gray-500 mt-1">{{ $stats['beds'] - $stats['vacant_beds'] }} of {{ $stats['beds'] }} beds occupied</p>
        </div>
        <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-green-100">
            <p class="text-sm font-medium text-gray-500 mb-1">Collected this month</p>
            <p class="text-3xl font-bold text-green-600">{{ money($collectedThisMonth) }}</p>
            <p class="text-xs text-gray-500 mt-1">
                Expenses: {{ money($expensesThisMonth) }} ·
                Net: <span class="{{ $collectedThisMonth - $expensesThisMonth >= 0 ? 'text-green-600' : 'text-red-600' }} font-semibold">{{ money($collectedThisMonth - $expensesThisMonth) }}</span>
            </p>
        </div>
        <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-amber-100">
            <p class="text-sm font-medium text-gray-500 mb-1">Pending / overdue dues</p>
            <p class="text-3xl font-bold text-amber-600">{{ money($pendingChargesAmount + $pendingDuesAmount) }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $overdueCount }} charge(s) overdue</p>
        </div>
        <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-violet-100">
            <p class="text-sm font-medium text-gray-500 mb-1">Open resident requests</p>
            <p class="text-3xl font-bold text-violet-600">{{ $openRequestsCount }}</p>
            <a href="{{ route('admin.requests.index') }}" class="text-xs text-violet-600 hover:underline">View all →</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Income vs Expenses chart -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-lg p-6 border-2 border-gray-100">
            <h3 class="text-lg font-display font-bold text-gray-900 mb-4">Income vs Expenses (last 6 months)</h3>
            <canvas id="incomeExpenseChart" height="110"></canvas>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-gray-100">
            <h3 class="text-lg font-display font-bold text-gray-900 mb-4">Quick Actions</h3>
            <div class="space-y-3">
                <a href="{{ route('admin.customers.create') }}"
                    class="block text-center px-4 py-3 bg-gradient-to-r from-teal-500 to-cyan-500 text-white font-bold rounded-xl hover:from-teal-600 hover:to-cyan-600 transition">
                    Check-In a Customer
                </a>
                <a href="{{ route('admin.expenses.create') }}"
                    class="block text-center px-4 py-3 bg-gradient-to-r from-cyan-500 to-violet-500 text-white font-bold rounded-xl hover:from-cyan-600 hover:to-violet-600 transition">
                    Record an Expense
                </a>
                <a href="{{ route('admin.announcements.create') }}"
                    class="block text-center px-4 py-3 bg-gradient-to-r from-violet-500 to-teal-500 text-white font-bold rounded-xl hover:from-violet-600 hover:to-teal-600 transition">
                    Post an Announcement
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Bookings -->
        <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-display font-bold text-gray-900">Recent Bookings</h3>
                <a href="{{ route('admin.bookings.index') }}" class="text-xs text-teal-600 hover:underline">View all →</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentBookings as $booking)
                    <div class="p-4 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $booking->customer->name }}</p>
                            <p class="text-xs text-gray-500">{{ $booking->bed->room->branch->name }} · {{ $booking->booking_reference }}</p>
                        </div>
                        <span class="text-xs px-2 py-1 rounded-full {{ $booking->status === 'active' ? 'bg-green-100 text-green-800' : ($booking->status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}">
                            {{ ucfirst($booking->status) }}
                        </span>
                    </div>
                @empty
                    <p class="p-6 text-sm text-gray-500 text-center">No bookings yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Open Requests -->
        <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-display font-bold text-gray-900">Open Resident Requests</h3>
                <a href="{{ route('admin.requests.index') }}" class="text-xs text-teal-600 hover:underline">View all →</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($openRequests as $reqItem)
                    <a href="{{ route('admin.requests.show', $reqItem) }}" class="p-4 flex items-center justify-between hover:bg-gray-50 transition">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $reqItem->subject }}</p>
                            <p class="text-xs text-gray-500">{{ $reqItem->customer->name ?? 'Unknown' }} · {{ $reqItem->type_label }}</p>
                        </div>
                        <span class="text-xs px-2 py-1 rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                    </a>
                @empty
                    <p class="p-6 text-sm text-gray-500 text-center">No open requests. 🎉</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    const ctx = document.getElementById('incomeExpenseChart');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($incomeExpenseChart->pluck('label')),
            datasets: [
                {
                    label: 'Income',
                    data: @json($incomeExpenseChart->pluck('income')),
                    backgroundColor: '#14b8a6',
                    borderRadius: 6,
                },
                {
                    label: 'Expenses',
                    data: @json($incomeExpenseChart->pluck('expenses')),
                    backgroundColor: '#c4b5fd',
                    borderRadius: 6,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { beginAtZero: true } },
        },
    });
</script>
@endpush
