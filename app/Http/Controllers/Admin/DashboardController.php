<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Due;
use App\Models\Expense;
use App\Models\MonthlyCharge;
use App\Models\Payment;
use App\Models\Request as ResidentRequest;
use App\Models\Room;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->get('branch_id');

        $bedsQuery = Bed::query()->when($branchId, fn ($q) => $q->whereHas('room', fn ($r) => $r->where('branch_id', $branchId)));

        $stats = [
            'branches' => Branch::count(),
            'rooms' => Room::when($branchId, fn ($q) => $q->where('branch_id', $branchId))->count(),
            'beds' => (clone $bedsQuery)->count(),
            'vacant_beds' => (clone $bedsQuery)->where('status', 'vacant')->count(),
            'customers' => Customer::count(),
        ];

        $stats['occupancy_rate'] = $stats['beds'] > 0
            ? round((($stats['beds'] - $stats['vacant_beds']) / $stats['beds']) * 100, 1)
            : 0;

        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $collectedThisMonth = Payment::successful()->receivedBetween($monthStart, $monthEnd)->forBranch($branchId)->sum('amount');
        $expensesThisMonth = Expense::whereBetween('date', [$monthStart, $monthEnd])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->sum('amount');

        $pendingDuesAmount = Due::where('status', 'pending')
            ->when($branchId, fn ($q) => $q->whereHas('customer.bookings.bed.room', fn ($r) => $r->where('branch_id', $branchId)))
            ->sum('amount');

        $pendingChargesAmount = MonthlyCharge::whereIn('status', ['pending', 'overdue'])
            ->when($branchId, fn ($q) => $q->whereHas('customer.bookings.bed.room', fn ($r) => $r->where('branch_id', $branchId)))
            ->sum('total_amount');

        $overdueCount = MonthlyCharge::where('status', 'overdue')
            ->when($branchId, fn ($q) => $q->whereHas('customer.bookings.bed.room', fn ($r) => $r->where('branch_id', $branchId)))
            ->count();

        // Last 6 months income vs expenses, for the Chart.js widget.
        $chartMonths = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i)->startOfMonth());
        $incomeExpenseChart = $chartMonths->map(function ($month) use ($branchId) {
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            return [
                'label' => $month->format('M Y'),
                'income' => (float) Payment::successful()->receivedBetween($start, $end)->forBranch($branchId)->sum('amount'),
                'expenses' => (float) Expense::whereBetween('date', [$start, $end])
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->sum('amount'),
            ];
        })->values();

        $recentBookings = Booking::with(['customer', 'bed.room.branch'])
            ->when($branchId, fn ($q) => $q->whereHas('bed.room', fn ($r) => $r->where('branch_id', $branchId)))
            ->latest()
            ->limit(5)
            ->get();

        $openRequests = ResidentRequest::with('customer')->where('status', 'pending')->latest()->limit(5)->get();
        $openRequestsCount = ResidentRequest::where('status', 'pending')->count();

        $branches = Branch::all();

        return view('admin.dashboard', compact(
            'stats', 'collectedThisMonth', 'expensesThisMonth', 'pendingDuesAmount', 'pendingChargesAmount',
            'overdueCount', 'incomeExpenseChart', 'recentBookings', 'openRequests', 'openRequestsCount',
            'branches', 'branchId'
        ));
    }
}
