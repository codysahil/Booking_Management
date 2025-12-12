@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Reports & Analytics</h1>
            <p class="text-gray-600">{{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</p>
        </div>
        <a href="{{ route('admin.reports.export', request()->query()) }}" class="mt-4 md:mt-0 inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Export CSV
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form method="GET" class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Period</label>
                <select name="period" onchange="this.form.submit()" class="w-full px-2 sm:px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-rose-500">
                    <option value="week" {{ $period == 'week' ? 'selected' : '' }}>This Week</option>
                    <option value="month" {{ $period == 'month' ? 'selected' : '' }}>Monthly</option>
                    <option value="quarter" {{ $period == 'quarter' ? 'selected' : '' }}>Quarterly</option>
                    <option value="year" {{ $period == 'year' ? 'selected' : '' }}>Yearly</option>
                </select>
            </div>
            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Year</label>
                <select name="year" onchange="this.form.submit()" class="w-full px-2 sm:px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-rose-500">
                    @for($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Month</label>
                <select name="month" onchange="this.form.submit()" class="w-full px-2 sm:px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-rose-500">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Branch</label>
                <select name="branch_id" onchange="this.form.submit()" class="w-full px-2 sm:px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-rose-500">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $branch_id == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Revenue -->
        <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl p-5 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm">Total Revenue</p>
                    <p class="text-2xl font-bold">₹{{ number_format($totalRevenue) }}</p>
                    <p class="text-sm mt-1 {{ $revenueGrowth >= 0 ? 'text-green-200' : 'text-red-200' }}">
                        {{ $revenueGrowth >= 0 ? '↑' : '↓' }} {{ abs($revenueGrowth) }}% vs previous
                    </p>
                </div>
                <div class="bg-white/20 p-3 rounded-full">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Occupancy Rate -->
        <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl p-5 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm">Occupancy Rate</p>
                    <p class="text-2xl font-bold">{{ $occupancyData['rate'] }}%</p>
                    <p class="text-sm mt-1 text-blue-200">{{ $occupancyData['occupied'] }}/{{ $occupancyData['total'] }} beds</p>
                </div>
                <div class="bg-white/20 p-3 rounded-full">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Active Customers -->
        <div class="bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl p-5 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm">Active Customers</p>
                    <p class="text-2xl font-bold">{{ $customerStats['active'] }}</p>
                    <p class="text-sm mt-1 text-purple-200">+{{ $customerStats['new'] }} new this period</p>
                </div>
                <div class="bg-white/20 p-3 rounded-full">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Pending Dues -->
        <div class="bg-gradient-to-br from-orange-500 to-red-600 rounded-xl p-5 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm">Pending Dues</p>
                    <p class="text-2xl font-bold">₹{{ number_format($pendingDues) }}</p>
                    <p class="text-sm mt-1 text-orange-200">Awaiting collection</p>
                </div>
                <div class="bg-white/20 p-3 rounded-full">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Revenue Chart -->
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Monthly Revenue (Last 12 Months)</h3>
            <div class="h-64">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Revenue by Branch -->
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Revenue by Branch</h3>
            @php
                $totalBranchRevenue = collect($revenueByBranch)->sum('revenue');
            @endphp
            @if($totalBranchRevenue > 0)
                <div class="h-64">
                    <canvas id="branchChart"></canvas>
                </div>
            @else
                <div class="h-64 flex flex-col items-center justify-center text-gray-400">
                    <svg class="w-16 h-16 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
                    </svg>
                    <p class="text-sm">No revenue data for this period</p>
                    <p class="text-xs mt-1">Revenue will appear here once payments are made</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Payment Stats & Recent Transactions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Payment Statistics -->
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Payment Statistics</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Total Transactions</span>
                    <span class="font-semibold">{{ $paymentStats['total_count'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-green-600">Completed</span>
                    <span class="font-semibold text-green-600">{{ $paymentStats['completed'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-yellow-600">Pending</span>
                    <span class="font-semibold text-yellow-600">{{ $paymentStats['pending'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-red-600">Failed</span>
                    <span class="font-semibold text-red-600">{{ $paymentStats['failed'] }}</span>
                </div>
                <hr>
                <div class="flex justify-between items-center">
                    <span class="text-gray-800 font-medium">Total Collected</span>
                    <span class="font-bold text-green-600">₹{{ number_format($paymentStats['total_amount']) }}</span>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-5">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Transactions</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="pb-3">Customer</th>
                            <th class="pb-3">Amount</th>
                            <th class="pb-3">Date</th>
                            <th class="pb-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($recentPayments as $payment)
                        <tr>
                            <td class="py-3">{{ $payment->customer->name ?? 'N/A' }}</td>
                            <td class="py-3 font-medium">₹{{ number_format($payment->amount) }}</td>
                            <td class="py-3 text-gray-500">{{ $payment->created_at->format('M d, H:i') }}</td>
                            <td class="py-3">
                                <span class="px-2 py-1 text-xs rounded-full {{ $payment->status == 'completed' ? 'bg-green-100 text-green-700' : ($payment->status == 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center text-gray-500">No transactions found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(collect($monthlyRevenue)->pluck('month')) !!},
            datasets: [{
                label: 'Revenue (₹)',
                data: {!! json_encode(collect($monthlyRevenue)->pluck('revenue')) !!},
                borderColor: 'rgb(236, 72, 153)',
                backgroundColor: 'rgba(236, 72, 153, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { callback: v => '₹' + v.toLocaleString() } }
            }
        }
    });

    // Branch Chart - only render if there's data
    @if($totalBranchRevenue > 0)
    const branchCtx = document.getElementById('branchChart').getContext('2d');
    new Chart(branchCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(collect($revenueByBranch)->pluck('name')) !!},
            datasets: [{
                data: {!! json_encode(collect($revenueByBranch)->pluck('revenue')) !!},
                backgroundColor: ['#ec4899', '#8b5cf6', '#3b82f6', '#10b981', '#f59e0b']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ₹' + context.raw.toLocaleString();
                        }
                    }
                }
            }
        }
    });
    @endif
</script>
@endsection
